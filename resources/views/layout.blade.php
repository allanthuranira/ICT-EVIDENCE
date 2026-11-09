<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KSG PC & Workplan Evidence System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">KSG Evidence System</a>
        <div class="navbar-nav">
            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="nav-link" href="{{ route('items.index') }}">PC Targets / KRAs</a>
            <a class="nav-link" href="{{ route('campuses.index') }}">Campuses</a>
            <a class="nav-link" href="{{ route('recipients.index') }}">Recipients</a>
            <a class="nav-link" href="{{ route('evidence.index') }}">Evidence</a>
            <a class="nav-link" href="{{ route('reports.index') }}">Quarterly Reports</a>
            <form method="post" action="{{ route('logout') }}" class="d-inline">@csrf
                <button class="btn btn-link nav-link d-inline">Logout</button>
            </form>
        </div>
    </div>
</nav>
<main class="container py-4">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @yield('content')
</main>
</body>
</html>
