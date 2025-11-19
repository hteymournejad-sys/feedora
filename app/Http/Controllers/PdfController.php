<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function downloadReport(Request $request)
    {
        $groupId = $request->input('group_id', 1); // فرض می‌کنیم group_id از درخواست میاد

        // اینجا باید داده‌ها رو از دیتابیس یا مدل بگیری
        $data = [
            'company_name' => 'نام شرکت نمونه',
            'dataValues' => [75, 60, 85, 50, 90], // داده‌های نمونه، باید از دیتابیس بیاد
            'maturityData' => ['levelAverages' => [30, 50, 70, 40, 20]], // داده‌های نمونه
            'report_date' => now()->toDateString(),
        ];

        // رندر قالب HTML به PDF با dompdf
        $pdf = Pdf::loadView('pdf.report', $data); // فرض می‌کنیم قالب pdf/report.blade.php داری
        return $pdf->download('report_' . $groupId . '.pdf');
    }
}