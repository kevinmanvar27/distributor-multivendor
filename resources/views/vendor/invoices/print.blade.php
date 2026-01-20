<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .company-info h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .company-info p {
            color: #666;
            font-size: 12px;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
        }
        .invoice-info p {
            font-size: 12px;
            color: #666;
        }
        .customer-section {
            margin-bottom: 30px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .customer-section h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #333;
            color: white;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            font-size: 16px;
            background: #f5f5f5;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-draft { background: #6c757d; color: white; }
        .status-approved { background: #17a2b8; color: white; }
        .status-dispatch { background: #007bff; color: white; }
        .status-delivery { background: #ffc107; color: #333; }
        .status-delivered { background: #28a745; color: white; }
        .status-return { background: #dc3545; color: white; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print Invoice</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; margin-left: 10px;">Close</button>
    </div>

    <div class="invoice-header">
        <div class="company-info">
            <h1>{{ $vendor->business_name ?? $vendor->name ?? 'Vendor' }}</h1>
            <p>{{ $vendor->address ?? '' }}</p>
            <p>{{ $vendor->phone ?? '' }}</p>
            <p>{{ $vendor->email ?? '' }}</p>
        </div>
        <div class="invoice-info">
            <h2>INVOICE</h2>
            <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($invoiceDate)->format('d M Y') }}</p>
            <p>
                <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $invoice->status)) }}">
                    {{ $invoice->status }}
                </span>
            </p>
        </div>
    </div>

    @if($customer || $invoice->user)
    <div class="customer-section">
        <h3>Bill To:</h3>
        @if($customer)
            <p><strong>{{ $customer['name'] ?? 'N/A' }}</strong></p>
            <p>{{ $customer['email'] ?? '' }}</p>
            <p>{{ $customer['phone'] ?? '' }}</p>
            <p>{{ $customer['address'] ?? '' }}</p>
        @elseif($invoice->user)
            <p><strong>{{ $invoice->user->name }}</strong></p>
            <p>{{ $invoice->user->email }}</p>
            <p>{{ $invoice->user->phone ?? '' }}</p>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th class="text-right">Price</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cartItems as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['product_name'] ?? 'Product' }}</td>
                <td class="text-right">₹{{ number_format($item['price'] ?? 0, 2) }}</td>
                <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                <td class="text-right">₹{{ number_format($item['total'] ?? 0, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No items</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">Total:</td>
                <td class="text-right">₹{{ number_format($total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">Paid Amount:</td>
                <td class="text-right" style="color: green;">₹{{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">Pending Amount:</td>
                <td class="text-right" style="color: red;">₹{{ number_format($invoice->pending_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
    </div>
</body>
</html>