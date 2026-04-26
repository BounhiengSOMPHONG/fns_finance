<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('role_name', 'like', "%{$search}%");
        }

        $perPage = request('per_page', 25);
        if ($perPage === 'all') {
            $perPage = $query->count() > 0 ? $query->count() : 1;
        } else {
            $perPage = (int) $perPage;
        }
        $roles = $query->latest('id')->paginate($perPage)->withQueryString();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:50|unique:roles',
        ]);

        Role::create($validated);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'ສ້າງບົດບາດສຳເລັດ');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load(['users' => function ($query) {
            $query->with('department')->latest('id')->limit(10);
        }]);

        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'role_name' => ['required', 'string', 'max:50', Rule::unique('roles')->ignore($role->id)],
        ]);

        $role->update($validated);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'ອັບເດດບົດບາດສຳເລັດ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'ບໍ່ສາມາດລຶບບົດບາດນີ້ໄດ້ເນື່ອງຈາກຍັງມີຜູ້ໃຊ້ຢູ່');
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'ລຶບບົດບາດສຳເລັດ');
    }
    /**
     * Remove the specified resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:roles,id',
        ]);

        $ids = $request->ids;
        $deletedCount = 0;
        $failedCount = 0;

        foreach ($ids as $id) {
            $role = Role::find($id);
            if ($role) {
                if ($role->users()->count() > 0) {
                    $failedCount++;
                } else {
                    $role->delete();
                    $deletedCount++;
                }
            }
        }

        $message = "ລຶບບົດບາດທີ່ເລືອກສຳເລັດ $deletedCount ລາຍການ.";
        if ($failedCount > 0) {
            $message .= " (ບໍ່ສາມາດລຶບ $failedCount ລາຍການເນື່ອງຈາກຍັງມີຜູ້ໃຊ້ງານຢຸ່)";
        }

        return redirect()->route('admin.roles.index')->with('success', $message);
    }
}
