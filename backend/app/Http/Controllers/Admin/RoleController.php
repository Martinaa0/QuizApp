<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->withCount('users')->get();

        return response()->json($roles);
    }

    public function show(string $id): JsonResponse
    {
        $role = Role::with(['permissions', 'users'])->findOrFail($id);

        return response()->json($role);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create($request->only('name', 'description'));

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role->load('permissions'),
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update($request->only('name', 'description'));

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role->load('permissions'),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        // Sprječavanje brisanja sistemskih uloga
        if (in_array($role->name, ['super_admin', 'admin', 'teacher', 'student'])) {
            return response()->json([
                'message' => 'Cannot delete system roles.',
            ], 403);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully',
        ], 204);
    }
}
