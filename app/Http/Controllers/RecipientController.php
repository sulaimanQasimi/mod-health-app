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
        $recipients = Recipient::with('children')->get();

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
     * Display the specified resource.
     */
    public function show(Recipient $recipient)
    {
        return view('pages.recipients.show', compact('recipient'));
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

    public function getRecipients(Request $request)
    {
        $recipientIds = $request->input('recipient_ids', []);

        // Retrieve the recipient names based on the IDs
        $recipients = Recipient::whereIn('id', $recipientIds)->get();

        return response()->json($recipients);
    }


    public function ajaxSearch(Request $request)
    {

      $search = $request->searchTerm;
      if ($search) {

        $recipients = Recipient::select('name_dr', 'id')->where('order_or_document', '0')->where('name_dr', 'LIKE', '%' . $search . '%')->limit(20)->get();
        if ($request->search_type == 1) {

          $recipients = Recipient::select('name_dr', 'id')->where('order_or_document', '0')->where('name_dr', 'LIKE', '%' . $search . '%')->limit(20)->get();
          return response()->json($recipients);
        } else {
          $data = array();
          foreach ($recipients as $item) {
            $data[] = array('id' => $item->id, 'text' => $item->name_dr);
          }
          return json_encode($data);
        }
      } elseif (isset($request->dep_type) && $request->dep_type == 6) {

        $recipients = Recipient::select('name_dr', 'id', 'parent_id')->where('order_or_document', '0')->whereCategory('6')->get();
        return response()->json($recipients);
      }
    }
}
