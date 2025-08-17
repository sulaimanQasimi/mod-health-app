<?php

namespace App\Http\Controllers;

use App\Models\MiliteryType;
use Illuminate\Http\Request;

class MiliteryTypeController extends Controller
{


    public function index()
    {
        $this->authorize('viewAny', MiliteryType::class);
        $militeryTypes = MiliteryType::all();
        return view('pages.militery_types.index', compact('militeryTypes'));
    }

    public function create()
    {
        $this->authorize('create', MiliteryType::class);
        return view('pages.militery_types.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', MiliteryType::class);
        $data = $request->validate([
            'name' => 'required',
        ]);

        MiliteryType::create($data);

        return redirect()->route('militery_types.index')->with('success', localize('global.militery_type_created_successfully.'));
    }

    public function edit(MiliteryType $militeryType)
    {
        $this->authorize('update', $militeryType);
        return view('pages.militery_types.edit', compact('militeryType'));
    }

    public function update(Request $request, MiliteryType $militeryType)
    {
        $this->authorize('update', $militeryType);
        $data = $request->validate([
            'name' => 'required',
        ]);

        $militeryType->update($data);

        return redirect()->route('militery_types.index')->with('success', localize('global.militery_type_updated_successfully.'));
    }

    public function destroy(MiliteryType $militeryType)
    {
        $this->authorize('delete', $militeryType);
        $militeryType->delete();

        return redirect()->route('militery_types.index')->with('success', localize('global.militery_type_deleted_successfully.'));
    }
}
