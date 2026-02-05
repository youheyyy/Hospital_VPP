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

class ConsolidatedExport
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

        // Sheet 1: BẢNG TỔNG (Summary by departments)
        $sheet1 = $spreadsheet->getActiveSheet();
        $this->createBangTongSheet($sheet1);

        // Sheet 2: TỔNG HỢP (Detailed)
        $sheet2 = $spreadsheet->createSheet();
        $this->createTongHopSheet($sheet2);

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

        // ===== HEADER SECTION =====
        // Row 1: Company name (left)
        $sheet->setCellValue('A' . $currentRow, 'CÔNG TY CỔ PHẦN BỆNH VIỆN ĐA KHOA TÂM TRÍ CAO LÃNH');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        // Row 2: Address
        $sheet->setCellValue('A' . $currentRow, 'Số 01, đường Lê Thị Riêng, phường 1, Thành phố Cao Lãnh, tỉnh Đồng Tháp');
        $currentRow++;

        // Row 3: Date
        $sheet->setCellValue('A' . $currentRow, 'Đồng Tháp, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y'));
        $sheet->getStyle('A' . $currentRow)->getFont()->setItalic(true);
        $currentRow++;

        $currentRow++; // Empty row

        // Title
        $sheet->setCellValue('A' . $currentRow, 'BẢNG TỔNG HỢP THÁNG ' . str_replace('/', '.', $this->month));
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14)->setName('Times New Roman');
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        $currentRow++; // Empty row

        // ===== TABLE SECTION =====
        $tableStartRow = $currentRow;

        // Build headers: STT, TÊN HÀNG, ĐVT, [Departments...], Tổng SL
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
        $lastCol = chr(ord($col) - 1);

        // Style header row
        $sheet->getStyle('A' . $currentRow . ':' . $lastCol . $currentRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
            'font' => ['bold' => true, 'name' => 'Times New Roman', 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Set row height for header
        $sheet->getRowDimension($currentRow)->setRowHeight(40);

        // Department columns (green background)
        $deptStartCol = 'D';
        $deptEndCol = chr(ord($deptStartCol) + $this->departments->count() - 1);
        $sheet->getStyle($deptStartCol . $currentRow . ':' . $deptEndCol . $currentRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D4EDDA'],
            ],
        ]);

        $currentRow++;

        // Data rows
        $stt = 0;
        $departmentTotals = [];
        $grandTotal = 0;

        foreach ($this->categories as $category) {
            if (isset($this->products[$category->id]) && $this->products[$category->id]->count() > 0) {
                // Category header
                $sheet->setCellValue('A' . $currentRow, $category->name);
                $sheet->mergeCells('A' . $currentRow . ':' . $lastCol . $currentRow);
                $sheet->getStyle('A' . $currentRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '3B82F6'],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'name' => 'Times New Roman',
                    ],
                ]);
                $currentRow++;

                // Products
                foreach ($this->products[$category->id] as $product) {
                    if ($product->monthlyOrders->count() > 0) {
                        $stt++;
                        $col = 'A';
                        $sheet->setCellValue($col++ . $currentRow, $stt);
                        $sheet->setCellValue($col++ . $currentRow, $product->name);
                        $sheet->setCellValue($col++ . $currentRow, $product->unit);

                        $totalQuantity = 0;
                        $deptIndex = 0;
                        foreach ($this->departments as $dept) {
                            $order = $product->monthlyOrders->firstWhere('department_id', $dept->id);
                            $quantity = $order ? $order->quantity : 0;
                            $totalQuantity += $quantity;

                            // Track department totals
                            if (!isset($departmentTotals[$deptIndex])) {
                                $departmentTotals[$deptIndex] = 0;
                            }
                            $departmentTotals[$deptIndex] += $quantity;

                            $sheet->setCellValue($col++ . $currentRow, $quantity > 0 ? $quantity : '');
                            $deptIndex++;
                        }

                        $sheet->setCellValue($col . $currentRow, $totalQuantity);
                        $grandTotal += $totalQuantity;
                        $currentRow++;
                    }
                }
            }
        }

        $dataEndRow = $currentRow - 1; // Last row of product data

        // Add total row
        $col = 'A';
        $sheet->setCellValue($col++ . $currentRow, '');
        $sheet->setCellValue($col++ . $currentRow, 'TỔNG CỘNG');
        $sheet->setCellValue($col++ . $currentRow, '');

        foreach ($departmentTotals as $deptTotal) {
            $sheet->setCellValue($col++ . $currentRow, $deptTotal > 0 ? $deptTotal : '');
        }
        $sheet->setCellValue($col . $currentRow, $grandTotal);

        // Style total row
        $sheet->getStyle('A' . $currentRow . ':' . $lastCol . $currentRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FBBF24'],
            ],
            'font' => ['bold' => true, 'size' => 12, 'name' => 'Times New Roman'],
        ]);

        $tableEndRow = $currentRow; // The new last row including the total row
        $currentRow++; // Move to the next row after the total row

        // Total column styling (yellow background)
        $sheet->getStyle($lastCol . $tableStartRow . ':' . $lastCol . $dataEndRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF3CD'],
            ],
            'font' => ['bold' => true],
        ]);

        // Borders
        $sheet->getStyle('A' . $tableStartRow . ':' . $lastCol . $tableEndRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Set Times New Roman font for entire sheet
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(6);  // STT
        $sheet->getColumnDimension('B')->setAutoSize(true);  // TÊN HÀNG
        $sheet->getColumnDimension('C')->setWidth(10); // ĐVT
        // Department columns and total column auto-size
        for ($i = 0; $i < $this->departments->count() + 1; $i++) {
            $colLetter = chr(ord('D') + $i);
            $sheet->getColumnDimension($colLetter)->setWidth(12);
        }
    }

    protected function createTongHopSheet($sheet)
    {
        $sheet->setTitle('TỔNG HỢP');

        $currentRow = 1;

        // ===== HEADER SECTION =====
        // Row 1: Company name (left)
        $sheet->setCellValue('A' . $currentRow, 'CTCP BỆNH VIỆN ĐA KHOA TÂM TRÍ CAO LÃNH');
        $sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        // Row 2: Department (left)
        $sheet->setCellValue('A' . $currentRow, 'BỘ PHẬN HỖ TRỢ DỊCH VỤ');
        $sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        // Row 3: Date (left, italic)
        $sheet->setCellValue('A' . $currentRow, 'Đồng Tháp, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y'));
        $sheet->getStyle('A' . $currentRow)->getFont()->setItalic(true);
        $currentRow++;

        $currentRow++; // Empty row

        // Row 5: Main title
        $sheet->setCellValue('A' . $currentRow, 'BẢNG ĐỀ NGHỊ  MUA VĂN PHÒNG PHẨM - VẬT TƯ TIÊU HAO (BỆNH VIỆN)');
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        // Row 6: Month subtitle
        $sheet->setCellValue('A' . $currentRow, 'Tháng ' . $this->month);
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        $currentRow++; // Empty row

        // Description paragraphs
        $sheet->setCellValue('A' . $currentRow, 'Căn cứ vào tình hình hoạt động thực tế tại đơn vị;');
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'Căn cứ đề nghị các khoa/phòng tháng ' . $this->month . ' về thực tế nhu cầu sử dụng văn phòng phẩm vật tư tiêu hao hàng tháng trong phục vụ hoạt động chuyên môn của bệnh viện;');
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'Nay Bộ phận hỗ trợ dịch vụ kính trình Ban Giám Đốc phê duyệt mua VPP-VTTH tháng ' . $this->month . '.');
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $currentRow++;

        // Total amount
        $sheet->setCellValue('A' . $currentRow, 'Tổng số tiền:');
        $sheet->setCellValue('B' . $currentRow, number_format($this->grandTotal, 0, ',', '.') . '  đ');
        $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'Số tiền bằng chữ:  Mười ba triệu không trăm bốn mươi một nghìn bảy trăm năm mươi đồng');
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $currentRow++;

        $currentRow++; // Empty row

        // ===== TABLE SECTION =====
        $tableStartRow = $currentRow;

        // Table title
        $sheet->setCellValue('A' . $currentRow, 'BẢNG KÊ MUA HÀNG THÁNG ' . str_replace('/', '.', $this->month));
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setName('Times New Roman');
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D3D3D3'],
            ],
        ]);
        $currentRow++;

        // Company info rows
        $sheet->setCellValue('A' . $currentRow, 'Đơn vị');
        $sheet->setCellValue('D' . $currentRow, 'CÔNG TY CỔ PHẦN BỆNH VIỆN ĐA KHOA TÂM TRÍ CAO LÃNH');
        $sheet->mergeCells('D' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'Địa chỉ:');
        $sheet->setCellValue('D' . $currentRow, 'Số 01, Lê Thị Riêng, Phường 1, TP Cao Lãnh, Đồng Tháp');
        $sheet->mergeCells('D' . $currentRow . ':G' . $currentRow);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'Mã Số Thuế:');
        $sheet->setCellValue('D' . $currentRow, '1400 920 324');
        $sheet->mergeCells('D' . $currentRow . ':G' . $currentRow);
        $currentRow++;

        // Table headers
        $headers = ['STT', 'TÊN VPP - VTTH', 'ĐVT', 'SỐ LƯỢNG', 'ĐƠN GIÁ', 'THÀNH TIỀN', 'GHI CHÚ'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $currentRow, $header);
            $col++;
        }

        // Style table header
        $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
            'font' => ['bold' => true, 'name' => 'Times New Roman'],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $currentRow++;

        // Data rows
        $stt = 0;

        foreach ($this->categories as $category) {
            if (isset($this->products[$category->id]) && $this->products[$category->id]->count() > 0) {
                // Category header
                $sheet->setCellValue('A' . $currentRow, $category->name);
                $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
                $sheet->getStyle('A' . $currentRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '3B82F6'],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'name' => 'Times New Roman',
                    ],
                ]);
                $currentRow++;

                // Products
                foreach ($this->products[$category->id] as $product) {
                    if ($product->monthlyOrders->count() > 0) {
                        $stt++;
                        $totalQuantity = 0;
                        $allNotes = [];

                        foreach ($this->departments as $dept) {
                            $order = $product->monthlyOrders->firstWhere('department_id', $dept->id);
                            if ($order) {
                                $quantity = $order->quantity;
                                $totalQuantity += $quantity;
                                if ($order->notes) {
                                    $allNotes[] = $order->notes;
                                }
                            }
                        }

                        $totalAmount = $totalQuantity * $product->price;

                        $sheet->setCellValue('A' . $currentRow, $stt);
                        $sheet->setCellValue('B' . $currentRow, $product->name);
                        $sheet->setCellValue('C' . $currentRow, $product->unit);
                        $sheet->setCellValue('D' . $currentRow, $totalQuantity);
                        $sheet->setCellValue('E' . $currentRow, $product->price);
                        $sheet->setCellValue('F' . $currentRow, $totalAmount);
                        $sheet->setCellValue('G' . $currentRow, implode("; ", array_unique(array_filter($allNotes))));

                        $currentRow++;
                    }
                }

                // Category total
                $sheet->setCellValue('E' . $currentRow, 'Cộng:');
                $sheet->setCellValue('F' . $currentRow, $this->categoryTotals[$category->id] ?? 0);
                $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'],
                    ],
                    'font' => ['bold' => true],
                ]);
                $currentRow++;
            }
        }

        // Grand total
        $sheet->setCellValue('E' . $currentRow, 'TỔNG CỘNG:');
        $sheet->setCellValue('F' . $currentRow, $this->grandTotal);
        $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FBBF24'],
            ],
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
        ]);
        $tableEndRow = $currentRow;
        $currentRow++;

        // Borders for table
        $sheet->getStyle('A' . $tableStartRow . ':G' . $tableEndRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Number formatting
        $sheet->getStyle('E' . ($tableStartRow + 5) . ':E' . $tableEndRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F' . ($tableStartRow + 5) . ':F' . $tableEndRow)->getNumberFormat()->setFormatCode('#,##0');

        $currentRow++; // Empty row
        $currentRow++; // Empty row

        // ===== SIGNATURE SECTION =====
        $sheet->setCellValue('A' . $currentRow, 'BP.HTDV');
        $sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
        $sheet->setCellValue('D' . $currentRow, 'TRƯỞNG PHÒNG TCKT');
        $sheet->mergeCells('D' . $currentRow . ':E' . $currentRow);
        $sheet->setCellValue('F' . $currentRow, 'BAN GIÁM ĐỐC');
        $sheet->mergeCells('F' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $currentRow)->getFont()->setBold(true);
        $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F' . $currentRow)->getFont()->setBold(true);
        $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow += 6; // Space for signatures

        // Names
        $sheet->setCellValue('A' . $currentRow, 'Nguyễn Thị Thúy Trang');
        $sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
        $sheet->setCellValue('D' . $currentRow, 'Nguyễn Thị Thúy Huỳnh');
        $sheet->mergeCells('D' . $currentRow . ':E' . $currentRow);
        $sheet->setCellValue('F' . $currentRow, 'Huỳnh Thị Nguyệt');
        $sheet->mergeCells('F' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $currentRow)->getFont()->setBold(true);
        $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F' . $currentRow)->getFont()->setBold(true);
        $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(6);  // STT - narrow
        $sheet->getColumnDimension('B')->setAutoSize(true);  // TÊN VPP
        $sheet->getColumnDimension('C')->setWidth(10); // ĐVT
        $sheet->getColumnDimension('D')->setWidth(12); // SỐ LƯỢNG
        $sheet->getColumnDimension('E')->setWidth(15); // ĐƠN GIÁ
        $sheet->getColumnDimension('F')->setWidth(15); // THÀNH TIỀN
        $sheet->getColumnDimension('G')->setWidth(20); // GHI CHÚ
    }
}
