<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Certificado de Cursos - {{ $user->name }} {{ $user->surname ?? '' }}</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            margin: 40px;
            color: #333;
        }

        .header-container {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #EE7C00;
            padding-bottom: 15px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            padding: 0;
            border: none;
            vertical-align: middle;
        }

        .logo {
            max-width: 150px;
            height: auto;
        }

        .export-date {
            text-align: right;
            font-size: 13px;
            color: #6b7280;
        }

        .hero {
            text-align: center;
            font-size: 12px;
            margin-top: 15px;
            margin-bottom: 15px;
            color: #4b5563;
        }

        .hero h2 {
            margin: 0;
            color: #111827;
            font-size: 18px;
        }

        .user-info {
            margin-bottom: 20px;
            font-size: 12px;
            color: #374151;
            background-color: #f9fafb;
            padding: 12px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .user-info p {
            margin: 3px 0;
        }

        .user-info strong {
            color: #111827;
        }

        table.content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: 1px solid #e5e7eb;
        }

        table.content-table th {
            background-color: #EE7C00;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            border: 1px solid #e5e7eb;
        }

        table.content-table td {
            padding: 8px 12px;
            font-size: 12px;
            border: 1px solid #e5e7eb;
        }

        table.content-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .additional-tag {
            display: inline-block;
            margin-left: 6px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #EE7C00;
        }

        .total {
            margin-top: 15px;
            text-align: right;
            font-size: 13px;
            font-weight: bold;
            color: #0a0a0a;
        }

        .total span {
            font-size: 15px;
            color: #0b0b0c;
        }

        .verification-container {
            margin-top: 30px;
            display: table;
            width: 100%;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        .verification-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #45484e;
            line-height: 1.5;
        }

        .signature-area {
            position: relative;
            margin-top: 20px;
        }

        .seal {
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            width: 110px;
            height: auto;
            opacity: 0.85;
        }
    </style>
</head>

<body>

    <!-- Encabezado con Tabla para asegurar compatibilidad de alineación en Dompdf -->
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td>
                    <img src="{{ public_path('assets/img/logo.jpg') }}" alt="Logo" class="logo">
                </td>
                <td class="export-date">
                    <strong>Certificado de Cursos Completados</strong><br>
                    Fecha de emisión: {{ $signedAt->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="hero">
        <h2>HR Media · Capacitatron</h2>
    </div>

    <!-- Tabla de cursos -->
    @if (count($courses) > 0)
        <table class="content-table">
            <thead>
                <tr>
                    <th style="width: 70%;">Curso</th>
                    <th style="width: 30%; text-align: center;">Fecha de finalización</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($courses as $course)
                    <tr>
                        <td>
                            {{ $course['name'] }}
                            @if ($course['is_additional'] ?? false)
                                <span class="additional-tag">(Curso adicional)</span>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $course['completed_at'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(isset($totalHours))
            <div class="total">
                Total de horas: <span>{{ number_format($totalHours, 1) }}</span>
            </div>
        @endif
    @else
        <p style="text-align: center; margin-top: 30px; color: #6b7280; font-size: 12px;">
            El colaborador no tiene cursos registrados.
        </p>
    @endif

    <!-- Verificación y Código QR -->
    <div class="verification-container">
        <div class="verification-cell" style="width: 70%;">
            <p style="font-size: 11px; color: #374151; margin: 0;">
                <strong>Folio de verificación:</strong> {{ $folio }}
            </p>
            <p style="font-size: 10px; color: #6b7280; margin: 4px 0 0 0;">
                Verifica la autenticidad de este documento en:<br>
                <a href="{{ $verificationUrl }}" style="color: #EE7C00; text-decoration: none;">{{ $verificationUrl }}</a>
            </p>
        </div>
        <div class="verification-cell" style="width: 30%; text-align: right;">
            <img src="data:image/png;base64,{{ $qrCodeBase64 }}" style="width: 85px; height: 85px;">
        </div>
    </div>

    <!-- Pie de página: conteo de horas y certificación -->
    <div class="footer">
        <p>Yo {{ $user->name }} {{ $user->surname ?? '' }} {{ $user->second_surname ?? '' }}, certifico que durante {{ now()->year }} asistí a un total de
            {{ number_format($totalHours, 1) }} horas de capacitación, conforme al Plan de Capacitación Obligatorio de HR Media {{ now()->year }}.</p>

        <br><br>
        <div class="signature-area">
            <img src="{{ public_path('assets/img/sello.png') }}" alt="Sello" class="seal">
            <p>____________________________________________________________________________________</p>
            <p>Atentamente, {{ $user->name }} {{ $user->surname ?? '' }} {{ $user->second_surname ?? '' }}{{ isset($user->major_description) ? ', ' . $user->major_description : '' }}</p>
            <p>HR Media - Capacitatron</p>
        </div>
    </div>

</body>

</html>