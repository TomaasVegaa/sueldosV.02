<?php

namespace App\Http\Controllers;

use App\Domain\Payroll\Services\PayslipPdfService;
use App\Models\PayrollRunVersion;
use App\Models\Payslip;
use App\Support\Authorization\AuthorizesPermissions;
use Illuminate\Http\Response;

class PayrollPdfController extends Controller
{
    use AuthorizesPermissions;

    protected PayslipPdfService $pdfService;

    public function __construct(PayslipPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Stream a single payslip PDF directly in browser tab.
     */
    public function streamPayslip(Payslip $payslip): Response
    {
        $this->authorizePayslipExport($payslip);

        return $this->pdfService->streamPayslip($payslip);
    }

    /**
     * Download a single payslip PDF.
     */
    public function downloadPayslip(Payslip $payslip): Response
    {
        $this->authorizePayslipExport($payslip);

        return $this->pdfService->downloadPayslip($payslip);
    }

    /**
     * Download the entire batch of payslips for a payroll run version in a combined PDF.
     */
    public function downloadBatch(PayrollRunVersion $version): Response
    {
        $version->loadMissing(['run', 'payslips.employment']);
        abort_unless($version->run, 404);
        $this->authorizeEmployerPermission('payroll.export', $version->run->employer_id);
        abort_unless(
            $version->payslips->every(
                fn (Payslip $payslip): bool => $payslip->employment?->employer_id === $version->run->employer_id
            ),
            404
        );

        return $this->pdfService->downloadRunBatch($version);
    }

    private function authorizePayslipExport(Payslip $payslip): void
    {
        $payslip->loadMissing(['runVersion.run', 'employment']);
        $run = $payslip->runVersion?->run;

        abort_unless($run, 404);
        $this->authorizeEmployerPermission('payroll.export', $run->employer_id);
        abort_unless($payslip->employment?->employer_id === $run->employer_id, 404);
    }
}
