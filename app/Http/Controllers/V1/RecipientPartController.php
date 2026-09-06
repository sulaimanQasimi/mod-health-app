<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Recipient;
use App\Models\RecipientPart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RecipientPartController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'recipient_id', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', RecipientPart::class);

        $query = RecipientPart::query()->with('recipient:id,name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('recipient_id')) {
            $query->where('recipient_id', $request->recipient_id);
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('RecipientParts/Index', [
            'recipientParts' => $this->paginationPayload($paginator, fn (RecipientPart $part) => [
                'id' => $part->id,
                'name' => $part->name,
                'code' => $part->code,
                'recipient_name' => $part->recipient?->name,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'recipients' => Recipient::query()->orderBy('name')->get(['id', 'name']),
            ],
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-recipient-parts',
                'edit-recipient-parts',
                'delete-recipient-parts',
            ),
            'urls' => [
                'index' => route('recipient-parts.index'),
                'create' => route('recipient-parts.create'),
                'edit' => url('/recipient-parts'),
                'destroy' => url('/recipient-parts'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', RecipientPart::class);

        return Inertia::render('RecipientParts/Create', [
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', RecipientPart::class);

        RecipientPart::create($this->validateRecipientPart($request));

        return redirect()
            ->route('recipient-parts.index')
            ->with('success', localize('global.recipient_part_created_successfully.'));
    }

    public function edit(Request $request, RecipientPart $recipientPart): Response
    {
        $this->authorize('update', $recipientPart);

        return Inertia::render('RecipientParts/Edit', [
            'recipientPart' => [
                'id' => $recipientPart->id,
                'name' => $recipientPart->name,
                'code' => $recipientPart->code,
                'recipient_id' => (string) $recipientPart->recipient_id,
            ],
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls($recipientPart),
        ]);
    }

    public function update(Request $request, RecipientPart $recipientPart): RedirectResponse
    {
        $this->authorize('update', $recipientPart);

        $recipientPart->update($this->validateRecipientPart($request, $recipientPart));

        return redirect()
            ->route('recipient-parts.index')
            ->with('success', localize('global.recipient_part_updated_successfully.'));
    }

    public function destroy(Request $request, RecipientPart $recipientPart): RedirectResponse
    {
        $this->authorize('delete', $recipientPart);

        $recipientPart->delete();

        return redirect()
            ->route('recipient-parts.index')
            ->with('success', localize('global.recipient_part_deleted_successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        return [
            'recipients' => Recipient::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?RecipientPart $recipientPart = null): array
    {
        return [
            'index' => route('recipient-parts.index'),
            'store' => route('recipient-parts.store'),
            'update' => $recipientPart ? route('recipient-parts.update', $recipientPart) : '',
            'back' => route('recipient-parts.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRecipientPart(Request $request, ?RecipientPart $recipientPart = null): array
    {
        $recipientId = $request->input('recipient_id');

        return $request->validate([
            'recipient_id' => 'required|exists:recipients,id',
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('recipient_parts', 'name')
                    ->where(fn ($query) => $query->where('recipient_id', $recipientId))
                    ->ignore($recipientPart?->id),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('recipient_parts', 'code')
                    ->where(fn ($query) => $query->where('recipient_id', $recipientId))
                    ->ignore($recipientPart?->id),
            ],
        ]);
    }
}
