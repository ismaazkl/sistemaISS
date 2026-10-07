<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha de matrícula {{ $ficha->estudiante }}</title>
    <style>
        @page {
            margin: 32px 28px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #18181b;
            line-height: 1.5;
            margin: 0;
        }

        .sello {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            object-fit: cover;
        }

        h1 {
            font-size: 16px;
            margin: 0;
        }

        p {
            margin: 0;
        }

        .header {
            border-bottom: 2px solid #18181b;
            padding-bottom: 10px;
            margin-bottom: 18px;
            padding-left: 70px;
            min-height: 46px;
        }

        .header-meta {
            font-size: 10px;
            color: #52525b;
            margin-top: 2px;
        }

        .badge {
            display: inline-block;
            border: 1px solid #a1a1aa;
            border-radius: 4px;
            padding: 1px 8px;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d4d4d8;
            padding: 7px 9px;
            text-align: left;
            vertical-align: top;
        }

        th {
            width: 38%;
            background-color: #f4f4f5;
            font-weight: bold;
        }

        .signature {
            margin-top: 46px;
            width: 100%;
        }

        .signature td {
            border: none;
            padding: 0;
            width: 45%;
            text-align: center;
        }

        .signature .line {
            border-top: 1px solid #18181b;
            margin-bottom: 4px;
        }

        .footer {
            margin-top: 26px;
            padding-top: 8px;
            border-top: 1px solid #d4d4d8;
            font-size: 9px;
            color: #71717a;
        }
    </style>
</head>
<body>
    @if ($sello)
        <img class="sello" src="{{ $sello }}" alt="">
    @endif

    <div class="header">
        <h1>Ficha de matrícula</h1>
        <p class="header-meta">{{ $team->name }}</p>
    </div>

    <table>
        <tbody>
            <tr>
                <th>Estudiante</th>
                <td>{{ $ficha->estudiante }}</td>
            </tr>
            <tr>
                <th>Cédula del estudiante</th>
                <td>{{ $ficha->cedula_estudiante }}</td>
            </tr>
            <tr>
                <th>Curso</th>
                <td>
                    <span class="badge">{{ $ficha->curso->curso }}</span>
                </td>
            </tr>
            <tr>
                <th>Representante</th>
                <td>{{ $ficha->representante }}</td>
            </tr>
            <tr>
                <th>Cédula del representante</th>
                <td>{{ $ficha->cedula_representante }}</td>
            </tr>
            <tr>
                <th>Teléfono</th>
                <td>{{ $ficha->telefono ?? '—' }}</td>
            </tr>
            <tr>
                <th>Fecha de matrícula</th>
                <td>{{ $ficha->fecha->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Hora</th>
                <td>{{ $hora ?? '—' }}</td>
            </tr>
            <tr>
                <th>Causa / motivo de salida</th>
                <td>{{ $ficha->causa_salida ?? '—' }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature">
        <tbody>
            <tr>
                <td>
                    <div class="line"></div>
                    <p>{{ $ficha->representante }}</p>
                    <p style="font-size: 9px; color: #71717a;">Firma del representante</p>
                </td>
                <td></td>
                <td>
                    <div class="line"></div>
                    <p>{{ $ficha->estudiante }}</p>
                    <p style="font-size: 9px; color: #71717a;">Firma del estudiante</p>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Documento generado automáticamente el {{ $generatedAt->format('d/m/Y \a \l\a\s H:i') }}.
    </div>
</body>
</html>