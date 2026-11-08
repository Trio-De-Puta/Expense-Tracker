<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Expense Tracker')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink">
    <div class="lg:grid lg:min-h-screen lg:grid-cols-[260px_1fr]">
        <x-nav.sidebar />

        <div class="flex min-h-screen flex-col">
            <x-nav.topbar />

            <main class="flex-1 px-4 pb-28 pt-5 sm:px-6 lg:px-10 lg:pb-10 lg:pt-9">
                <div class="mx-auto w-full max-w-5xl">
                    @yield('content')
                </div>
            </main>

            <x-nav.bottom />
        </div>
    </div>

    <x-ui.quick-add />
    <x-ui.confirm-dialog />
    <x-ui.toast />
</body>
</html>
