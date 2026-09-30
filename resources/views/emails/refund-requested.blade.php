<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Refund Request Diterima - Jelajahin</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #f1f5f9; font-family: 'Segoe UI', Arial, sans-serif; }
    .wrapper { max-width: 520px; margin: 40px auto; padding: 0 16px 40px; }
    .card { background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #0d1117 0%, #161b22 100%); padding: 36px 40px 32px; text-align: center; }
    .logo { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 4px; }
    .logo-icon { width: 40px; height: 40px; background: #0ea5a0; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; }
    .logo-text { color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
    .body { padding: 36px 40px; }
    .greeting { font-size: 15px; color: #64748b; margin-bottom: 6px; }
    .title { font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 16px; }
    .desc { font-size: 14px; color: #64748b; line-height: 1.7; margin-bottom: 32px; }
    .info-box { background: #f0fdfc; border: 2px solid #99f6e4; border-radius: 16px; padding: 24px; margin-bottom: 28px; }
    .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
    .info-row:last-child { border-bottom: none; }
    .info-label { font-size: 12px; color: #64748b; font-weight: 600; }
    .info-value { font-size: 14px; color: #0f172a; font-weight: 700; }
    .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center; }
    .footer p { font-size: 11px; color: #94a3b8; line-height: 1.8; }
    .footer a { color: #0ea5a0; text-decoration: none; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="card">
      <div class="header">
        <div class="logo">
          <div class="logo-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
              <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19.5 2.5c-1.5-1.5-3.5-1.5-5 0L11 6 2.8 4.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 5.2 6.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
            </svg>
          </div>
          <span class="logo-text">Jelajahin</span>
        </div>
      </div>
      <div class="body">
        <p class="greeting">Halo, <strong>{{ $name }}</strong></p>
        <h1 class="title">Refund Request Diterima</h1>
        <p class="desc">Refund request Anda telah diterima dan sedang diproses oleh tim kami.</p>
        <div class="info-box">
          <div class="info-row">
            <span class="info-label">No. Refund</span>
            <span class="info-value">{{ $refund_number }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">PNR Code</span>
            <span class="info-value">{{ $pnr_code }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Tipe Refund</span>
            <span class="info-value">{{ ucfirst($refund_type) }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Jumlah</span>
            <span class="info-value">Rp {{ number_format($amount, 0, ',', '.') }}</span>
          </div>
        </div>
      </div>
      <div class="footer">
        <p>Email ini dikirim otomatis oleh <a href="#">Jelajahin</a>.<br/>© {{ date('Y') }} Jelajahin · Semua Hak Dilindungi</p>
      </div>
    </div>
  </div>
</body>
</html>
