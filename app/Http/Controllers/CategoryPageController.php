<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryPageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $query = \App\Models\Category::query();

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->latest()->paginate(15)->withQueryString();

        return view('pages.categories.index', compact('categories'));
    }
}
