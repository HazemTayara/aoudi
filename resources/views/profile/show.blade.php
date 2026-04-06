@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header">My Profile</div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="{{ route('profile.password.edit') }}" class="btn btn-outline-secondary">Change Password</a>
            </form>
        </div>
    </div>
@endsection