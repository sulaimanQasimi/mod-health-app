<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\PhysiotherapyType;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

class SelectController extends Controller
{
    /**
     * Get physiotherapy types for select2 dropdown
     */

    public function getPhysiotherapyTypes(Request $request): JsonResponse
    {
        try {
            $query = PhysiotherapyType::query();
            
            // Apply search filter if provided
            if ($request->filled('search')) {
                $searchTerm = trim($request->search);
                $query->where('name', 'like', "%{$searchTerm}%");
            }
            
            // Get results with pagination support
            $physiotherapyTypes = $query
                ->orderBy('name', 'asc')
                ->limit(50)
                ->get(['id', 'name'])
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'text' => $item->name,
                        // Legacy support
                        'key' => $item->id,
                        'value' => $item->name
                    ];
                });
            
            return response()->json([
                'results' => $physiotherapyTypes,
                'pagination' => ['more' => false]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching physiotherapy types: ' . $e->getMessage());
            return response()->json([
                'results' => [],
                'error' => 'Failed to fetch physiotherapy types'
            ], 500);
        }
    }

    /**
     * Get physiotherapists (users) for select2 dropdown
     */
    public function getPhysiotherapists(Request $request): JsonResponse
    {
        try {
            $query = User::query();
            
            // Apply search filter if provided
            if ($request->filled('search')) {
                $searchTerm = trim($request->search);
                $query->where(function (Builder $q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%");
                });
            }
            
            // Filter by branch if user has branch_id
            if (auth()->check() && auth()->user()->branch_id) {
                $query->where('branch_id', auth()->user()->branch_id);
            }
            
            // Get results
            $physiotherapists = $query
                ->orderBy('name', 'asc')
                ->limit(50)
                ->get(['id', 'name', 'email'])
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'text' => $item->name . ($item->email ? ' (' . $item->email . ')' : ''),
                        // Legacy support
                        'key' => $item->id,
                        'value' => $item->name
                    ];
                });
            
            return response()->json([
                'results' => $physiotherapists,
                'pagination' => ['more' => false]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching physiotherapists: ' . $e->getMessage());
            return response()->json([
                'results' => [],
                'error' => 'Failed to fetch physiotherapists'
            ], 500);
        }
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

    /**
     * Get pharmacy users for Select2 dropdown (e.g. prescriptions report updated_by filter).
     * Requires pharmacy_id; returns users belonging to that pharmacy.
     */
    public function getPharmacyUsers(Request $request): JsonResponse
    {
        try {
            if (!$request->filled('pharmacy_id')) {
                return response()->json([
                    'results' => [],
                    'pagination' => ['more' => false]
                ]);
            }

            $pharmacy = Pharmacy::find($request->pharmacy_id);
            if (!$pharmacy) {
                return response()->json([
                    'results' => [],
                    'pagination' => ['more' => false]
                ]);
            }

            $query = $pharmacy->activeUsers();

            if ($request->filled('search')) {
                $searchTerm = trim($request->search);
                $query->where(function (Builder $q) use ($searchTerm) {
                    $q->where('users.name', 'like', "%{$searchTerm}%")
                      ->orWhere('users.email', 'like', "%{$searchTerm}%");
                });
            }

            $users = $query
                ->orderBy('users.name', 'asc')
                ->limit(50)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'text' => $item->name . ($item->email ? ' (' . $item->email . ')' : ''),
                        'key' => $item->id,
                        'value' => $item->name
                    ];
                });

            return response()->json([
                'results' => $users,
                'pagination' => ['more' => false]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching pharmacy users: ' . $e->getMessage());
            return response()->json([
                'results' => [],
                'error' => 'Failed to fetch pharmacy users'
            ], 500);
        }
    }

    /**
     * Get rooms for Select2 AJAX dropdown (e.g. room management, move patient).
     * Filters by branch_id when user has one.
     */
    public function getRooms(Request $request): JsonResponse
    {
        try {
            $query = Room::query()->select('id', 'name');

            if (auth()->check() && auth()->user()->branch_id) {
                $query->where('branch_id', auth()->user()->branch_id);
            }

            if ($request->filled('search')) {
                $searchTerm = trim($request->search);
                $query->where('name', 'like', "%{$searchTerm}%");
            }

            $rooms = $query
                ->orderBy('name', 'asc')
                ->limit(50)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'text' => $item->name,
                    ];
                });

            return response()->json([
                'results' => $rooms,
                'pagination' => ['more' => false],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching rooms for select: ' . $e->getMessage());
            return response()->json([
                'results' => [],
                'error' => 'Failed to fetch rooms',
            ], 500);
        }
    }
}