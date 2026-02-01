<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Phiếu Yêu Cầu - {{ $request->request_code }}</title>
    <style>
        @media print {
            @page { margin: 1cm; }
            body { margin: 0; }
            .no-print { display: none !important; }
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 13px;
            line-height: 1.6;
            color: #000;
            max-width: 21cm;
            margin: 0 auto;
            padding: 1cm;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .hospital-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
            text-align: center;
        }
        
        .info-section {
            margin: 20px 0;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
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
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 60px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            transition: all 0.2s;
        }
        
        .print-button:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ In Phiếu
    </button>
    
    <div class="header">
        <div class="hospital-name">BỆNH VIỆN ĐA KHOA</div>
        <div>{{ $request->department->department_name ?? 'N/A' }}</div>
    </div>
    
    <div class="title">PHIẾU YÊU CẦU MUA SẮM VẬT TƯ</div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Mã phiếu:</span>
            <span>{{ $request->request_code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Ngày tạo:</span>
            <span>{{ $request->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Người tạo:</span>
            <span>{{ $request->requester->full_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Khoa phòng:</span>
            <span>{{ $request->department->department_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Trạng thái:</span>
            <span>
                @php
                    $statusLabels = [
                        'draft' => 'Nháp',
                        'SUBMITTED' => 'Chờ duyệt',
                        'pending' => 'Chờ duyệt',
                        'APPROVED' => 'Đã duyệt',
                        'REJECTED' => 'Từ chối',
                        'ISSUED' => 'Đã xuất kho'
                    ];
                @endphp
                {{ $statusLabels[$request->status] ?? 'N/A' }}
            </span>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">STT</th>
                <th>Tên hàng hóa</th>
                <th style="width: 100px;">Mã VT</th>
                <th style="width: 60px;">ĐVT</th>
                <th style="width: 80px;">Số lượng</th>
                <th style="width: 100px;">Đơn giá</th>
                <th style="width: 120px;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($request->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product->product_name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->product->product_code ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->product->unit ?? 'N/A' }}</td>
                    <td class="text-right">{{ $item->quantity_requested }}</td>
                    <td class="text-right">{{ number_format($item->product->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">
                        {{ number_format($item->quantity_requested * $item->product->unit_price, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right">TỔNG CỘNG:</td>
                <td class="text-right font-bold">{{ number_format($totalAmount, 0, ',', '.') }} VNĐ</td>
            </tr>
        </tbody>
    </table>
    
    @if($request->note)
        <div style="margin-top: 20px;">
            <strong>Ghi chú:</strong> {{ $request->note }}
        </div>
    @endif
    
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-title">Người lập phiếu</div>
            <div>{{ $request->requester->full_name ?? 'N/A' }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">Trưởng khoa phê duyệt</div>
            <div>(Ký tên, đóng dấu)</div>
        </div>
    </div>
</body>
</html>
