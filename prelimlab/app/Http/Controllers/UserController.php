<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource (with search, filter, and pagination).
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function ($query, $role) {
                if (in_array($role, ['organizer', 'participant'])) {
                    $query->where('role', $role);
                }
            })
            ->paginate($request->input('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:organizer,participant',
        ]);

        // 2. Hash the password before saving it to the database
        $validatedData['password'] = Hash::make($validatedData['password']);

        // 3. Create the user
        $user = User::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201); // 201 Created status code
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // findOrFail will automatically return a 404 error if the user doesn't exist
        $user = User::select('id', 'name', 'email', 'role', 'created_at')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        // 1. Validate the incoming request (allowing partial updates)
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            // Ignore the current user's ID so they can keep their existing email
            'email' => 'sometimes|required|string|email|max:100|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|required|in:organizer,participant',
        ]);

        // 2. If a new password is provided, hash it
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        // 3. Update the user
        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}