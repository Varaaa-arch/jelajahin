<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; margin: 0; padding: 24px; color: #1f2937; font-size: 12px; }
        .ticket { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .header { background: #0d1117; color: #ffffff; padding: 22px 28px; }
        .header .brand { font-size: 11px; letter-spacing: 3px; color: #14c4be; font-weight: bold; }
        .header .title { font-size: 22px; font-weight: bold; margin: 4px 0 0 0; }
        .header .meta { font-size: 11px; color: #9ca3af; margin-top: 6px; }
        .badge { background: #0ea5a0; color: #ffffff; font-size: 10px; font-weight: bold; padding: 4px 12px; border-radius: 20px; letter-spacing: 1px; }
        .body { padding: 24px 28px; }
        .route-table { width: 100%; border-collapse: collapse; margin: 8px 0 4px 0; }
        .route-table td { vertical-align: middle; }
        .airport-code { font-size: 30px; font-weight: bold; color: #0d1117; }
        .airport-city { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .airport-time { font-size: 13px; font-weight: bold; color: #0ea5a0; margin-top: 4px; }
        .plane { text-align: center; font-size: 11px; color: #9ca3af; letter-spacing: 2px; }
        .flight-no { text-align: center; font-size: 11px; color: #6b7280; margin-top: 6px; }
        .divider { border-top: 1px dashed #d1d5db; margin: 18px 0; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; font-weight: bold; }
        .value { font-size: 13px; color: #111827; margin-top: 3px; }
        table.pax { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.pax th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; text-align: left; padding: 8px 10px; }
        table.pax td { font-size: 12px; padding: 8px 10px; border-bottom: 1px solid #f3f4f6; }
        .notice { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 10px 14px; font-size: 11px; margin-top: 18px; border-radius: 0 6px 6px 0; }
        .footer { padding: 14px 28px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
        .barcode { margin-top: 10px; }
        .barcode td { height: 34px; background: #111827; }
        .barcode td.gap { background: transparent; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <table width="100%">
                <tr>
                    <td>
                        <div class="brand">JELAJAHIN</div>
                        <div class="title">E-Tiket Penerbangan</div>
                        <div class="meta">{{ $airline }} &nbsp;•&nbsp; Diterbitkan {{ $booking_date }}</div>
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        <span class="badge">CONFIRMED</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="body">
            <table class="route-table">
                <tr>
                    <td width="35%">
                        <div class="airport-code">{{ $origin_code }}</div>
                        <div class="airport-city">{{ $origin_city }}</div>
                        <div class="airport-time">{{ $departure_time }} • {{ $departure_date }}</div>
                    </td>
                    <td width="30%">
                        <div class="plane">- - - ✈ - - -</div>
                        <div class="flight-no">{{ $flight_number }}</div>
                    </td>
                    <td width="35%" style="text-align: right;">
                        <div class="airport-code">{{ $destination_code }}</div>
                        <div class="airport-city">{{ $destination_city }}</div>
                        <div class="airport-time">{{ $arrival_time }} • {{ $departure_date }}</div>
                    </td>
                </tr>
            </table>

            <div class="divider"></div>

            <table width="100%">
                <tr>
                    <td width="50%">
                        <div class="label">Kode Pemesanan (PNR)</div>
                        <div class="value" style="font-family: Courier, monospace; font-weight: bold; font-size: 16px; letter-spacing: 2px;">{{ $pnr_code }}</div>
                    </td>
                    <td width="50%">
                        <div class="label">Jumlah Penumpang</div>
                        <div class="value">{{ $passenger_count }} Orang</div>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 16px;">
                <div class="label">Daftar Penumpang</div>
                <table class="pax">
                    <tr>
                        <th width="8%">No</th>
                        <th>Nama Penumpang</th>
                        <th width="20%">Kursi</th>
                    </tr>
                    @foreach($passengers as $p)
                    <tr>
                        <td>{{ $p['no'] }}</td>
                        <td>{{ $p['name'] }}</td>
                        <td>{{ $p['seat'] }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>

            <div class="notice">
                Tiba di bandara minimal 2 jam sebelum keberangkatan. Tunjukkan kode PNR beserta identitas diri saat check-in.
            </div>

            <table class="barcode" width="100%">
                <tr>
                    @foreach(str_split(md5($pnr_code)) as $i => $ch)
                        @if($i % 2 === 0)
                            <td width="{{ 2 + (hexdec($ch) % 4) }}"></td>
                            <td class="gap" width="3"></td>
                        @endif
                    @endforeach
                </tr>
            </table>
            <div style="text-align: center; font-size: 10px; color: #9ca3af; margin-top: 4px; letter-spacing: 3px;">{{ $pnr_code }}</div>
        </div>

        <div class="footer">
            Dokumen perjalanan yang sah. Simpan e-tiket ini untuk perjalanan Anda.<br>
            Dibuat {{ date('d M Y H:i') }} • Jelajahin Airlines
        </div>
    </div>
</body>
</html>
