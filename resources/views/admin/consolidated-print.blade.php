<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng đề nghị mua VPP - Tháng {{ str_replace('/', ' năm ', $selectedMonth) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13pt;
            line-height: 1.5;
            padding: 2cm;
            background: white;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .header-left {
            flex: 1;
        }

        .header-right {
            flex: 1;
            text-align: center;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        .company-name {
            font-weight: bold;
            font-size: 11pt;
            text-transform: uppercase;
        }

        .department {
            font-size: 11pt;
        }

        .government {
            font-weight: bold;
            font-size: 11pt;
            text-transform: uppercase;
        }

        .slogan {
            font-weight: bold;
            font-size: 11pt;
        }

        .date {
            font-style: italic;
            font-size: 10pt;
            margin-top: 3px;
        }

        .title {
            text-align: center;
            margin: 15px 0 10px 0;
            page-break-inside: avoid;
        }

        .title h1 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .title h2 {
            font-size: 13pt;
            font-weight: bold;
        }

        .content {
            margin: 20px 0;
            text-indent: 1cm;
        }

        .total-section {
            margin: 15px 0;
            font-weight: bold;
        }

        .total-amount {
            font-style: italic;
            font-weight: normal;
            margin-left: 1cm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }

        .category-header {
            background: #3b82f6;
            color: white;
            font-weight: bold;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .signatures {
            display: flex;
            justify-content: space-around;
            margin-top: 40px;
            text-align: center;
            page-break-inside: avoid;
        }

        .signature-block {
            flex: 1;
            page-break-inside: avoid;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 80px;
        }

        .signature-name {
            font-weight: bold;
        }

        @media print {
            body {
                padding: 1cm;
            }

            @page {
                size: A4;
                margin: 1cm;
            }

            .header {
                page-break-inside: avoid;
                page-break-after: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <div class="company-name">CTCP BỆNH VIỆN ĐA KHOA<br>TÂM TRÍ CAO LÃNH</div>
            <div class="department">Bộ phận hỗ trợ dịch vụ</div>
        </div>
        <div class="header-right">
            <div class="government">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
            <div class="slogan">Độc lập - Tự do - Hạnh phúc</div>
            <div class="date">Đồng Tháp, ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</div>
        </div>
    </div>

    <div class="title">
        <h1>BẢNG ĐỀ NGHỊ MUA VĂN PHÒNG PHẨM - VẬT TƯ TIÊU HAO (BỆNH VIỆN)</h1>
        <h2>Tháng {{ str_replace('/', '/', $selectedMonth) }}</h2>
    </div>

    <div class="content">
        <p>- Căn cứ vào tình hình hoạt động thực tế tại đơn vị;</p>
        <p>- Căn cứ đề nghị các khoa/phòng tháng {{ str_replace('/', ' năm ', $selectedMonth) }} về thực tế nhu cầu sử
            dụng văn phòng phẩm vật tư tiêu hao hàng tháng trong phục vụ hoạt động chuyên môn của bệnh viện;</p>
        <p>Nay Bộ phận hỗ trợ dịch vụ kính trình Ban Giám Đốc phê duyệt mua VPP-VTTH tháng
            {{ str_replace('/', '/', $selectedMonth) }}.
        </p>
    </div>

    <div class="total-section">
        Tổng số tiền: {{ number_format($grandTotal, 0, ',', '.') }} đ
        <div class="total-amount">Số tiền bằng chữ: Mười ba triệu không trăm bốn mươi mốt nghìn bảy trăm năm mươi đồng
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">STT</th>
                <th>TÊN VPP - VTTH</th>
                <th style="width: 80px;">ĐVT</th>
                <th style="width: 100px;">SỐ LƯỢNG</th>
                <th style="width: 120px;">ĐƠN GIÁ</th>
                <th style="width: 130px;">THÀNH TIỀN</th>
            </tr>
        </thead>
        <tbody>
            @php $stt = 0; @endphp
            @foreach($categories as $category)
                @if(isset($products[$category->id]) && $products[$category->id]->count() > 0)
                    <tr>
                        <td colspan="6" class="category-header">{{ $category->name }}</td>
                    </tr>
                    @foreach($products[$category->id] as $product)
                        @php
                            $totalQuantity = $product->monthlyOrders->sum('quantity');
                            $totalPrice = $totalQuantity * $product->price;
                        @endphp
                        @if($totalQuantity > 0)
                            @php $stt++; @endphp
                            <tr>
                                <td class="text-center">{{ $stt }}</td>
                                <td>{{ $product->name }}</td>
                                <td class="text-center">{{ $product->unit }}</td>
                                <td class="text-center">{{ number_format($totalQuantity, 1, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($product->price, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($totalPrice, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endif
            @endforeach
            <tr style="background: #fbbf24; font-weight: bold;">
                <td colspan="5" class="text-center">TỔNG CỘNG (VNĐ)</td>
                <td class="text-right">{{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signatures">
        <div class="signature-block">
            <div class="signature-title">BP.HTDV</div>
            <div class="signature-name">Nguyễn Thị Thùy Trang</div>
        </div>
        <div class="signature-block">
            <div class="signature-title">TRƯỞNG PHÒNG TCKT</div>
            <div class="signature-name">Nguyễn Thị Thúy Huỳnh</div>
        </div>
        <div class="signature-block">
            <div class="signature-title">BAN GIÁM ĐỐC</div>
            <div class="signature-name">Huỳnh Thị Nguyệt</div>
        </div>
    </div>
</body>

</html>