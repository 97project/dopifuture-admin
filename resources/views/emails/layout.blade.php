<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subject ?? 'DopiFuture' }}</title>
<style>
  /* Reset */
  body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
  table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
  img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
  body { margin: 0; padding: 0; width: 100%; background-color: #f0f4f8; font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }

  /* Container */
  .wrapper { width: 100%; background: linear-gradient(135deg, #0B1220 0%, #1a2744 50%, #0B1220 100%); padding: 40px 0; }
  .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }

  /* Header */
  .header { background: linear-gradient(135deg, #0B1220 0%, #1e3a5f 100%); padding: 32px 40px; text-align: center; }
  .header img.logo-icon { height: 56px; margin-bottom: 8px; }
  .header img.logo-text { height: 22px; }

  /* Body */
  .content { padding: 40px; }
  .content h1 { font-size: 24px; font-weight: 700; color: #0B1220; margin: 0 0 8px 0; }
  .content .subtitle { font-size: 15px; color: #64748b; margin: 0 0 28px 0; line-height: 1.5; }
  .content p { font-size: 15px; color: #334155; line-height: 1.7; margin: 0 0 16px 0; }

  /* Info Card */
  .info-card { background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%); border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin: 24px 0; }
  .info-card .label { font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 4px 0; }
  .info-card .value { font-size: 16px; font-weight: 600; color: #1e293b; margin: 0 0 16px 0; }
  .info-card .value:last-child { margin-bottom: 0; }
  .info-card .value.password { font-family: 'Courier New', monospace; background: #0B1220; color: #06B6D4; padding: 8px 16px; border-radius: 8px; display: inline-block; font-size: 18px; letter-spacing: 2px; }

  /* Stats Row */
  .stats-row { display: flex; gap: 12px; margin: 24px 0; }
  .stat-box { flex: 1; background: linear-gradient(135deg, #06B6D4 0%, #3B82F6 100%); border-radius: 12px; padding: 20px; text-align: center; }
  .stat-box .stat-number { font-size: 28px; font-weight: 800; color: #ffffff; margin: 0; }
  .stat-box .stat-label { font-size: 12px; color: rgba(255,255,255,0.8); margin: 4px 0 0 0; text-transform: uppercase; letter-spacing: 0.5px; }

  /* CTA Button */
  .cta-wrap { text-align: center; margin: 32px 0; }
  .cta-btn { display: inline-block; background: linear-gradient(135deg, #06B6D4 0%, #3B82F6 100%); color: #ffffff !important; font-size: 16px; font-weight: 700; text-decoration: none; padding: 14px 48px; border-radius: 50px; box-shadow: 0 4px 15px rgba(6,182,212,0.4); }

  /* Warning */
  .warning { background: #fefce8; border-left: 4px solid #eab308; border-radius: 0 8px 8px 0; padding: 14px 18px; margin: 24px 0; font-size: 13px; color: #854d0e; line-height: 1.5; }

  /* Divider */
  .divider { height: 1px; background: linear-gradient(90deg, transparent, #e2e8f0, transparent); margin: 28px 0; }

  /* Footer */
  .footer { background: #f8fafc; padding: 28px 40px; text-align: center; border-top: 1px solid #e2e8f0; }
  .footer p { font-size: 12px; color: #94a3b8; margin: 0 0 4px 0; line-height: 1.6; }
  .footer a { color: #06B6D4; text-decoration: none; }

  /* Responsive */
  @media (max-width: 640px) {
    .card { margin: 0 12px; border-radius: 12px; }
    .header { padding: 24px 20px; }
    .content { padding: 24px 20px; }
    .footer { padding: 20px; }
    .content h1 { font-size: 20px; }
  }
</style>
</head>
<body>
@php $prodUrl = 'https://dopifuture.97.team'; @endphp
<div class="wrapper">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center">
        <div class="card">
          {{-- Header --}}
          <div class="header">
            <img src="{{ $message->embed(public_path('images/dopifuture-logo-gorsel.png')) }}" alt="DopiFuture" class="logo-icon"><br>
            <img src="{{ $message->embed(public_path('images/dopifuture-logo-yazi.png')) }}" alt="dopifuture" class="logo-text">
          </div>

          {{-- Content --}}
          <div class="content">
            @yield('content')
          </div>

          {{-- Footer --}}
          <div class="footer">
            <p>© {{ date('Y') }} DopiFuture — {{ __('mail.footer_tagline', [], $locale ?? 'en') }}</p>
            <p><a href="{{ $prodUrl }}">dopifuture.97.team</a></p>
          </div>
        </div>
      </td>
    </tr>
  </table>
</div>
</body>
</html>
