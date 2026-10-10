<?php

namespace App\Http\Controllers;

use App\Domain\Payroll\Services\PayslipPdfService;
use App\Models\PayrollRunVersion;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayrollPdfController extends Controller
{
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
        return $this->pdfService->streamPayslip($payslip);
    }

    /**
     * Download a single payslip PDF.
     */
    public function downloadPayslip(Payslip $payslip): Response
    {
        return $this->pdfService->downloadPayslip($payslip);
    }

    /**
     * Download the entire batch of payslips for a payroll run version in a combined PDF.
     */
    public function downloadBatch(PayrollRunVersion $version): Response
    {
        return $this->pdfService->downloadRunBatch($version);
    }
}
