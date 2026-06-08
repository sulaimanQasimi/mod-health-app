<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\AuthorizesPharmacyStockAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Income;
use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncomeController extends Controller
{
    use AuthorizesPharmacyStockAccess;
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'income_type', 'date_from', 'date_to', 'sort_by', 'sort_order', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorizePharmacyManager($request->user());

        $query = Income::query()->with(['medicine', 'createdBy', 'branch']);
        $branchId = $request->user()->branch_id;

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->whereHas('medicine', fn ($medicineQuery) => $medicineQuery->where('name', 'like', "%{$search}%"))
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('income_type')) {
            $query->where('income_type', $request->income_type);
        }

        if ($from = $this->parseOptionalDate($request->date_from)) {
            $query->where('purchase_date', '>=', $from);
        }

        if ($to = $this->parseOptionalDate($request->date_to)) {
            $query->where('purchase_date', '<=', $to);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $paginator = $this->paginateQuery($query, $request, 15);

        return Inertia::render('Incomes/Index', [
            'incomes' => $this->paginationPayload($paginator, fn (Income $income) => [
                'id' => $income->id,
                'medicine_name' => $income->medicine?->name,
                'amount' => $income->amount,
                'batch_number' => $income->batch_number,
                'supplier_name' => $income->supplier_name,
                'purchase_price' => $income->purchase_price,
                'purchase_date' => $income->purchase_date?->format('Y-m-d'),
                'income_type' => $income->income_type,
                'branch_name' => $income->branch?->name,
                'created_by_name' => $income->createdBy ? trim("{$income->createdBy->name} {$income->createdBy->last_name}") : null,
                'created_at' => $income->created_at?->format('Y-m-d H:i'),
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'incomeTypes' => ['purchase', 'return', 'donation', 'transfer', 'completion'],
            ],
            'permissions' => $this->incomePermissions($request->user()),
            'urls' => [
                'index' => route('react.incomes.index'),
                'create' => route('react.incomes.create'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizePharmacyManager($request->user());

        return Inertia::render('Incomes/Create', [
            'formData' => [
                'medicines' => Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']),
                'incomeTypes' => ['purchase', 'return', 'donation', 'transfer', 'completion'],
            ],
            'urls' => [
                'index' => route('react.incomes.index'),
                'store' => route('react.incomes.store'),
                'back' => route('react.incomes.index'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePharmacyManager($request->user());

        $data = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'amount' => 'required|integer|min:1',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date|after:today',
            'supplier_name' => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|string',
            'income_type' => 'required|in:purchase,return,donation,transfer,completion',
            'notes' => 'nullable|string',
        ]);

        if (! empty($data['purchase_date'])) {
            $data['purchase_date'] = $this->parseOptionalDate($data['purchase_date']);
        }

        $data['branch_id'] = $request->user()->branch_id;
        Income::create($data);

        return redirect()
            ->route('react.incomes.index')
            ->with('success', localize('global.income_record_created_successfully'));
    }
}
