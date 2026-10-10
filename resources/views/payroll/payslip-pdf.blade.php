<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Haberes - Legajo {{ $payslip->file_number }}</title>
    <style>
        @page {
            margin: 15mm 12mm 15mm 12mm;
            size: A4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .receipt-card {
            border: 1px solid #334155;
            margin-bottom: 25px;
            padding: 10px;
            page-break-inside: avoid;
        }
        .header-table, .info-table, .concept-table, .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .company-meta {
            font-size: 7.5pt;
            color: #475569;
        }
        .receipt-badge {
            text-align: right;
            vertical-align: top;
        }
        .badge-box {
            display: inline-block;
            border: 1.5px solid #0284c7;
            padding: 4px 8px;
            text-align: center;
            background-color: #f0f9ff;
        }
        .badge-title {
            font-size: 9pt;
            font-weight: bold;
            color: #0369a1;
        }
        .badge-sub {
            font-size: 7pt;
            color: #0284c7;
        }
        .section-divider {
            border-top: 1px solid #94a3b8;
            margin: 6px 0;
        }
        .info-table td {
            padding: 2px 4px;
            font-size: 7.5pt;
        }
        .info-label {
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            font-size: 7pt;
        }
        .concept-table {
            margin-top: 6px;
            border-top: 1px solid #334155;
            border-bottom: 1px solid #334155;
        }
        .concept-table th {
            background-color: #f1f5f9;
            border-bottom: 1px solid #475569;
            padding: 4px;
            font-size: 7pt;
            text-transform: uppercase;
            color: #0f172a;
            font-weight: bold;
        }
        .concept-table td {
            padding: 3.5px 4px;
            font-size: 7.5pt;
            border-bottom: 1px dotted #cbd5e1;
        }
        .concept-table tr:last-child td {
            border-bottom: none;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-mono { font-family: "Courier New", Courier, monospace; }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            border-top: 1px solid #334155;
        }
        .totals-table th {
            font-size: 7pt;
            text-transform: uppercase;
            padding: 4px;
            background-color: #f8fafc;
            border-bottom: 1px solid #94a3b8;
        }
        .totals-table td {
            padding: 4px;
            font-size: 8.5pt;
            font-weight: bold;
        }
        .net-box {
            background-color: #f0fdf4;
            border: 1.5px solid #16a34a;
            padding: 5px 10px;
            margin-top: 6px;
            display: flex;
            justify-content: space-between;
        }
        .net-title {
            font-size: 9pt;
            font-weight: bold;
            color: #15803d;
        }
        .net-amount {
            font-size: 11pt;
            font-weight: bold;
            color: #166534;
            text-align: right;
        }
        .words-box {
            font-size: 7pt;
            font-style: italic;
            color: #334155;
            padding: 3px 0;
            border-bottom: 1px solid #cbd5e1;
        }
        .signature-table {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            font-size: 7.5pt;
            padding: 0 20px;
        }
        .signature-line {
            border-top: 1px solid #475569;
            padding-top: 4px;
        }
        .copy-tag {
            font-size: 6.5pt;
            text-align: right;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .legal-notice {
            font-size: 6.5pt;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.1;
        }
    </style>
</head>
<body>

@php
    $employer = $payslip->employment->employer;
    $run = $payslip->runVersion->run;
    $period = $run->period;
    $copies = [
        'ORIGINAL — EJEMPLAR PARA EL TRABAJADOR',
        'DUPLICADO — EJEMPLAR PARA EL EMPLEADOR',
    ];
@endphp

@foreach($copies as $copyIndex => $copyName)
    <div class="receipt-card" style="{{ $copyIndex > 0 ? 'page-break-before: always;' : '' }}">
        <!-- Encabezado Empleador y Recibo -->
        <table class="header-table">
            <tr>
                <td style="width: 65%;">
                    <div class="company-title">{{ $employer->business_name }}</div>
                    <div class="company-meta">
                        <strong>CUIT:</strong> {{ substr($employer->cuit, 0, 2) . '-' . substr($employer->cuit, 2, 8) . '-' . substr($employer->cuit, 10, 1) }} &nbsp;|&nbsp;
                        <strong>Domicilio:</strong> {{ $employer->legal_address ?? 'Sede Central' }}<br>
                        <strong>Actividad:</strong> {{ $employer->activity_code ?? 'Comercio y Servicios' }} &nbsp;|&nbsp;
                        <strong>CCT Aplicable:</strong> CCT 130/75 Empleados de Comercio
                    </div>
                </td>
                <td class="receipt-badge" style="width: 35%;">
                    <div class="badge-box">
                        <div class="badge-title">RECIBO DE HABERES</div>
                        <div class="badge-sub">LEY 20.744 ART. 140</div>
                        <div style="font-size: 8pt; font-weight: bold; margin-top: 2px;">
                            PERÍODO: {{ strtoupper($period->formatted_period) }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-divider"></div>

        <!-- Datos del Trabajador y Liquidación -->
        <table class="info-table">
            <tr>
                <td style="width: 35%;">
                    <span class="info-label">Apellido y Nombre:</span><br>
                    <strong>{{ $payslip->worker_name }}</strong>
                </td>
                <td style="width: 20%;">
                    <span class="info-label">C.U.I.L.:</span><br>
                    <span class="font-mono"><strong>{{ $payslip->worker_cuil }}</strong></span>
                </td>
                <td style="width: 15%;">
                    <span class="info-label">Legajo N.º:</span><br>
                    <strong>{{ $payslip->file_number }}</strong>
                </td>
                <td style="width: 15%;">
                    <span class="info-label">Fecha Ingreso:</span><br>
                    {{ $payslip->hire_date ? $payslip->hire_date->format('d/m/Y') : '-' }}
                </td>
                <td style="width: 15%;">
                    <span class="info-label">Antigüedad:</span><br>
                    <strong>{{ $payslip->seniority_years }} años</strong>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="info-label">Categoría Laboral:</span><br>
                    <strong>{{ $payslip->category_code }} - {{ $payslip->category_name }}</strong>
                </td>
                <td>
                    <span class="info-label">Obra Social:</span><br>
                    Cód. {{ $payslip->health_insurer_code ?? '126205' }} (OSECAC)
                </td>
                <td>
                    <span class="info-label">Fecha de Pago:</span><br>
                    {{ $run->pay_date ? $run->pay_date->format('d/m/Y') : now()->format('d/m/Y') }}
                </td>
                <td>
                    <span class="info-label">Tipo Liquidación:</span><br>
                    <strong>{{ $run->run_type_label }}</strong>
                </td>
                <td>
                    <span class="info-label">Banco / CBU:</span><br>
                    <span class="font-mono" style="font-size: 6.5pt;">{{ $payslip->cbu ? 'CBU: ' . substr($payslip->cbu, 0, 10) . '...' : 'Acreditación Bancaria' }}</span>
                </td>
            </tr>
        </table>

        <!-- Detalle de Conceptos Liquidados -->
        <table class="concept-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 6%;">Cód.</th>
                    <th class="text-left" style="width: 44%;">Concepto</th>
                    <th class="text-center" style="width: 12%;">Unidad</th>
                    <th class="text-right" style="width: 13%;">Hab. Remun.</th>
                    <th class="text-right" style="width: 12%;">Hab. No Rem.</th>
                    <th class="text-right" style="width: 13%;">Descuentos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payslip->lines as $line)
                    @if($line->print_on_receipt)
                        <tr>
                            <td class="text-center font-mono">{{ $line->concept_code }}</td>
                            <td class="text-left">{{ $line->concept_name }}</td>
                            <td class="text-center font-mono">
                                {{ $line->quantity ? number_format($line->quantity, 2, ',', '.') . ($line->unit ? ' ' . $line->unit : '') : '' }}
                            </td>
                            <td class="text-right font-mono">
                                {{ $line->remunerative_amount > 0 ? '$ ' . number_format($line->remunerative_amount, 2, ',', '.') : '' }}
                            </td>
                            <td class="text-right font-mono">
                                {{ $line->non_taxable_amount > 0 ? '$ ' . number_format($line->non_taxable_amount, 2, ',', '.') : '' }}
                            </td>
                            <td class="text-right font-mono" style="color: #991b1b;">
                                {{ $line->deduction_amount > 0 ? '$ ' . number_format($line->deduction_amount, 2, ',', '.') : '' }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <!-- Totales de Haberes y Retenciones -->
        <table class="totals-table">
            <tr>
                <th class="text-right" style="width: 50%;">SUBTOTALES</th>
                <th class="text-right" style="width: 16%; color: #0f172a;">TOTAL REMUNERATIVO</th>
                <th class="text-right" style="width: 16%; color: #0369a1;">TOTAL NO REMUNERAT.</th>
                <th class="text-right" style="width: 18%; color: #991b1b;">TOTAL RETENCIONES</th>
            </tr>
            <tr>
                <td></td>
                <td class="text-right font-mono">$ {{ number_format($payslip->gross_remunerative, 2, ',', '.') }}</td>
                <td class="text-right font-mono">$ {{ number_format($payslip->gross_non_remunerative, 2, ',', '.') }}</td>
                <td class="text-right font-mono" style="color: #991b1b;">$ {{ number_format($payslip->total_deductions, 2, ',', '.') }}</td>
            </tr>
        </table>

        <!-- Sueldo Neto y Texto en Letras -->
        <table style="width: 100%; margin-top: 6px; border: 1.5px solid #16a34a; background-color: #f0fdf4; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 10px; vertical-align: middle;">
                    <span style="font-size: 8.5pt; font-weight: bold; color: #166534; text-transform: uppercase;">
                        SUELDO NETO A COBRAR
                    </span>
                </td>
                <td style="padding: 6px 10px; text-align: right; vertical-align: middle;">
                    <span style="font-size: 13pt; font-weight: bold; color: #14532d; font-family: 'Courier New', Courier, monospace;">
                        $ {{ number_format($payslip->net_salary, 2, ',', '.') }}
                    </span>
                </td>
            </tr>
        </table>

        <div class="words-box">
            <strong>{{ $payslip->net_salary_words }}</strong>
        </div>

        <!-- Bases Imponibles y Contribuciones ARCA -->
        <table style="width: 100%; font-size: 6.5pt; color: #475569; margin-top: 4px;">
            <tr>
                <td>
                    <strong>Bases Imponibles ARCA (LSD):</strong>
                    Base 1 (SIPA): $ {{ number_format($payslip->gross_remunerative, 2, ',', '.') }} &nbsp;|&nbsp;
                    Base 4 (Salud): $ {{ number_format($payslip->gross_remunerative + $payslip->gross_non_remunerative, 2, ',', '.') }} &nbsp;|&nbsp;
                    Contribuciones Patronales Estimadas: $ {{ number_format($payslip->employer_contributions_total, 2, ',', '.') }}
                </td>
                <td style="text-align: right;">
                    <strong>Hash Verificación:</strong> {{ substr($payslip->runVersion->snapshot_sha256, 0, 16) }}... (v{{ $payslip->runVersion->version_number }})
                </td>
            </tr>
        </table>

        <!-- Firmas -->
        <table class="signature-table">
            <tr>
                <td>
                    <div style="height: 35px;"></div>
                    <div class="signature-line">
                        <strong>FIRMA Y SELLO DEL EMPLEADOR</strong>
                    </div>
                </td>
                <td>
                    <div style="height: 35px;"></div>
                    <div class="signature-line">
                        <strong>FIRMA DEL TRABAJADOR</strong><br>
                        <span style="font-size: 6.5pt; color: #64748b;">Recibí el importe neto conforme a la ley 20.744</span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="legal-notice">
            Constancia de depósito de aportes y contribuciones de la Seguridad Social según Art. 12 Ley 17.250 y Art. 140 Ley 20.744.
        </div>
        <div class="copy-tag">
            {{ $copyName }}
        </div>
    </div>
@endforeach

</body>
</html>
