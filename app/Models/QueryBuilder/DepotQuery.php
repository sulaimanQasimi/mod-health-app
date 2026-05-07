<?php
namespace App\Models\QueryBuilder;

use App\Models\Depot;

class DepotQuery
{
    public function getDepots()
    {
        return Depot::query()
            ->with('parentDepot')
            ->with('createdBy')
            ->with('updatedBy')
            ->with('deletedBy')
            ->get();
    }
    public function getDepotsByBranch($branchId)
    {
        return Depot::query()
            ->where('branch_id', $branchId)
            ->get();
    }
    public function getDepotsByDepartment($departmentId)
    {
        return Depot::query()
            ->where('department_id', $departmentId)
            ->get();
    }
    public function getDepotsByPharmacy($pharmacyId)
    {
        return Depot::query()
            ->where('pharmacy_id', $pharmacyId)
            ->get();
    }
    public function getDepotsByParentDepot($parentDepotId)
    {
        return Depot::query()
            ->where('parent_depot_id', $parentDepotId)
            ->get();
    }
    
}
