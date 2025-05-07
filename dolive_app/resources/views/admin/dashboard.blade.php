@extends("admin.layouts.app")

@section("content")
    <h1>Admin Dashboard</h1>
    <p>Welcome to the Dolive Admin Panel.</p>
    <p>From here you can manage users, settings, and view system statistics (once implemented).</p>
    <p><a href="{{ route("admin.users.index") }}" class="btn btn-primary">Manage Users</a></p>
@endsection

