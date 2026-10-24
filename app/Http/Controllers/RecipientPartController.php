<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipientPartRequest;
use App\Models\Recipient;
use App\Models\RecipientPart;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RecipientPartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $recipientParts = RecipientPart::query()
                ->with('recipient:id,name')
                ->get()
                ->map(fn (RecipientPart $part) => [
                    'id' => $part->id,
                    'name' => $part->name,
                    'code' => $part->code,
                    'recipient_name' => $part->recipient?->name,
                ]);

            return response()->json([
                'data' => $recipientParts,
            ]);
        }

        return view('pages.recipient-parts.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $recipients = Recipient::orderBy('name')->get();

        return view('pages.recipient-parts.create', compact('recipients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecipientPartRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['code'] = $this->generateUniqueCode((int) $data['recipient_id'], $data['name']);

            RecipientPart::create($data);
            DB::commit();

            return redirect()
                ->route('recipient-parts.index')
                ->with('success', localize('global.recipient_part_created_successfully.'));
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RecipientPart $recipientPart)
    {
        $recipients = Recipient::orderBy('name')->get();

        return view('pages.recipient-parts.edit', compact('recipients', 'recipientPart'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecipientPart $recipientPart)
    {
        $validated = $request->validate([
            'recipient_id' => ['required', 'exists:recipients,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('recipient_parts', 'name')
                    ->where(fn ($query) => $query->where('recipient_id', $request->input('recipient_id')))
                    ->ignore($recipientPart->id),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('recipient_parts', 'code')
                    ->where(fn ($query) => $query->where('recipient_id', $request->input('recipient_id')))
                    ->ignore($recipientPart->id),
            ],
        ]);

        $recipientPart->update($validated);

        return redirect()
            ->route('recipient-parts.index')
            ->with('success', localize('global.recipient_part_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        RecipientPart::where('id', $id)->delete();

        return redirect()->back()
            ->with('success', localize('global.recipient_part_deleted_successfully.'));
    }

    private function generateUniqueCode(int $recipientId, string $name): string
    {
        $base = Str::upper(Str::limit(preg_replace('/\s+/', '-', trim($name)) ?: 'PART', 40, ''));

        $code = $base;
        $suffix = 1;

        while (
            RecipientPart::where('recipient_id', $recipientId)
                ->where('code', $code)
                ->exists()
        ) {
            $code = Str::limit($base, 40, '').'-'.$suffix;
            $suffix++;
        }

        return Str::limit($code, 50, '');
    }
}
