<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu xuất kho - {{ $department->name }}</title>
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
            padding: 1cm;
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

        .form-number {
            font-weight: bold;
            font-size: 11pt;
        }

        .form-regulation {
            font-style: italic;
            font-size: 10pt;
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
            font-size: 12pt;
            font-weight: normal;
        }

        .department-name {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            border: 1px solid black;
            padding: 6px;
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

        .total-row {
            background: #fef3c7;
            font-weight: bold;
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
                padding: 0.5cm;
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
            <div class="department">P. HỖ TRỢ DỊCH VỤ</div>
        </div>
        <div class="header-right">
            <div class="form-number">Mẫu số 02-VT</div>
            <div class="form-regulation">(Ban hành theo TT số 200/2014/TT-BTC<br>Ngày 22/12/2014 của Bộ trưởng BTC)</div>
            <div class="date">Ngày …../…./2026</div>
        </div>
    </div>

    <div class="title">
        <h1>PHIẾU XUẤT KHO VÀ BIÊN BẢN BÀN GIAO NỘI BỘ</h1>
        <h2>Ngày …../…./2026</h2>
    </div>

    <div class="department-name">KHOA DƯỢC</div>

    <table>
        <thead>
            <tr>
                <th style="width: 50px;">STT</th>
                <th>Tên hàng</th>
                <th style="width: 80px;">ĐVT</th>
                <th style="width: 100px;">Số Lượng</th>
                <th style="width: 120px;">Đơn Giá</th>
                <th style="width: 130px;">Thành Tiền</th>
            </tr>
        </thead>
        <tbody>
            @php $stt = 0; @endphp
            @foreach($orders as $categoryName => $categoryOrders)
                <tr>
                    <td colspan="6" class="category-header">{{ $categoryName }}</td>
                </tr>
                @foreach($categoryOrders as $order)
                    @php 
                        $stt++;
                        $totalPrice = $order->quantity * $order->product->price;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $stt }}</td>
                        <td>{{ $order->product->name }}</td>
                        <td class="text-center">{{ $order->product->unit }}</td>
                        <td class="text-right">{{ number_format($order->quantity, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($order->product->price, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($totalPrice, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-center">Tổng</td>
                <td class="text-right">{{ $orders->flatten()->sum('quantity') }}</td>
                <td></td>
                <td class="text-right">{{ number_format($totalAmount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signatures">
        <div class="signature-block">
            <div class="signature-title">Người Lập phiếu</div>
            <div class="signature-name">Phạm Thị Huỳnh Như</div>
        </div>
        <div class="signature-block">
            <div class="signature-title">Người nhận</div>
            <div class="signature-name"></div>
        </div>
        <div class="signature-block">
            <div class="signature-title">Người giao</div>
            <div class="signature-name">Lê Thúy Huyền</div>
        </div>
    </div>
</body>

</html>
