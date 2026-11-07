<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PaginatesInertiaIndex;
use App\Models\Invoice;
use App\Models\PaymentReceipt;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class InvoiceController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'status', 'per_page'];

    public function __construct(private readonly InvoiceService $invoices)
    {
    }

    public function index(Request $request): Response
    {
        $this->authorizeFinance($request);

        $user = $request->user();
        $query = Invoice::query()
            ->with([
                'patient:id,name,last_name',
                'invoiceable',
            ])
            ->where('branch_id', $user->branch_id)
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($builder) use ($search) {
                $builder->where('uuid', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $paginator = $this->paginateQuery($query, $request);

        return Inertia::render('Finance/Invoices/Index', [
            'invoices' => $this->paginationPayload($paginator, function (Invoice $invoice) {
                $appointment = $invoice->appointment();
                if ($appointment && ! $appointment->relationLoaded('department')) {
                    $appointment->load('department:id,name');
                }

                return [
                    'id' => $invoice->id,
                    'uuid' => $invoice->uuid,
                    'patient_name' => trim(($invoice->patient?->name ?? '').' '.($invoice->patient?->last_name ?? '')),
                    'source' => class_basename((string) $invoice->invoiceable_type),
                    'department_name' => $appointment?->department?->name,
                    'status' => $invoice->status,
                    'total' => $invoice->total,
                    'paid_amount' => $invoice->paid_amount,
                    'issued_at' => $invoice->issued_at?->format('Y-m-d H:i'),
                    'paid_at' => $invoice->paid_at?->format('Y-m-d H:i'),
                    'show_url' => route('finance.invoices.show', $invoice),
                ];
            }),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'stats' => [
                'draft' => Invoice::query()->where('branch_id', $user->branch_id)->where('status', Invoice::STATUS_DRAFT)->count(),
                'issued' => Invoice::query()->where('branch_id', $user->branch_id)->where('status', Invoice::STATUS_ISSUED)->count(),
                'paid' => Invoice::query()->where('branch_id', $user->branch_id)->where('status', Invoice::STATUS_PAID)->count(),
            ],
            'permissions' => [
                'pay' => $this->canPay($user),
                'issue' => $this->canPay($user),
            ],
            'urls' => [
                'index' => route('finance.invoices.index'),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function show(Request $request, Invoice $invoice): Response
    {
        $this->authorizeFinance($request);
        $this->ensureBranch($request, $invoice);

        $invoice->load([
            'patient:id,name,last_name,phone',
            'invoiceable',
            'items.labType:id,name',
            'items.billable',
            'payments.paidByUser:id,name,last_name',
            'receipts',
            'branch:id,name',
        ]);

        $appointment = $invoice->appointment();
        if ($appointment && ! $appointment->relationLoaded('department')) {
            $appointment->load('department:id,name');
        }

        $latestReceipt = $invoice->receipts->sortByDesc('id')->first();

        return Inertia::render('Finance/Invoices/Show', [
            'invoice' => [
                'id' => $invoice->id,
                'uuid' => $invoice->uuid,
                'status' => $invoice->status,
                'source' => class_basename((string) $invoice->invoiceable_type),
                'subtotal' => $invoice->subtotal,
                'total' => $invoice->total,
                'paid_amount' => $invoice->paid_amount,
                'issued_at' => $invoice->issued_at?->format('Y-m-d H:i'),
                'paid_at' => $invoice->paid_at?->format('Y-m-d H:i'),
                'patient_name' => trim(($invoice->patient?->name ?? '').' '.($invoice->patient?->last_name ?? '')),
                'patient_phone' => $invoice->patient?->phone,
                'department_name' => $appointment?->department?->name,
                'branch_name' => $invoice->branch?->name,
                'items' => $invoice->items->map(fn ($item) => [
                    'id' => $item->id,
                    'item_type' => $item->item_type,
                    'description' => $item->description,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'amount' => $item->amount,
                    'payment_status' => $item->payment_status,
                ])->values()->all(),
                'payments' => $invoice->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'method' => $payment->method,
                    'paid_at' => $payment->paid_at?->format('Y-m-d H:i'),
                    'paid_by' => trim(($payment->paidByUser?->name ?? '').' '.($payment->paidByUser?->last_name ?? '')),
                ])->values()->all(),
            ],
            'receiptUrl' => $latestReceipt
                ? route('finance.receipts.print', $latestReceipt)
                : null,
            'permissions' => [
                'pay' => $this->canPay($request->user()) && $invoice->status !== Invoice::STATUS_PAID && $invoice->status !== Invoice::STATUS_CANCELLED,
                'issue' => $this->canPay($request->user()) && $invoice->status === Invoice::STATUS_DRAFT,
            ],
            'urls' => [
                'index' => route('finance.invoices.index'),
                'issue' => route('finance.invoices.issue', $invoice),
                'pay' => route('finance.invoices.pay', $invoice),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function issue(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeFinance($request);
        $this->ensureBranch($request, $invoice);
        abort_unless($this->canPay($request->user()), 403);

        try {
            $this->invoices->issue($invoice);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('finance.invoices.show', $invoice)
            ->with('success', localize('global.invoice_issued_successfully'));
    }

    public function pay(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeFinance($request);
        $this->ensureBranch($request, $invoice);
        abort_unless($this->canPay($request->user()), 403);

        $data = $request->validate([
            'method' => 'nullable|in:cash,card,other',
            'notes' => 'nullable|string|max:1000',
            'amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $receipt = $this->invoices->markPaid($invoice, $data);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('finance.invoices.show', $invoice)
            ->with('success', localize('global.invoice_paid_successfully'))
            ->with('receipt_uuid', $receipt->uuid);
    }

    public function printReceipt(Request $request, PaymentReceipt $receipt): SymfonyResponse
    {
        $this->authorizeFinance($request);
        $receipt->load(['invoice.branch', 'payment']);

        if ((int) $receipt->invoice?->branch_id !== (int) $request->user()->branch_id
            && ! $request->user()->hasRole(['admin', 'super_admin'])) {
            abort(403);
        }

        return response()->view('pages.finance.receipt', [
            'receipt' => $receipt,
            'snapshot' => $receipt->snapshot ?? [],
        ]);
    }

    private function authorizeFinance(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user->hasRole(['admin', 'super_admin'])
            || $user->can('access-finance')
            || $user->can('manage-finance'),
            403
        );
    }

    private function canPay($user): bool
    {
        return $user->hasRole(['admin', 'super_admin'])
            || $user->can('manage-finance')
            || $user->can('pay-invoices');
    }

    private function ensureBranch(Request $request, Invoice $invoice): void
    {
        $user = $request->user();
        if ($user->hasRole(['admin', 'super_admin'])) {
            return;
        }

        abort_unless((int) $invoice->branch_id === (int) $user->branch_id, 403);
    }
}
