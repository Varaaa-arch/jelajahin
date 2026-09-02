<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .invoice { max-width: 800px; background: white; }
        .header { background: #667eea; color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 32px; }
        .header p { margin: 5px 0; }
        .content { padding: 30px; }
        .row { display: flex; justify-content: space-between; margin: 20px 0; }
        .col { flex: 1; }
        .section-title { font-weight: bold; font-size: 14px; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #667eea; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #f0f0f0; padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .summary { background: #f9f9f9; padding: 15px; margin: 20px 0; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; }
        .total-row { font-size: 18px; font-weight: bold; color: #667eea; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <h1>INVOICE</h1>
            <p>{{ $invoice_number }}</p>
        </div>

        <div class="content">
            <!-- Invoice Details -->
            <div class="row">
                <div class="col">
                    <div class="section-title">INVOICE TO</div>
                    <p>Booking Reference: <strong>{{ $pnr_code }}</strong></p>
                    <p>Invoice Date: <strong>{{ $invoice_date }}</strong></p>
                    <p>Due Date: <strong>{{ $due_date }}</strong></p>
                </div>
                <div class="col" style="text-align: right;">
                    <div class="section-title">FLIGHT DETAILS</div>
                    <p>Flight: <strong>{{ $flight_number }}</strong></p>
                    <p>Date: <strong>{{ $departure_date }}</strong></p>
                    <p>Passengers: <strong>{{ $passenger_count }}</strong></p>
                </div>
            </div>

            <!-- Passengers -->
            <div class="section-title">PASSENGERS</div>
            <table>
                <tr>
                    <th>Passenger Name</th>
                </tr>
                @foreach($passengers as $passenger)
                <tr>
                    <td>{{ $passenger }}</td>
                </tr>
                @endforeach
            </table>

            <!-- Charges -->
            <div class="section-title">CHARGES</div>
            <div class="summary">
                <div class="summary-row">
                    <span>Base Amount ({{ $passenger_count }} pax)</span>
                    <span>Rp {{ number_format($base_amount, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <span>Tax (10%)</span>
                    <span>Rp {{ number_format($tax_amount, 0, ',', '.') }}</span>
                </div>
                @if($discount_amount > 0)
                <div class="summary-row">
                    <span>Discount</span>
                    <span>-Rp {{ number_format($discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="summary-row total-row">
                    <span>TOTAL AMOUNT DUE</span>
                    <span>Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>Thank you for your booking with Jelajahin Airlines!</p>
                <p>This invoice is valid until {{ $due_date }}</p>
                <p>Generated on {{ date('d M Y H:i:s') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
