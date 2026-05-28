<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(): Response
    {
        $departments = Department::withCount('users')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($d) => [
                'id'          => $d->id,
                'name'        => $d->name,
                'code'        => $d->code,
                'sort_order'  => $d->sort_order,
                'is_active'   => $d->is_active,
                'users_count' => $d->users_count,
            ]);

        $users = User::with('department:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'              => $u->id,
                'name'            => $u->name,
                'email'           => $u->email,
                'role'            => $u->role,
                'department_id'   => $u->department_id,
                'department_name' => $u->department?->name,
                'is_active'       => $u->is_active,
                'last_login_at'   => $u->last_login_at?->format('Y/m/d H:i'),
            ]);

        return Inertia::render('Admin/Index', [
            'departments' => $departments,
            'users'       => $users,
        ]);
    }

    // ── 部署 ────────────────────────────────────────────────────

    public function storeDepartment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'code'       => 'required|string|max:20|unique:departments,code',
            'sort_order' => 'integer|min:0',
            'is_active'  => 'boolean',
        ]);

        Department::create($validated);

        return back()->with('success', '部署を登録しました。');
    }

    public function updateDepartment(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'code'       => 'required|string|max:20|unique:departments,code,' . $department->id,
            'sort_order' => 'integer|min:0',
            'is_active'  => 'boolean',
        ]);

        $department->update($validated);

        return back()->with('success', '部署を更新しました。');
    }

    public function destroyDepartment(Department $department): RedirectResponse
    {
        if ($department->users()->exists()) {
            return back()->with('error', 'この部署には社員が所属しているため削除できません。');
        }

        $department->delete();

        return back()->with('success', '部署を削除しました。');
    }

    // ── 社員 ────────────────────────────────────────────────────

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|max:255|unique:users,email',
            'role'          => 'required|in:admin,manager,sales,viewer',
            'department_id' => 'nullable|exists:departments,id',
            'is_active'     => 'boolean',
        ]);

        User::create($validated);

        return back()->with('success', '社員を登録しました。');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'role'          => 'required|in:admin,manager,sales,viewer',
            'department_id' => 'nullable|exists:departments,id',
            'is_active'     => 'boolean',
        ]);

        if ($user->id === Auth::id() && $validated['role'] !== 'admin') {
            return back()->with('error', '自分自身のロールを変更することはできません。');
        }

        $user->update($validated);

        return back()->with('success', '社員情報を更新しました。');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', '自分自身を削除することはできません。');
        }

        $user->delete();

        return back()->with('success', '社員を削除しました。');
    }
}
