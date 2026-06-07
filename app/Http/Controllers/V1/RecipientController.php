<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Recipient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RecipientController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Recipient::class);

        $query = Recipient::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('Recipients/Index', [
            'recipients' => $this->paginationPayload($paginator, fn (Recipient $recipient) => [
                'id' => $recipient->id,
                'name' => $recipient->name,
                'description' => $recipient->description,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-recipients',
                'edit-recipients',
            ),
            'urls' => [
                'index' => route('react.recipients.index'),
                'create' => route('react.recipients.create'),
                'edit' => url('/react/recipients'),
                'destroy' => url('/react/recipients'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Recipient::class);

        return Inertia::render('Recipients/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Recipient::class);

        $data = $request->validate([
            'name' => 'required|string|max:191|unique:recipients,name',
            'description' => 'nullable|string',
        ]);

        Recipient::create($data);

        return redirect()
            ->route('react.recipients.index')
            ->with('success', localize('global.add_success_recipient'));
    }

    public function edit(Request $request, Recipient $recipient): Response
    {
        $this->authorize('update', $recipient);

        return Inertia::render('Recipients/Edit', [
            'recipient' => [
                'id' => $recipient->id,
                'name' => $recipient->name,
                'description' => $recipient->description ?? '',
            ],
            'urls' => $this->formUrls($recipient),
        ]);
    }

    public function update(Request $request, Recipient $recipient): RedirectResponse
    {
        $this->authorize('update', $recipient);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('recipients', 'name')->ignore($recipient->id),
            ],
            'description' => 'nullable|string',
        ]);

        $recipient->update($data);

        return redirect()
            ->route('react.recipients.index')
            ->with('success', localize('global.recipient_update_success'));
    }

    public function destroy(Request $request, Recipient $recipient): RedirectResponse
    {
        $this->authorize('delete', $recipient);

        $recipient->delete();

        return redirect()
            ->route('react.recipients.index')
            ->with('success', localize('global.recipient_update_success'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Recipient $recipient = null): array
    {
        return [
            'index' => route('react.recipients.index'),
            'store' => route('react.recipients.store'),
            'update' => $recipient ? route('react.recipients.update', $recipient) : '',
            'back' => route('react.recipients.index'),
        ];
    }
}
