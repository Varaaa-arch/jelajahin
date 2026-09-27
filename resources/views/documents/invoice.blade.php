<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; margin: 0; padding: 24px; color: #1f2937; font-size: 12px; }
        .invoice { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .header { background: #0d1117; color: #ffffff; padding: 24px 28px; }
        .header h1 { margin: 0; font-size: 26px; letter-spacing: 2px; }
        .header .inv-no { color: #14c4be; font-size: 12px; margin-top: 4px; }
        .content { padding: 24px 28px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .info-table td { vertical-align: top; padding: 4px 0; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; font-weight: bold; }
        .value { font-size: 12px; color: #111827; margin-top: 2px; }
        .section-title { font-weight: bold; font-size: 11px; letter-spacing: 1px; margin: 18px 0 6px 0; border-bottom: 2px solid #0ea5a0; padding-bottom: 4px; color: #0d1117; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.items th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; text-align: left; padding: 8px 10px; }
        table.items td { font-size: 12px; padding: 8px 10px; border-bottom: 1px solid #f3f4f6; }
        table.items td.num { text-align: right; white-space: nowrap; }
        .summary { margin-top: 14px; }
        .summary table { width: 60%; margin-left: 40%; border-collapse: collapse; }
        .summary td { font-size: 12px; padding: 5px 0; }
        .summary td.num { text-align: right; white-space: nowrap; }
        .total-row td { font-size: 16px; font-weight: bold; color: #0ea5a0; border-top: 2px solid #0ea5a0; padding-top: 8px; }
        .paid { display: inline-block; background: #0ea5a0; color: #fff; font-size: 10px; font-weight: bold; padding: 3px 12px; border-radius: 20px; letter-spacing: 1px; }
        .unpaid { display: inline-block; background: #f59e0b; color: #fff; font-size: 10px; font-weight: bold; padding: 3px 12px; border-radius: 20px; letter-spacing: 1px; }
        .footer { margin-top: 24px; padding: 14px 28px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <table width="100%">
                <tr>
                    <td>
                        <h1>INVOICE</h1>
                        <div class="inv-no">{{ $invoice_number }} &nbsp;•&nbsp; {{ $invoice_date }}</div>
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        @if(strtolower($invoice_status) === 'paid')
                            <span class="paid">LUNAS</span>
                        @elseif(strtolower($invoice_status) === 'issued')
                            <span class="unpaid">BELUM LUNAS</span>
                        @else
                            <span class="unpaid">{{ strtoupper($invoice_status) }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="content">
            <table class="info-table">
                <tr>
                    <td width="50%">
                        <div class="label">Ditagihkan Ke (PNR)</div>
                        <div class="value" style="font-family: Courier, monospace; font-weight: bold; letter-spacing: 1px;">{{ $pnr_code }}</div>
                        <div class="value">{{ $passenger_count }} Penumpang</div>
                    </td>
                    <td width="50%" style="text-align: right;">
                        <div class="label">Penerbangan</div>
                        <div class="value">{{ $flight_number }}</div>
                        <div class="value">{{ $origin_code }} - {{ $destination_code }} • {{ $departure_date }}</div>
                    </td>
                </tr>
            </table>

            <div class="section-title">PENUMPANG</div>
            <table class="items">
                <tr><th width="8%">No</th><th>Nama</th><th width="20%">Kursi</th></tr>
                @foreach($passengers as $p)
                <tr><td>{{ $p['no'] }}</td><td>{{ $p['name'] }}</td><td>{{ $p['seat'] }}</td></tr>
                @endforeach
            </table>

            <div class="section-title">RINCIAN BIAYA</div>
            <table class="items">
                <tr><th>Deskripsi</th><th width="30%" style="text-align: right;">Jumlah</th></tr>
                <tr><td>Tiket dasar ({{ $passenger_count }} pax)</td><td class="num">Rp {{ number_format($base_amount, 0, ',', '.') }}</td></tr>
                <tr><td>Pajak &amp; biaya (10%)</td><td class="num">Rp {{ number_format($tax_amount, 0, ',', '.') }}</td></tr>
                @if($addons_amount > 0)
                <tr><td>Layanan tambahan</td><td class="num">Rp {{ number_format($addons_amount, 0, ',', '.') }}</td></tr>
                @endif
                @if($discount_amount > 0)
                <tr><td>Diskon</td><td class="num">-Rp {{ number_format($discount_amount, 0, ',', '.') }}</td></tr>
                @endif
            </table>

            <div class="summary">
                <table>
                    <tr class="total-row"><td>TOTAL</td><td class="num">Rp {{ number_format($total_amount, 0, ',', '.') }}</td></tr>
                </table>
            </div>
        </div>

        <div class="footer">
            Terima kasih telah memesan di Jelajahin Airlines.<br>
            Dibuat {{ date('d M Y H:i') }}
        </div>
    </div>
</body>
</html>
