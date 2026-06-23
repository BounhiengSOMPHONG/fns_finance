<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $normalizedFilters = $this->normalizedIndexFilters($request);

        if ($this->indexFiltersNeedRedirect($request, $normalizedFilters)) {
            return redirect()->route('admin.users.index', $normalizedFilters);
        }

        $request->merge($normalizedFilters);

        $query = User::with(['role', 'department']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role_id')) {
            $roleId = $request->role_id;

            if (! is_numeric($roleId)) {
                $roleId = Role::where('role_name', $roleId)->value('id');
            }

            $query->where('role_id', $roleId);
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by status
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        $users = $query->latest('id')->paginate(10)->withQueryString();
        $roles = Role::orderBy('role_name')->get();
        $departments = Department::orderBy('department_name')->get();

        return view('dashboards.admin.users.index', compact('users', 'roles', 'departments'));
    }

    private function normalizedIndexFilters(Request $request): array
    {
        $filters = [];

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $filters['search'] = $search;
        }

        $roleId = trim((string) $request->input('role_id', ''));
        if ($roleId !== '') {
            if (! is_numeric($roleId)) {
                $roleId = (string) Role::where('role_name', $roleId)->value('id');
            }

            if ($roleId !== '') {
                $filters['role_id'] = $roleId;
            }
        }

        $departmentId = trim((string) $request->input('department_id', ''));
        if ($departmentId !== '') {
            $filters['department_id'] = $departmentId;
        }

        $isActive = trim((string) $request->input('is_active', ''));
        if (in_array($isActive, ['0', '1'], true)) {
            $filters['is_active'] = $isActive;
        }

        return $filters;
    }

    private function indexFiltersNeedRedirect(Request $request, array $normalizedFilters): bool
    {
        $filterKeys = ['search', 'role_id', 'department_id', 'is_active'];
        $currentFilters = [];

        foreach ($filterKeys as $key) {
            if ($request->query->has($key)) {
                $currentFilters[$key] = (string) $request->query($key);
            }
        }

        return $currentFilters !== $normalizedFilters;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::orderBy('role_name')->get();
        $departments = Department::orderBy('department_name')->get();

        return view('dashboards.admin.users.create', compact('roles', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:6',
            'full_name' => 'required|string|max:100',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active');

        User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'สร้างผู้ใช้งานสำเร็จ');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['role', 'department']);

        return view('dashboards.admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('role_name')->get();
        $departments = Department::orderBy('department_name')->get();

        return view('dashboards.admin.users.edit', compact('user', 'roles', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'full_name' => 'required|string|max:100',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'อัปเดตผู้ใช้งานสำเร็จ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'ไม่สามารถลบบัญชีตัวเองได้');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'ลบผู้ใช้งานสำเร็จ');
    }
}
