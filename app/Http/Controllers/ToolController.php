<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Unit;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function index(Request $request)
    {
        $query = Tool::query()->with('unit');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $tools = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('pages.tools.index', compact('tools'));
    }

    public function create()
    {
        $units = Unit::query()->where('is_active', true)->orderBy('name')->get();

        return view('pages.tools.create', compact('units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:64', 'unique:tools,code'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Tool::create([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('tools.index')->with('success', 'Tool created successfully.');
    }

    public function edit(Tool $tool)
    {
        $units = Unit::query()->where('is_active', true)->orderBy('name')->get();

        return view('pages.tools.edit', compact('tool', 'units'));
    }

    public function update(Request $request, Tool $tool)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:64', 'unique:tools,code,' . $tool->id],
            'unit_id' => ['nullable', 'exists:units,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $tool->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('tools.index')->with('success', 'Tool updated successfully.');
    }

    public function destroy(Tool $tool)
    {
        $tool->delete();

        return redirect()->route('tools.index')->with('success', 'Tool deleted successfully.');
    }
}
