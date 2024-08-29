<?php

namespace App\Http\Controllers;

use App\Models\FoodType;
use Illuminate\Http\Request;

class FoodTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $foodTypes = FoodType::paginate(15);
        return view('pages.food_types.index',compact('foodTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.food_types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
        ]);

        FoodType::create($data);

        return redirect()->route('food_types.index')->with('success', localize('global.food_type_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(FoodType $foodType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FoodType $foodType)
    {
        return view('pages.food_types.edit',compact('foodType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FoodType $foodType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $foodType->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('food_types.index')
            ->with('success', localize('global.food_type_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FoodType $foodType)
    {
        $foodType->delete();
        return redirect()->route('food_types.index')
                         ->with('success', 'Food type deleted successfully.');
    }
}
