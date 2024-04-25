<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipientRequest;
use Illuminate\Http\Request;
use App\Models\Recipient;
use Exception;
use Illuminate\Support\Facades\DB;

class RecipientController extends Controller
{
    public function __construct()
    {
        // $this->middleware(['permission:create-recipients|edit-recipients'], ['only' => ['store','update']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    if ($request->ajax()) {
        $recipients = Recipient::all();

        if ($recipients) {
            return response()->json([
                'data' => $recipients,
            ]);
        } else {
            return response()->json([
                'message' => 'Internal Server Error',
                'code' => 500,
                'data' => [],
            ]);
        }
    }

    return view('pages.recipients.index');
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $recipients = Recipient::all();
        return view('pages.recipients.create', compact('recipients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecipientRequest $request)
    {

        DB::beginTransaction();
        try {
            Recipient::create($request->input());
            DB::commit();
                return redirect()->route('recipients.index')->with('success', localize('global.add_success_recipient'));
            }
            catch (Exception $e) {
                DB::rollback();

                return redirect()->back()->with('error',$e->getMessage());
            }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recipient $recipient)
    {
        $recipients = Recipient::all();
        return view('pages.recipients.edit', compact('recipients','recipient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recipient $recipient)
    {
        $recipient->update($request->input());
        return redirect()->route('recipients.index')->with('success', localize('global.recipient_update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $recipient = Recipient::where('id', $id);

        $recipient->delete();

        return redirect()->back();
    }

}
