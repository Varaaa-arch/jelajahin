<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kode Verifikasi Jelajahin</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #f1f5f9; font-family: 'Segoe UI', Arial, sans-serif; }
    .wrapper { max-width: 520px; margin: 40px auto; padding: 0 16px 40px; }
    .card {
      background: #ffffff;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    }
    /* Header */
    .header {
      background: linear-gradient(135deg, #0d1117 0%, #161b22 100%);
      padding: 36px 40px 32px;
      text-align: center;
    }
    .logo {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 4px;
    }
    .logo-icon {
      width: 40px; height: 40px;
      background: #0ea5a0;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .logo-text {
      color: #ffffff;
      font-size: 22px;
      font-weight: 800;
      letter-spacing: -0.5px;
    }
    /* Body */
    .body { padding: 36px 40px; }
    .greeting {
      font-size: 15px;
      color: #64748b;
      margin-bottom: 6px;
    }
    .title {
      font-size: 22px;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 16px;
    }
    .desc {
      font-size: 14px;
      color: #64748b;
      line-height: 1.7;
      margin-bottom: 32px;
    }
    /* OTP Box */
    .otp-box {
      background: linear-gradient(135deg, #f0fdfc 0%, #e6fffe 100%);
      border: 2px solid #99f6e4;
      border-radius: 16px;
      padding: 28px;
      text-align: center;
      margin-bottom: 28px;
    }
    .otp-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 2px;
      color: #0ea5a0;
      text-transform: uppercase;
      margin-bottom: 12px;
    }
    .otp-code {
      font-size: 48px;
      font-weight: 900;
      letter-spacing: 10px;
      color: #0f172a;
      font-family: 'Courier New', monospace;
      line-height: 1;
    }
    .otp-digit { display: inline-block; }
    .otp-timer {
      margin-top: 12px;
      font-size: 12px;
      color: #94a3b8;
    }
    .otp-timer strong { color: #0ea5a0; }
    /* Warning */
    .warning {
      background: #fff7ed;
      border-left: 3px solid #f97316;
      border-radius: 8px;
      padding: 12px 16px;
      margin-bottom: 24px;
    }
    .warning p {
      font-size: 12px;
      color: #92400e;
      line-height: 1.6;
    }
    /* Footer */
    .footer {
      background: #f8fafc;
      border-top: 1px solid #e2e8f0;
      padding: 24px 40px;
      text-align: center;
    }
    .footer p {
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

      <!-- Header -->
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

      <!-- Body -->
      <div class="body">
        <p class="greeting">Halo, <strong>{{ $name }}</strong> 👋</p>
        <h1 class="title">Verifikasi akun Anda</h1>
        <p class="desc">
          Terima kasih telah mendaftar di Jelajahin! Masukkan kode verifikasi
          di bawah ini untuk mengaktifkan akun Anda. Kode berlaku selama
          <strong>10 menit</strong>.
        </p>

        <!-- OTP Code Box -->
        <div class="otp-box">
          <p class="otp-label">Kode Verifikasi</p>
          <div class="otp-code">
            @foreach(str_split($code) as $digit)
              <span class="otp-digit">{{ $digit }}</span>
            @endforeach
          </div>
          <p class="otp-timer">Berlaku hingga <strong>10 menit</strong> dari sekarang</p>
        </div>

        <!-- Warning -->
        <div class="warning">
          <p>
            🔒 <strong>Jangan bagikan kode ini kepada siapapun.</strong>
            Tim Jelajahin tidak akan pernah meminta kode verifikasi Anda.
            Jika Anda tidak mendaftar, abaikan email ini.
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="footer">
        <p>
          Email ini dikirim otomatis oleh <a href="#">Jelajahin</a>.<br/>
          © {{ date('Y') }} Jelajahin · Semua Hak Dilindungi
        </p>
      </div>

    </div>
  </div>
</body>
</html>
