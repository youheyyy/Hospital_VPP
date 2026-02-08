@php
    function docSoThanhChu($number) {
        $chuSo = ["không", "một", "hai", "ba", "bốn", "năm", "sáu", "bảy", "tám", "chín"];
        $docBlock = function ($number) use ($chuSo, &$docBlock) {
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
    <title>Phiếu xuất kho - {{ $department->name }} - Tháng {{ $selectedMonth }}</title>
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

        @media print {
            body {
                background: none;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                size: A4 portrait;
                margin: 0; /* Removing margin hides browser headers/footers */
            }

            .page {
                margin: 20mm 15mm 20mm 15mm; /* Add margin back to content */
                border: none;
                width: auto;
                min-height: auto;
                padding: 0 !important;
                page-break-after: always;
            }
            
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr { page-break-inside: avoid; }
        }

        /* Common Styles */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .italic { font-style: italic; }
        .uppercase { text-transform: uppercase; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th, td {
            border: 1px solid black;
            padding: 4px 6px;
            font-size: 11pt;
            vertical-align: middle;
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

        /* Header info */
        .header-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .company-info p, .national-info p {
            margin: 2px 0;
            font-size: 11pt;
        }

        .national-info {
            text-align: center;
        }
        
        .title-section {
             margin: 10px 0;
             text-align: center;
        }

        /* Signatures */
        .signature-section {
            display: flex;
            justify-content: space-around;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .sig-block {
            text-align: center;
            flex: 1;
        }

        .sig-title {
            font-weight: bold;
            margin-bottom: 70px;
            text-transform: uppercase;
            font-size: 11pt;
        }

        .sig-name {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11pt;

        }
    </style>
</head>

<body>
    <div class="page">
        <div class="header-section">
            <div class="company-info">
                <p class="font-bold uppercase">CTCP BỆNH VIỆN ĐA KHOA</p>
                <p class="font-bold uppercase">TÂM TRÍ CAO LÃNH</p>
                <p class="uppercase">{{ $department->name }}</p>
            </div>
            <div class="national-info">
                <p class="font-bold uppercase">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</p>
                <p class="font-bold">Độc lập - Tự do - Hạnh phúc</p>
                <p class="italic" style="margin-top: 5px;">Đồng Tháp, ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</p>
            </div>
        </div>

        <div class="title-section">
            <p class="font-bold" style="font-size: 16pt; text-transform: uppercase;">PHIẾU DỰ TRÙ VĂN PHÒNG PHẨM - VTTH</p>
            <p class="font-bold">Tháng {{ $selectedMonth }}</p>
        </div>

        <div style="margin: 10px 0;">
             <p>Kính gửi: Ban Giám đốc, Phòng Tài chính Kế toán, Bộ phận Hỗ trợ dịch vụ.</p>
             <p>Căn cứ nhu cầu sử dụng thực tế, {{ $department->name }} đề nghị cấp các vật tư sau:</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">STT</th>
                    <th>Tên hàng</th>
                    <th style="width: 60px;">ĐVT</th>
                    <th style="width: 80px;">Số lượng</th>
                    <th style="width: 100px;">Đơn giá</th>
                    <th style="width: 120px;">Thành tiền</th>
                    <th style="width: 150px;">Ghi chú</th>
                    <th style="width: 150px;">Phản hồi</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $stt = 0; 
                    $grandTotal = 0;
                @endphp
                @foreach($orders as $categoryName => $categoryOrders)
                    @php
                        $catTotal = 0;
                        foreach($categoryOrders as $o) {
                            $catTotal += $o->quantity * $o->product->price;
                        }
                        $grandTotal += $catTotal;
                    @endphp
                    
                    <tr class="bg-blue-header">
                        <td colspan="8" class="font-bold text-left pl-2">{{ $categoryName }}</td>
                    </tr>
                    
                    @foreach($categoryOrders as $order)
                        @php 
                            $stt++;
                            $itemTotal = $order->quantity * $order->product->price;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $stt }}</td>
                            <td>{{ $order->product->name }}</td>
                            <td class="text-center">{{ $order->product->unit }}</td>
                            <td class="text-right">{{ number_format($order->quantity, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($order->product->price, 0, ',', '.') }}</td>
                            <td class="text-right font-bold">{{ number_format($itemTotal, 0, ',', '.') }}</td>
                            <td>{{ $order->notes }}</td>
                            <td>{{ $order->admin_notes }}</td>
                        </tr>
                    @endforeach
                    
                    <!-- Category Subtotal -->
                    <tr class="bg-subtotal">
                        <td colspan="5" class="text-right font-bold pr-2">Cộng:</td>
                        <td class="text-right font-bold pr-2 text-red-600">{{ number_format($catTotal, 0, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                @endforeach
                
                <!-- Grand Total -->
                <tr class="bg-gold-total">
                    <td colspan="5" class="text-right uppercase pr-2">TỔNG CỘNG:</td>
                    <td class="text-right pr-2">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 10px;">
            <p class="font-bold">Tổng số tiền bằng chữ: <span class="italic font-normal">{{ docSoThanhChu($grandTotal) }}</span></p>
        </div>

        <div class="signature-section">
            <div class="sig-block">
                <p class="sig-title">Người Lập phiếu</p>
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
</body>

</html>
