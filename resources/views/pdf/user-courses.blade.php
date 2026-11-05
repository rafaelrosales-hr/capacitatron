<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Reporte de Cursos - {{ $user->name }} {{ $user->surname }}</title>
    <style>
        /* @font-face {
            font-family: 'Helvetica';
            src: url('{{ public_path('assets/fonts/Helvetica.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        } */

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
            margin-top: 4px;
            margin-bottom: 4px;
            color: #4b5563;
        }

        .hero h2 {
            margin: 0;
        }

        table.content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #e5e7eb;
        }

        table.content-table th {
            background-color: #EE7C00;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            border: 1px solid #e5e7eb;
        }

        table.content-table td {
            padding: 8px;
            font-size: 12px;
            border: 1px solid #e5e7eb;
        }

        table.content-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .total {
            margin-top: 30px;
            text-align: right;
            font-size: 13px;
            font-weight: bold;
            color: #0a0a0a;
        }

        .total span {
            font-size: 15px;
            color: #0b0b0c;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #45484e;
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
                    <strong>Formato de cetificación de capacitaciones</strong><br>
                    {{ now()->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>
    <div class="hero">
        <h2>Plan de Capacitación Obligatoria HR Media {{ now()->year }}</h2>
    </div>
    {{-- <div class="user-name">
        <strong>Colaborador:</strong> {{ $user->name }} {{ $user->surname }}
    </div> --}}

    @if ($courses->count() > 0)
        <table class="content-table">
            <thead>
                <tr>
                    <th style="width: 90%;text-align: center;">Nombre del Curso</th>
                    <th style="width: 10%;text-align: center;">Duración (horas)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($courses as $course)
                    <tr>
                        <td>{{ $course['name'] }}</td>
                        <td style="text-align: center;">{{ $course['duration'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            Total de horas: <span>{{ $totalDuration }}</span>
        </div>
    @else
        <p style="text-align: center; margin-top: 30px; color: #6b7280;">
            El colavorador no tiene cursos registrados.
        </p>
    @endif

    <div class="footer">
        <p>Yo {{ $user->name }} {{ $user->surname }} {{ $user->second_surname }}, certifico que durante {{ now()->year }} asistí a un total de
            {{ $totalDuration }} horas de capacitación, conforme al Plan de Capacitación Obligatorio de HR Media. {{ now()->year }}</p>
        
        <br><br><br><br><br><br>
        <p>____________________________________________________________________________________</p>
        <p>Atentamente,{{ $user->name }} {{ $user->surname }} {{ $user->second_surname }}, {{ $user->major_description }}</p>
        <p>HR Media - Capacitatron</p>
    </div>
</body>

</html>