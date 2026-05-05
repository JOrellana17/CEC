<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports)
    {
    }

    public function index(Request $request)
    {
        $filters = $this->reports->filters($request->all());
        $context = $this->reports->context($filters);

        return view('backend.reports.index', array_merge($context, [
            'operational' => $this->reports->operational($filters),
            'financial' => $this->reports->financial($filters),
            'statistical' => $this->reports->statistical($filters),
        ]));
    }

    public function show(Request $request, string $type)
    {
        $filters = $this->reports->filters($request->all());
        $report = $this->reports->report($type, $filters);

        return view("backend.reports.$type", array_merge($this->reports->context($filters), [
            'type' => $type,
            'report' => $report,
        ]));
    }

    public function exportPdf(Request $request, string $type)
    {
        $filters = $this->reports->filters($request->all());
        $report = $this->reports->report($type, $filters);

        $pdf = Pdf::loadView('backend.reports.pdf.report', [
            'type' => $type,
            'filters' => $filters,
            'report' => $report,
            'rows' => $this->reports->rows($type, $report),
            'generatedAt' => now(),
        ])->setPaper('letter');

        return $pdf->download($this->filename($type, $filters, 'pdf'));
    }

    public function exportExcel(Request $request, string $type): StreamedResponse
    {
        $filters = $this->reports->filters($request->all());
        $report = $this->reports->report($type, $filters);
        $rows = $this->reports->rows($type, $report);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(ucfirst($type));
        $sheet->setCellValue('A1', $report['title']);
        $sheet->setCellValue('A2', 'Date range');
        $sheet->setCellValue('B2', $filters['date_from'].' to '.$filters['date_to']);

        $headers = array_keys($rows->first() ?? ['No data' => '']);
        $sheet->fromArray($headers, null, 'A4');

        $rowNumber = 5;
        foreach ($rows as $row) {
            $sheet->fromArray(array_values($row), null, 'A'.$rowNumber);
            $rowNumber++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $this->filename($type, $filters, 'xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function filename(string $type, array $filters, string $extension): string
    {
        return "{$type}-report-{$filters['date_from']}-to-{$filters['date_to']}.{$extension}";
    }
}
