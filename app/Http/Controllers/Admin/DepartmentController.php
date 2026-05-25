<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Department::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('department_name', 'like', "%{$search}%")
                    ->orWhere('department_type', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('department_type')) {
            $query->where('department_type', $request->department_type);
        }

        $departments = $query->withCount('users')->latest('id')->paginate(10)->withQueryString();
        
        // Get unique department types for filter (cached for 1 hour)
        $departmentTypes = cache()->remember('department_types', 3600, function () {
            return Department::distinct()->pluck('department_type')->filter()->values();
        });

        return view('admin.departments.index', compact('departments', 'departmentTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:100',
            'department_type' => 'required|string|max:50',
        ]);

        Department::create($validated);

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'สร้างแผนกสำเร็จ');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        $department->load(['users' => function ($query) {
            $query->with('role')->latest('id')->limit(10);
        }]);

        return view('admin.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:100',
            'department_type' => 'required|string|max:50',
        ]);

        $department->update($validated);

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'อัปเดตแผนกสำเร็จ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        // Check if department has users (use withCount from index if loaded)
        if ($department->users_count > 0 || $department->users()->count() > 0) {
            return redirect()
                ->route('admin.departments.index')
                ->with('error', 'ไม่สามารถลบแผนกนี้ได้เนื่องจากมีผู้ใช้งานอยู่');
        }

        $department->delete();
        
        // Clear cache when department is deleted
        cache()->forget('department_types');

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'ลบแผนกสำเร็จ');
    }
}
