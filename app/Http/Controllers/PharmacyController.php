<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:pharmacy.index')->only('index');
        $this->middleware('permission:pharmacy.create')->only(['create', 'store']);
        $this->middleware('permission:pharmacy.edit')->only(['edit', 'update']);
        $this->middleware('permission:pharmacy.delete')->only('destroy');
        $this->middleware('permission:pharmacy.show')->only('show');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $pharmacies = Pharmacy::with(['user'])->get();

            if ($pharmacies) {
                return response()->json([
                    'data' => $pharmacies,
                ]);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                    'code' => 500,
                    'data' => [],
                ]);
            }
        }

        $pharmacies = Pharmacy::with(['user'])->get();
        return view('pages.pharmacies.index', compact('pharmacies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('pages.pharmacies.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:191',
            'address' => 'required|string',
            'user_id' => 'required|exists:users,id|unique:pharmacies,user_id',
        ]);

        $pharmacy = new Pharmacy();
        $pharmacy->name = $request->name;
        $pharmacy->phone = $request->phone;
        $pharmacy->address = $request->address;
        $pharmacy->user_id = $request->user_id;
        $pharmacy->created_by = Auth::id();
        $pharmacy->save();

        return redirect()->route('pharmacies.index')->with('success', localize('global.pharmacy_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pharmacy = Pharmacy::with(['user', 'createdBy', 'updatedBy'])->findOrFail($id);
        return view('pages.pharmacies.show', compact('pharmacy'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $users = User::all();
        return view('pages.pharmacies.edit', compact('pharmacy', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:191',
            'address' => 'required|string',
            'user_id' => 'required|exists:users,id|unique:pharmacies,user_id,' . $id,
        ]);

        $pharmacy = Pharmacy::findOrFail($id);
        $pharmacy->name = $request->name;
        $pharmacy->phone = $request->phone;
        $pharmacy->address = $request->address;
        $pharmacy->user_id = $request->user_id;
        $pharmacy->updated_by = Auth::id();
        $pharmacy->save();

        return redirect()->route('pharmacies.index')->with('success', localize('global.pharmacy_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $pharmacy->deleted_by = Auth::id();
        $pharmacy->save();
        $pharmacy->delete();

        return redirect()->route('pharmacies.index')->with('success', localize('global.pharmacy_deleted_successfully'));
    }
}
