@php
    function docSoThanhChu($number) {
        $chuSo = ["không", "một", "hai", "ba", "bốn", "năm", "sáu", "bảy", "tám", "chín"];
        $docBlock = function($number) use ($chuSo, &$docBlock) {
            $tram = floor($number / 100);
            $chuc = floor(($number % 100) / 10);
            $donvi = $number % 10;
            $res = "";
            if ($tram > 0) $res .= $chuSo[$tram] . " trăm ";
            else if ($res !== "") $res .= "không trăm ";
            if ($chuc > 1) $res .= $chuSo[$chuc] . " mươi ";
            else if ($chuc === 1) $res .= "mười ";
            else if ($tram > 0 && $donvi > 0) $res .= "lẻ ";
            if ($donvi === 5 && $chuc >= 1) $res .= "lăm";
            else if ($donvi > 1 || ($donvi === 1 && $chuc === 0)) $res .= $chuSo[$donvi];
            else if ($donvi === 1 && $chuc > 0) $res .= "mốt";
            return $res;
        };
        $hangDonVi = ["", " nghìn", " triệu", " tỷ", " nghìn tỷ", " triệu tỷ"];
        if ($number == 0) return "Không đồng";
        $res = ""; $i = 0;
        $num = (float)$number;
        do {
            $block = $num % 1000;
            if ($block > 0) {
                $s = $docBlock($block);
                $res = $s . $hangDonVi[$i] . ($res !== "" ? " " : "") . $res;
            }
            $i++;
            $num = floor($num / 1000);
        } while ($num > 0);
        return ucfirst(trim($res)) . " đồng./.";
    }
@endphp
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Tổng Hợp - Tháng {{ $selectedMonth }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.3; background: white; }
        .page { padding: 1.5cm; width: 210mm; margin: 0 auto; min-height: 297mm; position: relative; }
        .page-landscape { width: 297mm; min-height: 210mm; }

        @media print {
            body { background: none; }
            .page { margin: 0; border: none; width: auto; min-height: auto; page-break-after: always; }
            .no-print { display: none; }
            @page { margin: 0; }
        }

        /* Common Styles */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .italic { font-style: italic; }
        .uppercase { text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid black; padding: 4px 6px; font-size: 11pt; }
        th { background: #f3f4f6; font-weight: bold; text-align: center; }

        /* Header info */
        .header-section { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .company-info { text-align: left; }
        .national-info { text-align: center; }

        /* Signatures */
        .signature-section { display: flex; justify-content: space-around; margin-top: 30px; page-break-inside: avoid; }
        .sig-block { text-align: center; flex: 1; }
        .sig-title { font-weight: bold; margin-bottom: 70px; }
        .sig-name { font-weight: bold; }

        /* Specific Table Coloring */
        .bg-blue-100 { background-color: #ebf8ff; }
        .bg-yellow-100 { background-color: #fef9c3; }
        .bg-orange-100 { background-color: #ffedd5; }
    </style>
</head>

<body>
    <!-- 1. BẢNG TỔNG (Landscape because of many departments) -->
    <div class="page page-landscape">
        <div class="header-section">
            <div class="company-info">
                <p class="font-bold uppercase">CTCP BỆNH VIỆN ĐA KHOA</p>
                <p class="font-bold uppercase">TÂM TRÍ CAO LÃNH</p>
                <p>Bộ phận hỗ trợ dịch vụ</p>
            </div>
            <div class="national-info">
                <p class="font-bold uppercase">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</p>
                <p class="font-bold">Độc lập - Tự do - Hạnh phúc</p>
            </div>
        </div>

        <div class="text-center" style="margin: 20px 0;">
            <p class="font-bold" style="font-size: 16pt; text-transform: uppercase;">BẢNG TỔNG HỢP NHU CẦU VPP - VTTH</p>
            <p class="font-bold">Tháng {{ $selectedMonth }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 40px;">STT</th>
                    <th rowspan="2">TÊN HÀNG</th>
                    <th rowspan="2" style="width: 60px;">ĐVT</th>
                    <th colspan="{{ $departments->count() }}">KHOA / PHÒNG</th>
                    <th rowspan="2" style="width: 80px;">TỔNG SL</th>
                </tr>
                <tr>
                    @foreach($departments as $dept)
                        <th style="font-size: 8pt; width: 60px;">{{ $dept->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $stt = 0; @endphp
                @foreach($categories as $category)
                    @if(isset($products[$category->id]) && $products[$category->id]->count() > 0)
                        <tr class="bg-blue-100">
                            <td colspan="{{ 4 + $departments->count() }}" class="font-bold uppercase">{{ $category->name }}</td>
                        </tr>
                        @foreach($products[$category->id] as $product)
                            @php
                                $totalQty = $product->monthlyOrders->sum('quantity');
                            @endphp
                            @if($totalQty > 0)
                                @php $stt++; @endphp
                                <tr>
                                    <td class="text-center">{{ $stt }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td class="text-center">{{ $product->unit }}</td>
                                    @foreach($departments as $dept)
                                        @php $qty = $product->monthlyOrders->where('department_id', $dept->id)->sum('quantity'); @endphp
                                        <td class="text-right">{{ $qty > 0 ? number_format($qty, 0, ',', '.') : '' }}</td>
                                    @endforeach
                                    <td class="text-right font-bold bg-yellow-100">{{ number_format($totalQty, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>

        <div class="signature-section">
            <div class="sig-block">
                <p class="sig-title">LẬP BẢNG</p>
                <p class="sig-name">Nguyễn Thị Thùy Trang</p>
            </div>
            <div class="sig-block">
                <p class="sig-title">PHÒNG TCKT</p>
                <p class="sig-name">Nguyễn Thị Thúy Huỳnh</p>
            </div>
            <div class="sig-block">
                <p class="sig-title">BAN GIÁM ĐỐC</p>
                <p class="sig-name">Huỳnh Thị Nguyệt</p>
            </div>
        </div>
    </div>

    <!-- 2. TỔNG HỢP (Portrait) -->
    <div class="page">
        <div class="header-section">
            <div class="company-info">
                <p class="font-bold uppercase">CTCP BỆNH VIỆN ĐA KHOA</p>
                <p class="font-bold uppercase">TÂM TRÍ CAO LÃNH</p>
                <p>Bộ phận hỗ trợ dịch vụ</p>
            </div>
            <div class="national-info">
                <p class="font-bold uppercase">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</p>
                <p class="font-bold">Độc lập - Tự do - Hạnh phúc</p>
                <p class="italic" style="margin-top: 5px;">Đồng Tháp, ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</p>
            </div>
        </div>

        <div class="text-center" style="margin: 20px 0;">
            <p class="font-bold" style="font-size: 14pt; text-transform: uppercase;">BẢNG ĐỀ NGHỊ MUA VĂN PHÒNG PHẨM - VẬT TƯ TIÊU HAO</p>
            <p class="font-bold">Tháng {{ $selectedMonth }}</p>
        </div>

        <div style="margin: 15px 0;">
            <p>- Căn cứ vào tình hình hoạt động thực tế tại đơn vị;</p>
            <p>- Căn cứ đề nghị các khoa/phòng tháng {{ $selectedMonth }} về nhu cầu sử dụng VPP-VTTH;</p>
            <p>Nay Bộ phận hỗ trợ dịch vụ kính trình Ban Giám Đốc phê duyệt mua sắm phục vụ hoạt động chuyên môn.</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">STT</th>
                    <th>TÊN VPP - VTTH</th>
                    <th style="width: 60px;">ĐVT</th>
                    <th style="width: 80px;">SL</th>
                    <th style="width: 100px;">ĐƠN GIÁ</th>
                    <th style="width: 120px;">THÀNH TIỀN</th>
                </tr>
            </thead>
            <tbody>
                @php $stt = 0; @endphp
                @foreach($categories as $category)
                    @if(isset($products[$category->id]) && $products[$category->id]->count() > 0)
                        @php 
                            $catProducts = $products[$category->id]->filter(fn($p) => $p->monthlyOrders->sum('quantity') > 0);
                        @endphp
                        @if($catProducts->count() > 0)
                            <tr class="bg-blue-100">
                                <td colspan="6" class="font-bold">{{ $category->name }}</td>
                            </tr>
                            @foreach($catProducts as $product)
                                @php 
                                    $stt++;
                                    $qty = $product->monthlyOrders->sum('quantity');
                                    $total = $qty * $product->price;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $stt }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td class="text-center">{{ $product->unit }}</td>
                                    <td class="text-right">{{ number_format($qty, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td class="text-right font-bold">{{ number_format($total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    @endif
                @endforeach
                <tr class="bg-orange-100 font-bold">
                    <td colspan="5" class="text-right uppercase">Tổng cộng:</td>
                    <td class="text-right">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div style="margin: 15px 0;">
            <p class="font-bold">Tổng số tiền: {{ number_format($grandTotal, 0, ',', '.') }} đ</p>
            <p class="italic">Số tiền bằng chữ: {{ docSoThanhChu($grandTotal) }}</p>
        </div>

        <div class="signature-section">
            <div class="sig-block">
                <p class="sig-title">BP.HTDV</p>
                <p class="sig-name">Nguyễn Thị Thùy Trang</p>
            </div>
            <div class="sig-block">
                <p class="sig-title">TRƯỞNG PHÒNG TCKT</p>
                <p class="sig-name">Nguyễn Thị Thúy Huỳnh</p>
            </div>
            <div class="sig-block">
                <p class="sig-title">BAN GIÁM ĐỐC</p>
                <p class="sig-name">Huỳnh Thị Nguyệt</p>
            </div>
        </div>
    </div>

    <!-- 3. PHIẾU XUẤT KHO (One for each department) -->
    @foreach($departmentOrders as $deptId => $data)
    <div class="page">
        <div class="header-section">
            <div class="company-info">
                <p class="font-bold uppercase">CTCP BỆNH VIỆN ĐA KHOA</p>
                <p class="font-bold uppercase">TÂM TRÍ CAO LÃNH</p>
                <p>Khoa dược / P. HTDV</p>
            </div>
            <div class="national-info">
                <p class="font-bold">Mẫu số 02-VT</p>
                <p class="italic" style="font-size: 9pt;">(Ban hành theo TT số 200/2014/TT-BTC)</p>
                <p class="italic" style="margin-top: 5px;">Ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</p>
            </div>
        </div>

        <div class="text-center" style="margin: 20px 0;">
            <p class="font-bold" style="font-size: 14pt; text-transform: uppercase;">PHIẾU XUẤT KHO VÀ BIÊN BẢN BÀN GIAO</p>
            <p class="font-bold uppercase">Khoa/Phòng: {{ $data['department']->name }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">STT</th>
                    <th>TÊN HÀNG HÓA</th>
                    <th style="width: 60px;">ĐVT</th>
                    <th style="width: 80px;">SỐ LƯỢNG</th>
                    <th style="width: 100px;">ĐƠN GIÁ</th>
                    <th style="width: 120px;">THÀNH TIỀN</th>
                </tr>
            </thead>
            <tbody>
                @php $stt = 0; @endphp
                @foreach($data['sections'] as $section)
                    <tr class="bg-blue-100">
                        <td colspan="6" class="font-bold italic">{{ $section['category']->name }}</td>
                    </tr>
                    @foreach($section['orders'] as $item)
                        @php $stt++; @endphp
                        <tr>
                            <td class="text-center">{{ $stt }}</td>
                            <td>{{ $item['product']->name }}</td>
                            <td class="text-center">{{ $item['product']->unit }}</td>
                            <td class="text-right">{{ number_format($item['order']->quantity, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($item['product']->price, 0, ',', '.') }}</td>
                            <td class="text-right font-bold">{{ number_format($item['total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endforeach
                <tr class="bg-orange-100 font-bold">
                    <td colspan="5" class="text-right uppercase">Cộng:</td>
                    <td class="text-right">{{ number_format($data['total'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section">
            <div class="sig-block">
                <p class="sig-title">Người lập phiếu</p>
                <p class="sig-name">Phạm Thị Huỳnh Như</p>
            </div>
            <div class="sig-block">
                <p class="sig-title">Người nhận</p>
                <p class="sig-name"></p>
            </div>
            <div class="sig-block">
                <p class="sig-title">Người giao</p>
                <p class="sig-name">Lê Thúy Huỳnh</p>
            </div>
        </div>
    </div>
    @endforeach

</body>

</html>