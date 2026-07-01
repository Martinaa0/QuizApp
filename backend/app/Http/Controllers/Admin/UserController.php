<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search by name, username, or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by user type
        if ($request->has('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        $users = $query->withCount(['quizzes', 'quizAttempts'])->latest()->paginate(15);

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $userType = $request->user_type ?? 'student';

        // Samo super_admin može kreirati admin ili super_admin korisnike
        if (in_array($userType, ['admin', 'super_admin']) && $request->user()->user_type !== 'super_admin') {
            return response()->json([
                'message' => 'Samo super administrator može kreirati administratore.',
            ], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $userType,
        ]);

        // Dodjela uloge
        $role = Role::where('name', $userType)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('roles'),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with(['quizzes', 'quizAttempts'])->findOrFail($id);

        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validated();

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Samo super_admin može promijeniti ulogu u admin/super_admin
        if (isset($data['user_type']) && in_array($data['user_type'], ['admin', 'super_admin']) && $request->user()->user_type !== 'super_admin') {
            return response()->json([
                'message' => 'Samo super administrator može dodijeliti admin uloge.',
            ], 403);
        }

        $oldType = $user->user_type;
        $user->update($data);

        // Ako se promijenio user_type, ažuriraj i ulogu
        if (isset($data['user_type']) && $data['user_type'] !== $oldType) {
            $newRole = Role::where('name', $data['user_type'])->first();
            if ($newRole) {
                $user->roles()->sync([$newRole->id]);
            }
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);

        // Samo super_admin može dodijeliti admin/super_admin ulogu
        if (in_array($role->name, ['admin', 'super_admin']) && $request->user()->user_type !== 'super_admin') {
            return response()->json([
                'message' => 'Samo super administrator može dodijeliti admin uloge.',
            ], 403);
        }

        $user->roles()->sync([$role->id]);
        $user->update(['user_type' => $role->name]);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ], 204);
    }
}
