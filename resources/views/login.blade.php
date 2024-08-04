<!DOCTYPE html>
<html lang="en">
    <head>
    </head>
    <body>
        <h1>Login</h1>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}">

            <label for="password">Password</label>
            <input type="password" name="password" id="password">

            <button type="submit">Login</button>
    </body>
</html>
