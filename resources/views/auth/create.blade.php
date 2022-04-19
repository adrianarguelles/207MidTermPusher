@extends('layouts.master')

@section('content')

<h2>Register</h2>
<form method="POST" action="/register">
    {{ csrf_field() }}
    <div class="form-group">
    <label for="name">Username:</label>
    <input type="text" class="form-control" id="name" name="name">
    </div>
    
    <div class="form-group">
    <label for="first_name">Firstname:</label>
    <input type="text" class="form-control" id="first_name" name="first_name">
    </div>

    <div class="form-group">
    <label for="last_name">Lastname:</label>
    <input type="text" class="form-control" id="last_name" name="last_name">
    </div>

    <div class="form-group">
    <label for="email">Email Address:</label>
    <input type="email" class="form-control" id="email" name="email">
    </div>

    <div class="form-group">
    <label for="password">Password:</label>
    <input type="password" class="form-control" id="password" name="password">
    </div>

    <div class="form-group">
    <button style="cursor:pointer" type="submit" class="btn btn-primary">Submit</button>
    </div>

    @include('partial.formerrors')
    
</form>

@endsection
