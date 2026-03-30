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

class ConsolidatedAllExport
{
    protected $month;
    protected $departments;
    protected $categories;
    protected $products;
    protected $categoryTotals;
    protected $grandTotal;
    protected $tabType;
    protected $deptId;
    protected $exportMode; // Fixed to 'aggregated_vpp_all'

    public function __construct($month, $departments, $categories, $products, $categoryTotals, $grandTotal, $tabType = 'all', $deptId = null)
    {
        $this->month = $month;
        $this->departments = $departments;
        $this->categories = $categories;
        $this->products = $products;
        $this->categoryTotals = $categoryTotals;
        $this->grandTotal = $grandTotal;
        $this->tabType = $tabType;
        $this->deptId = $deptId;
        $this->exportMode = 'aggregated_vpp_all';
    }

    public function download($filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheetCount = 0;

        if ($this->tabType === 'bang_tong' || $this->tabType === 'all') {
            $sheet = $spreadsheet->getActiveSheet();
            $this->createBangTongSheet($sheet);
            $sheetCount++;
        }

        if ($this->tabType === 'tong_hop' || $this->tabType === 'all') {
            $sheet = $sheetCount == 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $this->createTongHopSheet($sheet);
            $sheetCount++;
        }

        if ($this->tabType === 'phieu_xuat_kho' && $this->deptId) {
            $dept = $this->departments->firstWhere('id', $this->deptId);
            if ($dept) {
                $sheet = $sheetCount == 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
                $sheetTitle = mb_substr($dept->name, 0, 31);
                $sheet->setTitle($sheetTitle);
                $this->createDepartmentSheet($sheet, $dept);
                $sheetCount++;
            }
        } elseif ($this->tabType === 'all') {
            foreach ($this->departments as $dept) {
                $hasOrders = false;
                foreach ($this->products as $categoryId => $categoryProducts) {
                    foreach ($categoryProducts as $product) {
                        $order = $product->monthlyOrders
                            ->where('department_id', $dept->id)
                            ->where('month', $this->month)
                            ->first();
                        if ($order && $order->quantity > 0) {
                            $hasOrders = true;
                            break 2;
                        }
                    }
                }

                if ($hasOrders) {
                    $sheet = $spreadsheet->createSheet();
                    $sheetTitle = mb_substr($dept->name, 0, 31);
                    $sheet->setTitle($sheetTitle);
                    $this->createDepartmentSheet($sheet, $dept);
                }
            }
        }

        if ($spreadsheet->getSheetCount() > 0) {
            $spreadsheet->setActiveSheetIndex(0);
        }

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

        // Header section (same as before)
        $sheet->setCellValue('A' . $currentRow, 'CÔNG TY CỔ PHẦN BỆNH VIỆN ĐA KHOA TÂM TRÍ CAO LÃNH');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'Số 01, đường Lê Thị Riêng, phường 1, Thành phố Cao Lãnh, tỉnh Đồng Tháp');
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'Đồng Tháp, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y'));
        $sheet->getStyle('A' . $currentRow)->getFont()->setItalic(true);
        $currentRow++;
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'BẢNG TỔNG HỢP THÁNG ' . str_replace('/', '.', $this->month));
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14)->setName('Times New Roman');
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow++;
        $currentRow++;

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

        $sheet->getStyle('A' . $currentRow . ':' . $lastCol . $currentRow)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
            'font' => ['bold' => true, 'name' => 'Times New Roman', 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension($currentRow)->setRowHeight(40);
        $currentRow++;

        $stt = 0;
        $departmentTotals = [];
        $grandTotal = 0;

        foreach ($this->categories as $category) {
            if (isset($this->products[$category->id]) && $this->products[$category->id]->count() > 0) {
                // Pre-filter products for this mode (skip forms)
                $categoryProducts = $this->products[$category->id]->filter(function($p) {
                    return !$p->is_form;
                });

                if ($categoryProducts->count() > 0) {
                    $sheet->setCellValue('A' . $currentRow, $category->name);
                    $sheet->mergeCells('A' . $currentRow . ':' . $lastCol . $currentRow);
                    $sheet->getStyle('A' . $currentRow)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']],
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Times New Roman'],
                    ]);
                    $currentRow++;

                    foreach ($categoryProducts as $product) {
                        $totalQuantity = 0;
                        $hasOrderInMonth = false;
                        foreach ($this->departments as $dept) {
                            $order = $product->monthlyOrders->where('department_id', $dept->id)->where('month', $this->month)->first();
                            if ($order && $order->quantity > 0) $hasOrderInMonth = true;
                        }

                        if ($hasOrderInMonth) {
                            $stt++;
                            $colIdx = 1;
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $stt);
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $product->name);
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $product->unit);

                            $deptIndex = 0;
                            foreach ($this->departments as $dept) {
                                $order = $product->monthlyOrders->where('department_id', $dept->id)->where('month', $this->month)->first();
                                $quantity = $order ? $order->quantity : 0;
                                $totalQuantity += $quantity;
                                if (!isset($departmentTotals[$deptIndex])) $departmentTotals[$deptIndex] = 0;
                                $departmentTotals[$deptIndex] += $quantity;
                                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $quantity > 0 ? $quantity : '');
                                $deptIndex++;
                            }
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx) . $currentRow, $totalQuantity);
                            $grandTotal += $totalQuantity;
                            $currentRow++;
                        }
                    }
                }
            }
        }

        // Add Aggregated Forms into Bảng Tổng
        $paperAggregation = $this->calculateMasterPaperAggregation();
        if (!empty($paperAggregation)) {
            $sheet->setCellValue('A' . $currentRow, 'BIỂU MẪU (DẠNG GIẤY)');
            $sheet->mergeCells('A' . $currentRow . ':' . $lastCol . $currentRow);
            $sheet->getStyle('A' . $currentRow)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Times New Roman'],
            ]);
            $currentRow++;

            foreach ($paperAggregation as $size => $data) {
                $stt++;
                $colIdx = 1;
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $stt);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, 'Giấy ' . $size);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, 'Gram');

                $totalRowWeight = 0;
                $deptIndex = 0;
                foreach ($this->departments as $dept) {
                    $weight = $data['depts'][$dept->id] ?? 0;
                    $totalRowWeight += $weight;
                    if (!isset($departmentTotals[$deptIndex])) $departmentTotals[$deptIndex] = 0;
                    // Note: Paper weight doesn't easily sum into product totals but we include them here as requested
                    $departmentTotals[$deptIndex] += $weight; 
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $weight > 0 ? $weight : '');
                    $deptIndex++;
                }
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx) . $currentRow, $totalRowWeight);
                $grandTotal += $totalRowWeight;
                $currentRow++;
            }
        }

        // Total row
        $col = 'A';
        $sheet->setCellValue($col++ . $currentRow, '');
        $sheet->setCellValue($col++ . $currentRow, 'TỔNG CỘNG');
        $sheet->setCellValue($col++ . $currentRow, '');
        foreach ($departmentTotals as $deptTotal) {
            $sheet->setCellValue($col++ . $currentRow, $deptTotal > 0 ? $deptTotal : '');
        }
        $sheet->setCellValue($col . $currentRow, $grandTotal);
        $sheet->getStyle('A' . $currentRow . ':' . $lastCol . $currentRow)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FBBF24']],
            'font' => ['bold' => true, 'size' => 12, 'name' => 'Times New Roman'],
        ]);
        $tableEndRow = $currentRow;

        // Styling and formatting
        $sheet->getStyle('A' . $tableStartRow . ':' . $lastCol . $tableEndRow)->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $colIdx = 4;
        for ($i = 0; $i < $this->departments->count() + 1; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIdx + $i))->setWidth(12);
        }
    }

    protected function createTongHopSheet($sheet)
    {
        $sheet->setTitle('TỔNG HỢP');
        $currentRow = 1;
        // ... (Header and Title sections same as ConsolidatedExport)
        // I will simplify the replication to ensure logic is correct
        $this->renderTongHopHeader($sheet, $currentRow);
        
        $tableStartRow = $currentRow;
        $headers = ['STT', 'TÊN VPP - VTTH', 'ĐVT', 'SỐ LƯỢNG', 'ĐƠN GIÁ', 'THÀNH TIỀN', 'GHI CHÚ'];
        $colIdx = 1;
        foreach ($headers as $h) $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $h);
        $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
            'font' => ['bold' => true, 'name' => 'Times New Roman'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $currentRow++;

        $stt = 0;
        foreach ($this->categories as $category) {
            $categoryProducts = $this->products[$category->id] ?? collect();
            $filteredProducts = $categoryProducts->filter(fn($p) => !$p->is_form);
            
            if ($filteredProducts->count() > 0) {
                $sheet->setCellValue('A' . $currentRow, $category->name);
                $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
                $sheet->getStyle('A' . $currentRow)->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]]);
                $currentRow++;

                foreach ($filteredProducts as $product) {
                    $totalQuantity = $product->monthlyOrders->where('month', $this->month)->sum('quantity');
                    if ($totalQuantity > 0) {
                        $stt++;
                        $sheet->setCellValue('A' . $currentRow, $stt);
                        $sheet->setCellValue('B' . $currentRow, $product->name);
                        $sheet->setCellValue('C' . $currentRow, $product->unit);
                        $sheet->setCellValue('D' . $currentRow, $totalQuantity);
                        $sheet->setCellValue('E' . $currentRow, $product->price);
                        $sheet->setCellValue('F' . $currentRow, $totalQuantity * $product->price);
                        $currentRow++;
                    }
                }
            }
        }

        // Add Paper aggregation
        $paperAggregation = $this->calculateSummaryPaperAggregation();
        if (!empty($paperAggregation)) {
            $sheet->setCellValue('A' . $currentRow, 'BIỂU MẪU (DẠNG GIẤY)');
            $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]]);
            $currentRow++;
            foreach ($paperAggregation as $size => $data) {
                $stt++;
                $sheet->setCellValue('A' . $currentRow, $stt);
                $sheet->setCellValue('B' . $currentRow, 'Giấy ' . $size);
                $sheet->setCellValue('C' . $currentRow, 'Gram');
                $sheet->setCellValue('D' . $currentRow, $data['weight']);
                $currentRow++;
            }
        }

        $sheet->getStyle('A' . $tableStartRow . ':G' . $currentRow)->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
    }

    protected function createDepartmentSheet($sheet, $department)
    {
        $sheet->setTitle(mb_substr($department->name, 0, 31));
        $currentRow = 1;
        $this->renderDepartmentHeader($sheet, $department, $currentRow);

        $headers = ['STT', 'Tên hàng hóa', 'ĐVT', 'Số lượng', 'Đơn giá', 'Thành tiền', 'Ghi chú'];
        $colIdx = 1;
        foreach ($headers as $h) $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . $currentRow, $h);
        $currentRow++;

        $stt = 0;
        foreach ($this->categories as $category) {
            $categoryTotal = 0;
            $rows = [];
            foreach ($this->products[$category->id] ?? [] as $product) {
                if ($product->is_form) continue;
                $order = $product->monthlyOrders->where('department_id', $department->id)->where('month', $this->month)->first();
                if ($order && $order->quantity > 0) {
                    $rows[] = [$product, $order->quantity, $product->price];
                    $categoryTotal += $order->quantity * $product->price;
                }
            }

            if (count($rows) > 0) {
                $sheet->setCellValue('A' . $currentRow, $category->name);
                $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
                $currentRow++;
                foreach ($rows as $row) {
                    $stt++;
                    $sheet->setCellValue('A' . $currentRow, $stt);
                    $sheet->setCellValue('B' . $currentRow, $row[0]->name);
                    $sheet->setCellValue('C' . $currentRow, $row[0]->unit);
                    $sheet->setCellValue('D' . $currentRow, $row[1]);
                    $sheet->setCellValue('E' . $currentRow, $row[2]);
                    $sheet->setCellValue('F' . $currentRow, $row[1] * $row[2]);
                    $currentRow++;
                }
            }
        }

        $deptAggregation = $this->calculateDepartmentPaperAggregation($department->id);
        if (!empty($deptAggregation)) {
            $sheet->setCellValue('A' . $currentRow, 'BIỂU MẪU (TỔNG THEO GRAM)');
            $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
            $currentRow++;
            foreach ($deptAggregation as $size => $data) {
                $stt++;
                $sheet->setCellValue('A' . $currentRow, $stt);
                $sheet->setCellValue('B' . $currentRow, 'Giấy ' . $size);
                $sheet->setCellValue('C' . $currentRow, 'Gram');
                $sheet->setCellValue('D' . $currentRow, $data['weight']);
                $currentRow++;
            }
        }
    }

    protected function calculateMasterPaperAggregation()
    {
        $aggregation = [];
        foreach ($this->products as $categoryId => $categoryProducts) {
            foreach ($categoryProducts as $product) {
                if ($product->is_form && $product->paper_size) {
                    if (!isset($aggregation[$product->paper_size])) {
                        $aggregation[$product->paper_size] = ['depts' => []];
                    }
                    foreach ($this->departments as $dept) {
                        $order = $product->monthlyOrders->where('department_id', $dept->id)->where('month', $this->month)->first();
                        if ($order && $order->quantity > 0) {
                            $sheets = ($product->unit === 'Cuốn') ? $order->quantity * 60 : $order->quantity;
                            if (!isset($aggregation[$product->paper_size]['depts'][$dept->id])) {
                                $aggregation[$product->paper_size]['depts'][$dept->id] = 0;
                            }
                            $aggregation[$product->paper_size]['depts'][$dept->id] += $sheets;
                        }
                    }
                }
            }
        }
        foreach ($aggregation as $size => &$data) {
            foreach ($data['depts'] as $deptId => $totalSheets) {
                $data['depts'][$deptId] = ceil($totalSheets / 500);
            }
        }
        return $aggregation;
    }

    protected function calculateSummaryPaperAggregation()
    {
        $aggregation = [];
        foreach ($this->products as $categoryId => $categoryProducts) {
            foreach ($categoryProducts as $product) {
                if ($product->is_form && $product->paper_size) {
                    $totalSheets = 0;
                    foreach ($product->monthlyOrders->where('month', $this->month) as $order) {
                        $totalSheets += ($product->unit === 'Cuốn') ? $order->quantity * 60 : $order->quantity;
                    }
                    if ($totalSheets > 0) {
                        if (!isset($aggregation[$product->paper_size])) $aggregation[$product->paper_size] = ['total_sheets' => 0];
                        $aggregation[$product->paper_size]['total_sheets'] += $totalSheets;
                    }
                }
            }
        }
        foreach ($aggregation as $size => &$data) $data['weight'] = ceil($data['total_sheets'] / 500);
        return $aggregation;
    }

    protected function calculateDepartmentPaperAggregation($deptId)
    {
        $aggregation = [];
        foreach ($this->products as $categoryId => $categoryProducts) {
            foreach ($categoryProducts as $product) {
                if ($product->is_form && $product->paper_size) {
                    $order = $product->monthlyOrders->where('department_id', $deptId)->where('month', $this->month)->first();
                    if ($order && $order->quantity > 0) {
                        if (!isset($aggregation[$product->paper_size])) $aggregation[$product->paper_size] = ['total_sheets' => 0];
                        $aggregation[$product->paper_size]['total_sheets'] += ($product->unit === 'Cuốn') ? $order->quantity * 60 : $order->quantity;
                    }
                }
            }
        }
        foreach ($aggregation as $size => &$data) $data['weight'] = ceil($data['total_sheets'] / 500);
        return $aggregation;
    }

    protected function renderTongHopHeader($sheet, &$currentRow)
    {
        $sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $sheet->setCellValue('A' . $currentRow, 'CTCP BỆNH VIỆN ĐA KHOA TÂM TRÍ CAO LÃNH');

        $sheet->mergeCells('D' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $currentRow)->getFont()->setBold(true);
        $sheet->setCellValue('D' . $currentRow, 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM');
        $currentRow++;

        $sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $sheet->setCellValue('A' . $currentRow, 'BỘ PHẬN HỖ TRỢ DỊCH VỤ');

        $sheet->mergeCells('D' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $currentRow)->getFont()->setBold(true)->setUnderline(true);
        $sheet->setCellValue('D' . $currentRow, 'Độc lập - Tự do - Hạnh phúc');
        $currentRow++;

        $sheet->mergeCells('D' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $currentRow)->getFont()->setItalic(true);
        $sheet->setCellValue('D' . $currentRow, 'Đồng Tháp, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y'));
        $currentRow += 2;

        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A' . $currentRow, 'BẢNG ĐỀ NGHỊ MUA VĂN PHÒNG PHẨM - VẬT TƯ TIÊU HAO (BỆNH VIỆN)');
        $currentRow++;

        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $sheet->setCellValue('A' . $currentRow, 'Tháng ' . $this->month);
        $currentRow += 2;

        $sheet->setCellValue('A' . $currentRow, 'Tổng số tiền: ' . number_format($this->grandTotal, 0, ',', '.') . ' đ');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'Số tiền bằng chữ: ' . $this->numberToWords((int)$this->grandTotal));
        $currentRow += 2;
    }

    protected function renderDepartmentHeader($sheet, $department, &$currentRow)
    {
        $sheet->setCellValue('A' . $currentRow, 'CTY CP BV ĐA KHOA TÂM TRÍ CAO LÃNH');
        $sheet->mergeCells('A' . $currentRow . ':D' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $sheet->setCellValue('E' . $currentRow, 'Mẫu số 02-VT');
        $sheet->mergeCells('E' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('E' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'P. HỖ TRỢ DỊCH VỤ');
        $sheet->mergeCells('A' . $currentRow . ':D' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $sheet->setCellValue('E' . $currentRow, '(Ban hành theo TT số 200/2014/TT-BTC');
        $sheet->mergeCells('E' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('E' . $currentRow)->getFont()->setItalic(true);
        $sheet->getStyle('E' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $currentRow++;

        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A' . $currentRow, 'PHIẾU XUẤT KHO VÀ BIÊN BẢN BÀN GIAO NỘI BỘ');
        $currentRow++;

        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('3B82F6');
        $sheet->setCellValue('A' . $currentRow, mb_strtoupper($department->name));
        $currentRow += 2;
    }

    protected function getDepartmentNote($order)
    {
        $notes = [];
        if ($order->notes) $notes[] = $order->notes;
        if ($order->admin_notes) {
            $parts = explode('|||', $order->admin_notes);
            $privatePart = isset($parts[1]) ? trim($parts[1]) : '';
            if ($privatePart !== '') $notes[] = $privatePart;
        }
        return implode(' - ', $notes);
    }

    protected function romanize($num)
    {
        $map = ['M'=>1000,'CM'=>900,'D'=>500,'CD'=>400,'C'=>100,'XC'=>90,'L'=>50,'XL'=>40,'X'=>10,'IX'=>9,'V'=>5,'IV'=>4,'I'=>1];
        $res = '';
        while ($num > 0) {
            foreach ($map as $roman => $int) {
                if ($num >= $int) {
                    $num -= $int;
                    $res .= $roman;
                    break;
                }
            }
        }
        return $res;
    }

    protected function numberToWords(int $number): string
    {
        $chuSo = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $docBlock = function (int $n) use ($chuSo): string {
            $tram=intdiv($n,100); $chuc=intdiv($n%100,10); $donvi=$n%10; $res='';
            if($tram>0)$res.=$chuSo[$tram].' trăm ';
            if($chuc>1)$res.=$chuSo[$chuc].' mươi ';
            elseif($chuc===1)$res.='mười ';
            elseif($tram>0 && $donvi>0)$res.='lẻ ';
            if($donvi===5 && $chuc>=1)$res.='lăm';
            elseif($donvi===1 && $chuc>0)$res.='mốt';
            elseif($donvi>0)$res.=$chuSo[$donvi];
            return $res;
        };
        $hangDonVi = ['', ' nghìn', ' triệu', ' tỷ', ' nghìn tỷ', ' triệu tỷ'];
        if ($number === 0) return 'Không đồng';
        $res = ''; $i = 0; $num = $number;
        do {
            $block = $num % 1000;
            if ($block > 0) {
                $s = $docBlock($block);
                $res = trim($s) . $hangDonVi[$i] . ($res !== '' ? ' ' : '') . $res;
            }
            $i++; $num = intdiv($num, 1000);
        } while ($num > 0);
        return ucfirst(trim($res)) . ' đồng';
    }
}
