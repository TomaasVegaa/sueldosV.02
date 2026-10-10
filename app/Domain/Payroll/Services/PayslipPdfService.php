<?php

namespace App\Domain\Payroll\Services;

use App\Models\PayrollRunVersion;
use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PayslipPdfService
{
    /**
     * Render a single payslip into a DomPDF instance.
     */
    public function renderPayslip(Payslip $payslip)
    {
        $payslip->loadMissing(['employment.employer', 'employment.worker', 'runVersion.run.period', 'lines', 'bases']);

        return Pdf::loadView('payroll.payslip-pdf', [
            'payslip' => $payslip,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Download a single payslip PDF directly.
     */
    public function downloadPayslip(Payslip $payslip): Response
    {
        $pdf = $this->renderPayslip($payslip);
        $period = $payslip->runVersion->run->period->year_month;
        $filename = sprintf('Recibo_%s_%s_%s.pdf', $payslip->file_number, $period, str_replace(' ', '_', $payslip->worker_name));

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Stream a single payslip PDF in browser (inline view).
     */
    public function streamPayslip(Payslip $payslip): Response
    {
        $pdf = $this->renderPayslip($payslip);
        $period = $payslip->runVersion->run->period->year_month;
        $filename = sprintf('Recibo_%s_%s.pdf', $payslip->file_number, $period);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Render all payslips of a payroll run version into a combined batch PDF.
     */
    public function renderRunBatch(PayrollRunVersion $runVersion)
    {
        $runVersion->loadMissing(['run.employer', 'run.period', 'payslips.employment.worker', 'payslips.lines', 'payslips.bases']);

        return Pdf::loadView('payroll.batch-payslips-pdf', [
            'runVersion' => $runVersion,
            'payslips' => $runVersion->payslips,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Download all payslips of a run version combined in a single PDF.
     */
    public function downloadRunBatch(PayrollRunVersion $runVersion): Response
    {
        $pdf = $this->renderRunBatch($runVersion);
        $run = $runVersion->run;
        $period = $run->period->year_month;
        $filename = sprintf('Lote_Recibos_%s_%s_v%d.pdf', $run->employer->cuit, $period, $runVersion->version_number);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
