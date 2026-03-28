<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In Bảng Tổng - Tháng {{ $selectedMonth }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; background: white; }
        .page { padding: 1.5cm; margin: 0 auto; position: relative; }
        
        @media print {
            body { background: none; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            @page { size: A4 landscape; margin: 0; }
            .page { margin: 10mm 15mm; padding: 0 !important; width: auto; page-break-after: always; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid black; padding: 4px; font-size: 10pt; }
        th { background: #4472c4 !important; color: white !important; font-weight: bold; text-align: center; text-transform: uppercase; }
        .bg-category { background-color: #e2efda !important; font-weight: bold; }
        .bg-total { background-color: #fff2cc !important; font-weight: bold; }
        
        .header-title { margin: 20px 0; font-size: 16pt; }
    </style>
</head>
<body>
    <div class="page page-landscape">
        <div class="text-center header-title">
            <p class="font-bold uppercase">BẢNG TỔNG HỢP NHU CẦU VĂN PHÒNG PHẨM - VẬT TƯ TIÊU HAO</p>
            <p class="font-bold">Tháng {{ $selectedMonth }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">STT</th>
                    <th style="width: 250px;">TÊN HÀNG</th>
                    <th style="width: 50px;">ĐVT</th>
                    @foreach($departments as $dept)
                    <th style="font-size: 9pt;">{{ mb_strtoupper($dept->name, 'UTF-8') }}</th>
                    @endforeach
                    <th style="width: 80px;">Tổng SL</th>
                </tr>
            </thead>
            <tbody>
                @php $stt = 0; @endphp
                @foreach($categories as $category)
                    @if(isset($products[$category->id]) && $products[$category->id]->count() > 0)
                        @php
                            $catProducts = $products[$category->id]->filter(fn($p) => $p->monthlyOrders->where('month', $selectedMonth)->sum('quantity') > 0);
                        @endphp
                        @if($catProducts->count() > 0)
                            <tr class="bg-category">
                                <td colspan="{{ 4 + $departments->count() }}">{{ mb_strtoupper($category->name, 'UTF-8') }}</td>
                            </tr>
                            @foreach($catProducts as $product)
                                @php
                                    $stt++;
                                    $monthlyOrders = $product->monthlyOrders->where('month', $selectedMonth);
                                    $totalQuantity = 0;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $stt }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td class="text-center">{{ $product->unit }}</td>
                                    @foreach($departments as $dept)
                                        @php
                                            $order = $monthlyOrders->firstWhere('department_id', $dept->id);
                                            $quantity = $order ? $order->quantity : 0;
                                            $totalQuantity += $quantity;
                                        @endphp
                                        <td class="text-center">{{ $quantity > 0 ? $quantity : '' }}</td>
                                    @endforeach
                                    <td class="text-right font-bold">{{ number_format($totalQuantity, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="3" class="text-right">TỔNG CỘNG SỐ LƯỢNG:</td>
                    @php $overallQty = 0; @endphp
                    @foreach($departments as $dept)
                        @php
                            $deptQty = 0;
                            foreach($categories as $cat) {
                                if(isset($products[$cat->id])) {
                                    $catProducts = $products[$cat->id];
                                    foreach($catProducts as $p) {
                                        $deptQty += $p->monthlyOrders->where('month', $selectedMonth)->where('department_id', $dept->id)->sum('quantity');
                                    }
                                }
                            }
                            $overallQty += $deptQty;
                        @endphp
                        <td class="text-center">{{ $deptQty > 0 ? number_format($deptQty, 0, ',', '.') : '' }}</td>
                    @endforeach
                    <td class="text-right font-bold">{{ number_format($overallQty, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
