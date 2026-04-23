<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCtxhSharedLogic;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SummaryController extends Controller
{
    use HandlesCtxhSharedLogic;

    public function summary(): View
    {
        $summaryData = $this->getSummaryData();

        return view('ctxh.summary', [
            'stats' => $summaryData['stats'],
            'students' => $summaryData['students'],
        ]);
    }

    public function exportSummaryExcel()
    {
        $summaryData = $this->getSummaryData();
        $students = $summaryData['students'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tong ket CTXH');

        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'BÁO CÁO TỔNG KẾT ĐIỂM CTXH');
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Ngày xuất: ' . now()->format('d/m/Y H:i:s'));

        $headers = [
            'A4' => 'Họ và tên',
            'B4' => 'MSSV',
            'C4' => 'Khoa / Lớp',
            'D4' => 'Điểm tích lũy',
            'E4' => 'Điểm yêu cầu',
            'F4' => 'Trạng thái',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $row = 5;
        foreach ($students as $student) {
            $sheet->setCellValue('A' . $row, $student['name']);
            $sheet->setCellValue('B' . $row, $student['student_id']);
            $sheet->setCellValue('C' . $row, $student['class_name']);
            $sheet->setCellValue('D' . $row, (int) $student['score']);
            $sheet->setCellValue('E' . $row, (int) $student['required_score']);
            $sheet->setCellValue('F' . $row, $student['status']);
            $row++;
        }

        $lastDataRow = max($row - 1, 5);

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(14);

        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1D4ED8'],
            ],
        ]);

        $sheet->getStyle('A2:F2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 11,
                'color' => ['rgb' => '475569'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A4:F4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        $sheet->getStyle("A5:F{$lastDataRow}")->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ]);

        $sheet->getStyle("B5:F{$lastDataRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        for ($i = 5; $i <= $lastDataRow; $i++) {
            $status = (string) $sheet->getCell('F' . $i)->getValue();

            if ($status === 'Đạt') {
                $sheet->getStyle('F' . $i)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '15803D'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'ECFDF3'],
                    ],
                ]);
            } else {
                $sheet->getStyle('F' . $i)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'DC2626'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF2F2'],
                    ],
                ]);
            }
        }

        $sheet->freezePane('A5');
        $sheet->setAutoFilter("A4:F{$lastDataRow}");
        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getRowDimension(4)->setRowHeight(22);

        $fileName = 'tong_ket_ctxh_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}