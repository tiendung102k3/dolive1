<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config("app.name", "Laravel") }} - Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(["resources/css/app.css", "resources/js/app.js"])

    <style>
        body { font-family: "Figtree", sans-serif; margin: 0; background-color: #f9fafb; color: #1f2937; }
        .admin-container { max-width: 1200px; margin: 20px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .admin-nav { background-color: #374151; padding: 1rem; border-radius: 8px 8px 0 0; }
        .admin-nav ul { list-style-type: none; margin: 0; padding: 0; display: flex; }
        .admin-nav ul li a { color: white; text-decoration: none; padding: 0.5rem 1rem; display: block; }
        .admin-nav ul li a:hover { background-color: #4b5563; }
        .admin-content { padding: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        .table th { background-color: #f3f4f6; }
        .btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 0.9em; }
        .btn-primary { background-color: #2563eb; color: white; }
        .btn-primary:hover { background-color: #1d4ed8; }
        .btn-danger { background-color: #dc2626; color: white; }
        .btn-danger:hover { background-color: #b91c1c; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: .25rem; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input[type=\"text\"], .form-group input[type=\"email\"], .form-group input[type=\"password\"], .form-group select {
            width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem; box-sizing: border-box;
        }
        .form-group input[type=\"checkbox\"] { margin-right: 0.5rem; }
    </style>
</head>
<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <ul>
                <li><a href="{{ route("admin.dashboard") }}">Dashboard</a></li>
                <li><a href="{{ route("admin.users.index") }}">Manage Users</a></li>
                <li><a href="{{ route("dashboard") }}">Main Site</a></li> 
                <li>
                    <form method="POST" action="{{ route("logout") }}">
                        @csrf
                        <a href="{{ route("logout") }}" 
                           onclick="event.preventDefault(); this.closest("form").submit();">
                           Logout
                        </a>
                    </form>
                </li>
            </ul>
        </nav>

        <main class="admin-content">
            @if(session("success"))
                <div class="alert alert-success">
                    {{ session("success") }}
                </div>
            @endif
            @if(session("error"))
                <div class="alert alert-error">
                    {{ session("error") }}
                </div>
            @endif

            @yield("content")
        </main>
    </div>
</body>
</html>

