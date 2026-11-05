<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Certificado</title>
    <link rel="icon" href="{{ asset('/assets/img/HR-Media_LOGO_Iso.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-background p-6">
    <div class="max-w-md w-full rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm p-8 text-center">

        @if ($signature && $isValid)
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-emerald-500/10 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-text font-syne mb-2">Certificado válido</h1>
            <p class="text-sm text-text-muted mb-6">Este documento fue emitido por Capacitatron y no ha sido alterado.</p>

            <div class="text-left space-y-2 bg-surface-hover/30 rounded-xl p-4 text-sm">
                <p><span class="text-text-muted">Folio:</span> <span class="text-text font-medium">{{ $signature->folio }}</span></p>
                <p><span class="text-text-muted">Colaborador:</span> <span class="text-text font-medium">{{ $signature->user->name }} {{ $signature->user->surname }}</span></p>
                <p><span class="text-text-muted">Fecha de firma:</span> <span class="text-text font-medium">{{ $signature->signed_at->format('d/m/Y H:i') }}</span></p>
                <p><span class="text-text-muted">Cursos incluidos:</span> <span class="text-text font-medium">{{ count($signature->course_ids) }}</span></p>
            </div>
        @else
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-rose-500/10 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-text font-syne mb-2">Certificado no válido</h1>
            <p class="text-sm text-text-muted">No se encontró este folio, o el documento pudo haber sido alterado.</p>
        @endif
    </div>
</body>
</html>