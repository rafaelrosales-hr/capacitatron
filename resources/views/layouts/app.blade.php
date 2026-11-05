<!DOCTYPE html>
<html lang="es-Mx">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard · HR Media' }}</title>

    <link rel="icon" href="{{ asset('/assets/img/HR-Media_LOGO_Iso.png') }}" type="image/png">

    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script>
    function applyTheme() {
        const theme = localStorage.getItem('theme') || 'light';
        const accent = localStorage.getItem('accent') || 'orange';

        document.documentElement.classList.toggle('dark', theme === 'dark');
        document.documentElement.dataset.accent = accent;
    }
    applyTheme();
    document.addEventListener('livewire:navigated', applyTheme);
</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>

<body class="flex min-h-screen bg-background text-text">

    <aside class="sticky top-0 h-screen shrink-0 z-30">
        <livewire:sidebar />
    </aside>

    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
        <header class="sticky top-0 z-20 w-full bg-background/80 backdrop-blur-md">
            @include('layouts.partials.navbar')
        </header>

        <main class="relative mx-auto bg-background text-text w-full flex-1">
            <div
                class="absolute inset-0 h-full w-full bg-[radial-gradient(var(--color-accent)_1.5px,transparent_1.5px)] [background-size:14px_14px] [mask-image:radial-gradient(ellipse_60%_60%_at_50%_50%,#000_70%,transparent_100%)] opacity-40 transition-colors duration-300 pointer-events-none"
            ></div>

            <div class="relative z-10 p-4">
                {{ $slot }}
            </div>
        </main>
    </div>

    <x-toast />

</body>

</html>