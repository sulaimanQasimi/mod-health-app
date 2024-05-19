<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\Province;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('pages.dashboard.index');
    }

    public function changeLanguage($lang)
    {
        Session()->put('language', $lang);

        return redirect()->back();
    }

    public function getRelatedDistricts($provinceId)
    {
        $province = Province::findOrFail($provinceId);
        $districts = $province->districts;
        $options = '<option value = "">Select District</option>';

        foreach($districts as $district)
        {
            $options .='<option value = "' .$district->id . '">' . $district->name_dr. '</option>';
        }

        return $options;
    }

    public function getRelatedDepartments($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $departments = $branch->departments;
        $options = '<option value = "">Select Department</option>';

        foreach($departments as $department)
        {
            $options .='<option value = "' .$department->id . '">' . $department->name. '</option>';
        }

        return $options;
    }

    public function getRelatedSections($depId)
    {
        $department = Department::findOrFail($depId);
        $sections = $department->sections;
        $options = '<option value = "">Select Department</option>';

        foreach($sections as $section)
        {
            $options .='<option value = "' .$section->id . '">' . $section->name. '</option>';
        }

        return $options;
    }

    public function getRelatedDoctors($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $doctors = $department->doctors;
        $options = '<option value = "">Select Department</option>';

        foreach($doctors as $doctor)
        {
            $options .='<option value = "' .$doctor->id . '">' . $doctor->name. '</option>';
        }

        return $options;
    }

    public function getRelatedLabTypes($labTypeId)
    {
        $labTypeSection = LabTypeSection::findOrFail($labTypeId);
        $labTypes = $labTypeSection->labTypes;
        $options = '<option value = "">Select Department</option>';

        foreach($labTypes as $labType)
        {
            $options .='<option value = "' .$labType->id . '">' . $labType->name. '</option>';
        }

        return $options;
    }

    public function getLabTypeTests($labTypeId)
    {
         // Retrieve the lab type tests based on the $labTypeId
         $labTypeTests = LabType::where('parent_id', $labTypeId)->get();

         // Return the lab type tests as JSON response
         return response()->json($labTypeTests);
    }

    public function getBranchDoctors($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $doctors = $branch->doctors;
        $options = '<option value = "">Select Doctor</option>';

        foreach($doctors as $doctor)
        {
            $options .='<option value = "' .$doctor->id . '">' . $doctor->name. '</option>';
        }

        return $options;
    }
}
