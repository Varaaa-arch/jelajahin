<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .section { background: white; padding: 15px; margin: 10px 0; border: 1px solid #ddd; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; }
        .label { font-weight: bold; }
        .total { font-size: 18px; color: #667eea; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice</h1>
            <p>#{{ $invoice->invoice_number }}</p>
        </div>
        <div class="content">
            <p>Dear Customer,</p>
            <p>Thank you for your booking with Jelajahin Airlines. Please find your invoice details below.</p>

            <div class="section">
                <div class="row">
                    <div>
                        <strong>Invoice Number:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}<br>
                        <strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}
                    </div>
                    <div>
                        <strong>Booking Reference:</strong> {{ $booking->pnr_code }}<br>
                        <strong>Flight Number:</strong> {{ $booking->flight->flight_number }}<br>
                        <strong>Departure:</strong> {{ $booking->flight->departure_date->format('d M Y') }}
                    </div>
                </div>
            </div>

            <div class="section">
                <strong>Passengers:</strong>
                <table>
                    <tr>
                        <th>Passenger Name</th>
                    </tr>
                    @foreach($booking->passengers as $passenger)
                    <tr>
                        <td>{{ $passenger->title }}. {{ $passenger->first_name }} {{ $passenger->last_name }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>

            <div class="section">
                <strong>Charges Summary:</strong>
                <div class="row">
                    <span>Base Amount:</span>
                    <span>Rp {{ number_format($invoice->base_amount, 0, ',', '.') }}</span>
                </div>
                <div class="row">
                    <span>Tax (10%):</span>
                    <span>Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="row">
                    <span>Discount:</span>
                    <span>-Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <hr>
                <div class="row">
                    <span class="label">Total Amount Due:</span>
                    <span class="total">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="section">
                <strong>Payment Instructions:</strong>
                <p>Please arrange payment before the due date. For payment methods and bank details, please refer to the attached PDF invoice or visit our website.</p>
            </div>

            <p>If you have any questions about this invoice, please contact our customer service team.</p>
            <p>Thank you for your business!<br><strong>Jelajahin Airlines Team</strong></p>
        </div>
    </div>
</body>
</html>
