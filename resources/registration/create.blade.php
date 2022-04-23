@section('content')

<h2>Register</h2>
<form method="POST" action="/register">
    {{ csrf_field() }}
    <div class="form-group">
    <label for="name">Username:</label>
    <input type="text" class="form-control" id="name" name="name">
    </div>
    
    <div class="form-group">
    <label for="firstname">Firstname:</label>
    <input type="text" class="form-control" id="firstname" name="firstname">
    </div>

    <div class="form-group">
    <label for="lastname">Lastname:</label>
    <input type="text" class="form-control" id="lastname" name="lastname">
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
