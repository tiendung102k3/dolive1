<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        // For now, just a simple view. Later can add stats.
        return view("admin.dashboard");
    }

    /**
     * Display a listing of the users.
     */
    public function indexUsers()
    {
        $users = User::paginate(15); // Paginate for better performance
        return view("admin.users.index", compact("users"));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function editUser(User $user)
    {
        return view("admin.users.edit", compact("user"));
    }

    /**
     * Update the specified user in storage.
     */
    public function updateUser(Request $request, User $user)
    {
        $validatedData = $request->validate([
            "name" => ["required", "string", "max:255"],
            "email" => ["required", "string", "email", "max:255", Rule::unique("users")->ignore($user->id)],
            "is_admin" => ["sometimes", "boolean"],
            "password" => ["nullable", "string", "min:8", "confirmed"],
            "max_concurrent_streams" => ["required", "integer", "min:0"],
            "max_monthly_streaming_minutes" => ["required", "integer", "min:0"],
            // current_monthly_streaming_minutes is usually not set by admin directly, but can be reset
            "max_destinations_per_stream" => ["required", "integer", "min:1"],
        ]);

        $user->name = $validatedData["name"];
        $user->email = $validatedData["email"];

        if (isset($validatedData["is_admin"])) {
            $user->is_admin = (bool)$validatedData["is_admin"];
        }

        if (!empty($validatedData["password"])) {
            $user->password = Hash::make($validatedData["password"]);
        }
        
        $user->max_concurrent_streams = $validatedData["max_concurrent_streams"];
        $user->max_monthly_streaming_minutes = $validatedData["max_monthly_streaming_minutes"];
        $user->max_destinations_per_stream = $validatedData["max_destinations_per_stream"];
        
        // Optionally, add a way to reset current_monthly_streaming_minutes or streaming_minutes_reset_at
        if ($request->has("reset_streaming_minutes")) {
            $user->current_monthly_streaming_minutes = 0;
            $user->streaming_minutes_reset_at = now();
        }

        $user->save();

        return redirect()->route("admin.users.index")->with("success", "User updated successfully.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroyUser(User $user)
    {
        // Prevent admin from deleting themselves (optional, but good practice)
        if (auth()->user()->id === $user->id) {
            return redirect()->route("admin.users.index")->with("error", "You cannot delete your own account.");
        }

        $user->delete();
        return redirect()->route("admin.users.index")->with("success", "User deleted successfully.");
    }

    // Placeholder for other resource methods if needed, though not used by current routes
    public function index() { return $this->indexUsers(); } // Alias for consistency if someone calls index()
    public function create() { /* TODO: Implement if admin can create users directly */ abort(404); }
    public function store(Request $request) { /* TODO: Implement if admin can create users directly */ abort(404); }
    public function show(User $user) { /* TODO: Implement if needed */ return $this->editUser($user); }
    public function edit(User $user) { return $this->editUser($user); } // Alias for consistency
    public function update(Request $request, User $user) { return $this->updateUser($request, $user); } // Alias
    public function destroy(User $user) { return $this->destroyUser($user); } // Alias
}

