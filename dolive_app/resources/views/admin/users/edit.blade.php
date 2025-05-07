@extends("admin.layouts.app")

@section("content")
    <h1>Edit User: {{ $user->name }}</h1>

    <form action="{{ route("admin.users.update", $user) }}" method="POST">
        @csrf
        @method("PUT")

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="{{ old("name", $user->name) }}" required>
            @error("name")
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="{{ old("email", $user->email) }}" required>
            @error("email")
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" name="password" id="password">
            @error("password")
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm New Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation">
        </div>

        <div class="form-group">
            <label for="is_admin">
                <input type="checkbox" name="is_admin" id="is_admin" value="1" {{ old("is_admin", $user->is_admin) ? "checked" : "" }}>
                Is Admin?
            </label>
            @error("is_admin")
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>
        
        <hr style="margin: 20px 0;">
        <h3 style="margin-bottom: 10px;">Usage Limits</h3>

        <div class="form-group">
            <label for="max_concurrent_streams">Max Concurrent Streams</label>
            <input type="number" name="max_concurrent_streams" id="max_concurrent_streams" value="{{ old("max_concurrent_streams", $user->max_concurrent_streams) }}" required min="0">
            @error("max_concurrent_streams")
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="max_monthly_streaming_minutes">Max Monthly Streaming Minutes</label>
            <input type="number" name="max_monthly_streaming_minutes" id="max_monthly_streaming_minutes" value="{{ old("max_monthly_streaming_minutes", $user->max_monthly_streaming_minutes) }}" required min="0">
            @error("max_monthly_streaming_minutes")
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="current_monthly_streaming_minutes">Current Monthly Streaming Minutes (Read-only)</label>
            <input type="number" name="current_monthly_streaming_minutes_display" id="current_monthly_streaming_minutes_display" value="{{ $user->current_monthly_streaming_minutes }}" readonly disabled>
        </div>
        
        <div class="form-group">
            <label for="reset_streaming_minutes">
                <input type="checkbox" name="reset_streaming_minutes" id="reset_streaming_minutes" value="1">
                Reset Current Monthly Streaming Minutes to 0 (Next save will apply)
            </label>
        </div>

        <div class="form-group">
            <label for="max_destinations_per_stream">Max Destinations Per Stream</label>
            <input type="number" name="max_destinations_per_stream" id="max_destinations_per_stream" value="{{ old("max_destinations_per_stream", $user->max_destinations_per_stream) }}" required min="1">
            @error("max_destinations_per_stream")
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="{{ route("admin.users.index") }}" class="btn">Cancel</a>
    </form>
@endsection

