<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password — Jelajahin</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      background: #f1f5f9;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
    }
    .wrapper {
      max-width: 480px;
      margin: 40px auto;
      padding: 0 16px 48px;
    }
    .card {
      background: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    /* Header */
    .header {
      background: #0f172a;
      padding: 28px 40px;
      text-align: center;
    }
    .header img {
      height: 36px;
      width: auto;
      filter: invert(1) brightness(2);
    }

    /* Body */
    .body {
      padding: 36px 40px 32px;
    }
    .title {
      font-size: 20px;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 8px;
    }
    .subtitle {
      font-size: 14px;
      color: #64748b;
      line-height: 1.6;
      margin-bottom: 32px;
    }

    /* OTP digits */
    .otp-label {
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 1.5px;
      color: #94a3b8;
      text-transform: uppercase;
      text-align: center;
      margin-bottom: 14px;
    }
    .digits {
      display: table;
      margin: 0 auto 8px;
      border-collapse: separate;
      border-spacing: 6px 0;
    }
    .digits td { display: table-cell; vertical-align: middle; }
    .digit-box {
      display: inline-block;
      width: 52px;
      height: 64px;
      line-height: 64px;
      text-align: center;
      background: #fffbf8;
      border: 1.5px solid #fed7aa;
      border-bottom: 3px solid #f97316;
      border-radius: 10px;
      font-size: 30px;
      font-weight: 800;
      color: #0f172a;
      font-family: 'Courier New', monospace;
    }
    .digit-sep {
      display: inline-block;
      font-size: 22px;
      color: #cbd5e1;
      padding: 0 2px;
      vertical-align: middle;
    }
    .otp-expire {
      text-align: center;
      font-size: 12px;
      color: #94a3b8;
      margin-top: 10px;
      margin-bottom: 28px;
    }
    .otp-expire strong { color: #f97316; }

    /* Warning */
    .warning {
      background: #fff1f2;
      border: 1px solid #fecdd3;
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 12.5px;
      color: #9f1239;
      line-height: 1.6;
    }

    /* Footer */
    .footer {
      background: #f8fafc;
      border-top: 1px solid #e2e8f0;
      padding: 18px 40px;
      text-align: center;
      font-size: 11px;
      color: #94a3b8;
      line-height: 1.8;
    }
    .footer a { color: #0ea5a0; text-decoration: none; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="card">

      <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Jelajahin" />
      </div>

      <div class="body">
        <h1 class="title">Reset Password</h1>
        <p class="subtitle">
          Halo <strong>{{ $name }}</strong>, gunakan kode di bawah untuk mengatur ulang password akun Jelajahin Anda.
        </p>

        <p class="otp-label">Kode Reset Password</p>

        <table class="digits" role="presentation" cellspacing="0" cellpadding="0">
          <tr>
            @php $digits = str_split($code); @endphp
            @foreach($digits as $i => $digit)
              <td><span class="digit-box">{{ $digit }}</span></td>
              @if($i === 2 && count($digits) === 6)
                <td><span class="digit-sep">·</span></td>
              @endif
            @endforeach
          </tr>
        </table>

        <p class="otp-expire">Berlaku selama <strong>10 menit</strong></p>

        <div class="warning">
          🔒 <strong>Jika bukan Anda yang meminta ini, abaikan email ini.</strong> Password Anda tidak akan berubah. Tim Jelajahin tidak pernah meminta kode ini.
        </div>
      </div>

      <div class="footer">
        &copy; {{ date('Y') }} Jelajahin &middot; <a href="#">Hubungi Kami</a>
      </div>

    </div>
  </div>
</body>
</html>
