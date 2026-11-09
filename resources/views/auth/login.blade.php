<!doctype html><html><head><title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container" style="max-width:420px">
    <div class="card shadow"><div class="card-body">
        <h5 class="mb-3">KSG PC & Workplan Evidence System</h5>
        @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
        <form method="post" action="{{ route('login') }}">@csrf
            <div class="mb-3"><label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required autofocus></div>
            <div class="mb-3"><label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required></div>
            <button class="btn btn-dark w-100">Login</button>
        </form>
    </div></div>
</div></body></html>
