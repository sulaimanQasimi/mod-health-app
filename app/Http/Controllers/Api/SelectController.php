<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SelectController extends Controller
{

    public function getPhysiotherapyTypes(Request $request)
    {
        $physiotherapyTypes = \App\Models\PhysiotherapyType::when($request->search, function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search . '%');
        })

            ->get()
            ->map(function ($item) {
                return [
                    'key' => $item->id,
                    'value' => $item->name
                ];
            });
        return response()->json($physiotherapyTypes);
    }

    public function getPhysiotherapists(Request $request)
    {
        $physiotherapists = \App\Models\User::when($request->search, function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search . '%');
        })
            ->get()
            ->map(function ($item) {
                return [
                    'key' => $item->id,
                    'value' => $item->name
                ];
            });
        return response()->json($physiotherapists);
    }
    public function users(Request $request)
    {
        $users = \App\Models\User::when($request->search, function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search . '%');
        })
            ->get()
            ->map(function ($item) {
                return [
                    'key' => $item->id,
                    'value' => $item->name
                ];
            });
        return response()->json($users);
    }
}