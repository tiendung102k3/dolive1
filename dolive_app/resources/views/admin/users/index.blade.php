@extends("admin.layouts.app")

@section("content")
    <h1>Manage Users</h1>

    @if($users->isEmpty())
        <p>No users found.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Admin</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->is_admin ? "Yes" : "No" }}</td>
                        <td>{{ $user->created_at->format("Y-m-d H:i") }}</td>
                        <td>
                            <a href="{{ route("admin.users.edit", $user) }}" class="btn btn-primary">Edit</a>
                            <form action="{{ route("admin.users.destroy", $user) }}" method="POST" style="display:inline-block;" onsubmit="return confirm("Are you sure you want to delete this user?");">
                                @csrf
                                @method("DELETE")
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top: 20px;">
            {{ $users->links() }} 
        </div>
    @endif
@endsection

