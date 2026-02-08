@php
    function docSoThanhChu($number)
    {
        $chuSo = ["không", "một", "hai", "ba", "bốn", "năm", "sáu", "bảy", "tám", "chín"];
        $docBlock = function ($number) use ($chuSo, &$docBlock) {
            $tram = floor($number / 100);
            $chuc = floor(($number % 100) / 10);
            $donvi = $number % 10;
            $res = "";
            if ($tram > 0)
                $res .= $chuSo[$tram] . " trăm ";
            else if ($res !== "")
                $res .= "không trăm ";
            if ($chuc > 1)
                $res .= $chuSo[$chuc] . " mươi ";
            else if ($chuc === 1)
                $res .= "mười ";
            else if ($tram > 0 && $donvi > 0)
                $res .= "lẻ ";
            if ($donvi === 5 && $chuc >= 1)
                $res .= "lăm";
            else if ($donvi > 1 || ($donvi === 1 && $chuc === 0))
                $res .= $chuSo[$donvi];
            else if ($donvi === 1 && $chuc > 0)
                $res .= "mốt";
            return $res;
        };
        $hangDonVi = ["", " nghìn", " triệu", " tỷ", " nghìn tỷ", " triệu tỷ"];
        if ($number == 0)
            return "Không đồng";
        $res = "";
        $i = 0;
        $num = (float) $number;
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.3;
            background: white;
        }

        .page {
            padding: 1.5cm;
            width: 210mm;
            margin: 0 auto;
            min-height: 297mm;
            position: relative;
        }

        .page-landscape {
            width: 297mm;
            min-height: 210mm;
        }

        @media print {
            body {
                background: none;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .page {
                margin: 0;
                border: none;
                width: auto;
                min-height: auto;
                page-break-after: always;
            }

            .no-print {
                display: none;
            }

            @page {
                margin: 5mm 15mm 10mm 15mm; /* Reduced top margin to 5mm */
                size: A4 portrait;
            }
            
            .page {
                padding: 0 !important; /* Remove page padding in print */
            }
            
            /* Ensure table headers repeat */
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr { page-break-inside: avoid; }
        }

        /* Common Styles */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .italic {
            font-style: italic;
        }

        .uppercase {
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th,
        td {
            border: 1px solid black;
            padding: 4px 6px;
            font-size: 11pt;
        }

        th {
            background: #4472c4 !important;
            color: white !important;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .bg-blue-header {
            background-color: #4472c4 !important;
            color: white !important;
            font-weight: bold;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .bg-subtotal {
            background-color: #fff2cc !important;
            font-weight: bold;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .bg-gold-total {
            background-color: #ffc000 !important;
            font-weight: bold;
            font-size: 14pt;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Ensure table header sticks */
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        tr { page-break-inside: avoid; }
        
        /* Reduce gap between title and table */
        .text-center { margin: 10px 0 !important; }

        /* Header info */
        .header-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .company-info {
            text-align: left;
        }

        .national-info {
            text-align: center;
        }

        /* Signatures */
        .signature-section {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .sig-block {
            text-align: center;
            flex: 1;
        }

        .sig-title {
            font-weight: bold;
            margin-bottom: 70px;
        }

        .sig-name {
            font-weight: bold;
        }

        /* Specific Table Coloring */
        .bg-blue-100 {
            background-color: #ebf8ff;
        }

        .bg-yellow-100 {
            background-color: #fef9c3;
        }

        .bg-orange-100 {
            background-color: #ffedd5;
        }
    </style>
</head>

<body>
    <!-- 1. BẢNG TỔNG (Hidden per user request) -->
    {{--
    <div class="page page-landscape">
        ... (Hidden Landscape Table) ...
    </div>
    --}}

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
                <p class="italic" style="margin-top: 5px;">Đồng Tháp, ngày {{ date('d') }} tháng {{ date('m') }} năm
                    {{ date('Y') }}
                </p>
            </div>
        </div>

        <div class="text-center" style="margin: 20px 0;">
            <p class="font-bold" style="font-size: 14pt; text-transform: uppercase;">BẢNG ĐỀ NGHỊ MUA VĂN PHÒNG PHẨM -
                VẬT TƯ TIÊU HAO</p>
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
                    <th style="width: 80px;">SỐ LƯỢNG</th>
                    <th style="width: 100px;">ĐƠN GIÁ</th>
                    <th style="width: 120px;">THÀNH TIỀN</th>
                    <th style="width: 150px;">GHI CHÚ</th>
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
                            <tr class="bg-blue-header">
                                <td colspan="7" class="font-bold text-left pl-2">{{ $category->name }}</td>
                            </tr>
                            @foreach($catProducts as $product)
                                @php 
                                    $stt++;
                                    $qty = $product->monthlyOrders->sum('quantity');
                                    $total = $qty * $product->price;
                                    // Find the first non-empty admin note from any order
                                    $noteOrder = $product->monthlyOrders->where('admin_notes', '!=', null)->where('admin_notes', '!=', '')->first();
                                    $note = $noteOrder ? $noteOrder->admin_notes : '';
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $stt }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td class="text-center">{{ $product->unit }}</td>
                                    <td class="text-right">{{ number_format($qty, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td class="text-right font-bold">{{ number_format($total, 0, ',', '.') }}</td>
                                    <td>{{ $note }}</td>
                                </tr>
                            @endforeach
                            <!-- Category Subtotal -->
                            <tr class="bg-subtotal">
                                <td colspan="5" class="text-right font-bold pr-2">CỘNG:</td>
                                <td class="text-right font-bold pr-2 text-red-600">{{ number_format($catProducts->sum(fn($p) => $p->monthlyOrders->sum('quantity') * $p->price), 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endif
                    @endif
                @endforeach
                <tr class="bg-gold-total">
                    <td colspan="5" class="text-right uppercase pr-2">TỔNG CỘNG:</td>
                    <td class="text-right pr-2">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                    <td></td>
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

    <!-- 3. PHIẾU XUẤT KHO (Hidden per user request) -->
    {{-- 
    @foreach($departmentOrders as $deptId => $data)
       ... (Hidden Department Forms) ...
    @endforeach 
    --}}

</body>

</html>