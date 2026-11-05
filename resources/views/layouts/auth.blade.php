<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Capacitatron') }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Orbitron:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet" />
    <link rel="icon" href="{{ asset('/assets/img/HR-Media_LOGO_Iso.png') }}" type="image/png">
    <x-toast />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>

<body>
    <main class="grid grid-cols-2 h-screen">
        <div class="bg-cover bg-center flex items-end justify-start p-8"
            style="background-image: url('{{ asset('/assets/img/login_bg.png') }}');">
            <img src="{{ asset('/assets/img/logo_hr_mw.png') }}" class="w-36 mr-4" alt="Logo">
            <div>
                <h1 class="text-[#fdfdfd] text-2xl font-semibold capitalize tracking-wide font-syne">
                    Capacitatron
                </h1>
                <h4 class="text-[#fdfdfd] text-sm font-light font-montserrat tracking-wider">
                    HR Media Research Center
                </h4>
            </div>
        </div>

        <div class="relative bg-[#fdfdfd] flex items-center">
            <div
                class="absolute inset-0 h-full w-full bg-[radial-gradient(#fda4af_1.5px,transparent_1.5px)] [background-size:14px_14px] [mask-image:radial-gradient(ellipse_75%_75%_at_50%_50%,transparent_70%,#fdfdfd_100%)]">
            </div>

            <div class="relative z-10 w-full">
                {{ $slot }}
            </div>
        </div>
    </main>

</body>

</html>
