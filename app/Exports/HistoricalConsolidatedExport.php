<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\Product;
use App\Models\Category;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class HistoricalConsolidatedExport
{
    protected $month;
    protected $departments;
    protected $categories;
    protected $products;
    protected $categoryTotals;
    protected $grandTotal;

    public function __construct($month, $departments, $categories, $products, $categoryTotals, $grandTotal)
    {
        $this->month = $month;
        $this->departments = $departments;
        $this->categories = $categories;
        $this->products = $products;
        $this->categoryTotals = $categoryTotals;
        $this->grandTotal = $grandTotal;
    }

    public function download($filename)
    {
        $spreadsheet = new Spreadsheet();

        // Sheet 1: BẢNG TỔNG (Matrix summary)
        $sheet1 = $spreadsheet->getActiveSheet();
        $this->createBangTongSheet($sheet1);

        // Sheet 2: TỔNG HỢP (Detailed list with prices)
        $sheet2 = $spreadsheet->createSheet();
        $this->createTongHopSheet($sheet2);

        // Sheets 3+: ALL Departments (Mandatory 20+ sheets)
        foreach ($this->departments as $dept) {
            $sheet = $spreadsheet->createSheet();
            // Excel sheet names are limited to 31 chars
            $sheetTitle = mb_substr($dept->name, 0, 31);
            $sheet->setTitle($sheetTitle);
            $this->createDepartmentSheet($sheet, $dept);
        }

        // Set active sheet to first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Download
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    protected function createBangTongSheet($sheet)
    {
        $sheet->setTitle('BẢNG TỔNG');
        $currentRow = 1;

        // Header Info
        $sheet->setCellValue('A' . $currentRow, 'CÔNG TY CỔ PHẦN BỆNH VIỆN ĐA KHOA TÂM TRÍ CAO LÃNH');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'Số 01, đường Lê Thị Riêng, phường 1, Thành phố Cao Lãnh, tỉnh Đồng Tháp');
        $currentRow++;
        $currentRow++;

        // Title
        $sheet->setCellValue('A' . $currentRow, 'BẢNG TỔNG HỢP THÁNG ' . $this->month);
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow += 2;

        $tableStartRow = $currentRow;
        $headers = ['STT', 'TÊN HÀNG', 'ĐVT'];
        foreach ($this->departments as $dept) {
            $headers[] = $dept->name;
        }
        $headers[] = 'Tổng SL';

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $currentRow, $header);
            $col++;
        }
        $lastCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($col) - 1);

        // Style header
        $sheet->getStyle('A' . $currentRow . ':' . $lastCol . $currentRow)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension($currentRow)->setRowHeight(40);
        $currentRow++;

        // Data
        $stt = 0;
        foreach ($this->categories as $category) {
            if (isset($this->products[$category->id]) && $this->products[$category->id]->count() > 0) {
                // Category header
                $sheet->setCellValue('A' . $currentRow, $category->name);
                $sheet->mergeCells('A' . $currentRow . ':' . $lastCol . $currentRow);
                $sheet->getStyle('A' . $currentRow)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                ]);
                $currentRow++;

                foreach ($this->products[$category->id] as $product) {
                    $stt++;
                    $sheet->setCellValue('A' . $currentRow, $stt);
                    $sheet->setCellValue('B' . $currentRow, $product->name);
                    $sheet->setCellValue('C' . $currentRow, $product->unit);

                    $colIdx = 4;
                    foreach ($this->departments as $dept) {
                        $order = $product->monthlyOrders->firstWhere('department_id', $dept->id);
                        $qty = $order ? $order->quantity : 0;
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $qty > 0 ? $qty : 0);
                    }

                    // Total formula
                    $rowTotalCol = Coordinate::stringFromColumnIndex($colIdx);
                    $firstDeptCol = 'D';
                    $lastDeptCol = Coordinate::stringFromColumnIndex($colIdx - 1);
                    $sheet->setCellValue($rowTotalCol . $currentRow, "=SUM({$firstDeptCol}{$currentRow}:{$lastDeptCol}{$currentRow})");
                    $currentRow++;
                }
            }
        }

        // Borders
        $sheet->getStyle('A' . $tableStartRow . ':' . $lastCol . ($currentRow - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setWidth(10);
    }

    protected function createTongHopSheet($sheet)
    {
        $sheet->setTitle('TỔNG HỢP');
        // Simplified version of Tong Hop sheet
        $currentRow = 1;
        $sheet->setCellValue('A' . $currentRow, 'BẢNG KÊ CHI TIẾT ĐƠN GIÁ THÁNG ' . $this->month);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow += 2;

        $headers = ['STT', 'TÊN VPP', 'ĐVT', 'SỐ LƯỢNG', 'ĐƠN GIÁ', 'THÀNH TIỀN'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $currentRow, $header);
            $col++;
        }
        $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $stt = 0;
        foreach ($this->categories as $category) {
            if (isset($this->products[$category->id]) && $this->products[$category->id]->count() > 0) {
                foreach ($this->products[$category->id] as $product) {
                    $totalQty = $product->monthlyOrders->sum('quantity');
                    if ($totalQty > 0) {
                        $stt++;
                        $sheet->setCellValue('A' . $currentRow, $stt);
                        $sheet->setCellValue('B' . $currentRow, $product->name);
                        $sheet->setCellValue('C' . $currentRow, $product->unit);
                        $sheet->setCellValue('D' . $currentRow, $totalQty);
                        $sheet->setCellValue('E' . $currentRow, $product->price);
                        $sheet->setCellValue('F' . $currentRow, "=D{$currentRow}*E{$currentRow}");
                        $currentRow++;
                    }
                }
            }
        }
        $sheet->getStyle('A1:F' . ($currentRow - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    protected function createDepartmentSheet($sheet, $department)
    {
        $currentRow = 1;
        $sheet->setCellValue('A' . $currentRow, 'PHIẾU XUẤT KHO - ' . mb_strtoupper($department->name));
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $currentRow += 2;

        $headers = ['STT', 'Tên hàng hóa', 'ĐVT', 'Số lượng', 'Đơn giá', 'Thành tiền'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $currentRow, $header);
            $col++;
        }
        $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $stt = 0;
        foreach ($this->categories as $category) {
            if (isset($this->products[$category->id]) && $this->products[$category->id]->count() > 0) {
                foreach ($this->products[$category->id] as $product) {
                    $order = $product->monthlyOrders->where('department_id', $department->id)->first();
                    $qty = $order ? $order->quantity : 0;

                    if ($qty > 0) {
                        $stt++;
                        $sheet->setCellValue('A' . $currentRow, $stt);
                        $sheet->setCellValue('B' . $currentRow, $product->name);
                        $sheet->setCellValue('C' . $currentRow, $product->unit);
                        $sheet->setCellValue('D' . $currentRow, $qty);
                        $sheet->setCellValue('E' . $currentRow, $product->price);
                        $sheet->setCellValue('F' . $currentRow, "=D{$currentRow}*E{$currentRow}");
                        $currentRow++;
                    }
                }
            }
        }

        $sheet->getStyle('A3:F' . ($currentRow - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
}
