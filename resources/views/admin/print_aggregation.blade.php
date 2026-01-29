<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Xuất Kho và Biên Bản Bàn Giao Nội Bộ</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13pt;
            line-height: 1.4;
            color: #000;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .header-left {
            text-align: left;
            font-weight: bold;
        }

        .header-right {
            text-align: right;
            font-weight: bold;
        }

        .sub-header {
            text-align: center;
            font-style: italic;
            font-size: 11pt;
            margin-bottom: 20px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 16pt;
            margin: 20px 0;
            text-transform: uppercase;
        }

        .date-section {
            text-align: center;
            font-style: italic;
            margin-bottom: 10px;
        }

        .approval-section {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .supplier-header {
            background-color: #e8f5e9;
            font-weight: bold;
            text-transform: uppercase;
        }

        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-around;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            width: 23%;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .signature-subtitle {
            font-style: italic;
            font-size: 11pt;
            margin-bottom: 60px;
        }

        .signature-name {
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="position: fixed; top: 10px; right: 10px; padding: 10px 20px; background: #0d9488; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
        In Phiếu
    </button>

    <div class="header">
        <div class="header-left">
            CTY CP BV ĐA KHOA TÂM TRÍ CAO LÃNH<br>
            P. HỖ TRỢ DỊCH VỤ
        </div>
        <div class="header-right">
            Mẫu số 02-VT
        </div>
    </div>

    <div class="sub-header">
        (Ban hành theo TT số 200/2014/TT-BTC<br>
        Ngày 22/12/2014 của Bộ trưởng BTC)
    </div>

    <div class="title">
        PHIẾU XUẤT KHO VÀ BIÊN BẢN BÀN GIAO NỘI BỘ
    </div>

    <div class="date-section">
        Ngày {{ now()->format('d/m/Y') }}
    </div>

    <div class="approval-section">
        XÉT NGHIỆM
    </div>

    @foreach($aggregatedBySupplier as $supplierId => $supplierItems)
        @php 
            $supplierName = $supplierItems->first()->product->supplier->supplier_name ?? 'Chưa gán NCC';
            $totalAmount = $supplierItems->sum(function($item) { 
                return $item->total_approved * $item->product->unit_price; 
            });
        @endphp

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">STT</th>
                    <th>Tên hàng</th>
                    <th style="width: 80px;">ĐVT</th>
                    <th style="width: 100px;">Số Lượng</th>
                    <th style="width: 120px;">Đơn giá</th>
                    <th style="width: 130px;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr class="supplier-header">
                    <td colspan="6">{{ strtoupper($supplierName) }}</td>
                </tr>
                @php 
                    $groupedByProduct = $supplierItems->groupBy('product_id');
                    $stt = 1;
                @endphp
                @foreach($groupedByProduct as $prodId => $prods)
                    @php
                        $firstItem = $prods->first();
                        $prod = $firstItem->product;
                        $totalQty = $firstItem->total_approved;
                        $lineTotal = $totalQty * $prod->unit_price;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $stt++ }}</td>
                        <td>{{ $prod->product_name }}</td>
                        <td class="text-center">{{ $prod->unit }}</td>
                        <td class="text-center">{{ $totalQty }}</td>
                        <td class="text-right">{{ number_format($prod->unit_price) }}</td>
                        <td class="text-right">{{ number_format($lineTotal) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" class="text-right">Tổng cộng:</td>
                    <td class="text-right">{{ number_format($totalAmount) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-title">Người lập phiếu</div>
            <div class="signature-subtitle">(Ký, họ tên)</div>
            <div class="signature-name">{{ Auth::user()->full_name ?? '' }}</div>
        </div>

        <div class="signature-box">
            <div class="signature-title">Thủ kho</div>
            <div class="signature-subtitle">(Ký, họ tên)</div>
            <div class="signature-name"></div>
        </div>

        <div class="signature-box">
            <div class="signature-title">Kế toán trưởng</div>
            <div class="signature-subtitle">(Ký, họ tên)</div>
            <div class="signature-name"></div>
        </div>

        <div class="signature-box">
            <div class="signature-title">Giám đốc</div>
            <div class="signature-subtitle">(Ký, họ tên)</div>
            <div class="signature-name"></div>
        </div>
    </div>
</body>
</html>
