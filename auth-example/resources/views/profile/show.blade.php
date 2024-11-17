@extends('layouts.app')

@section('content')
<div class="container">
    <h1>User Profile</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="mobile">Mobile</label>
            <input type="text" name="mobile" value="{{ old('mobile', optional($user->profile)->mobile) }}" class="form-control">
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" value="{{ old('address', optional($user->profile)->address) }}" class="form-control">
        </div>

        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea name="bio" class="form-control" rows="3">{{ old('bio', optional($user->profile)->bio) }}</textarea>
        </div>

        <!-- Image Upload Field -->
        <div class="form-group">
            <label for="image">Profile Image</label>
            <input type="file" name="image" class="form-control-file">
            @if(optional($user->profile)->image)
                <img src="{{ asset('storage/' . $user->profile->image) }}" alt="Profile Image" class="img-thumbnail mt-2" style="max-width: 150px;">
            @endif
        </div>

        <!-- Add additional fields as necessary -->

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
@endsection