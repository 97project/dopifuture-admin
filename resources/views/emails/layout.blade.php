<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>{{ $subject ?? 'DopiFuture' }}</title>
<!--[if mso]>
<style>table,td,th{font-family:Arial,sans-serif!important;}</style>
<![endif]-->
<style>
  /* Reset */
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { margin: 0; padding: 0; width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
  table { border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
  img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; display: block; }

  /* Base */
  body, .body-wrap { background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }

  /* Card */
  .email-card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }

  /* Header — light */
  .email-header { background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 50%, #f5f0ff 100%); padding: 36px 24px; text-align: center; border-bottom: 1px solid #e2e8f0; }

  /* Content */
  .email-body { padding: 36px 32px; }
  .email-body h1 { font-size: 22px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0; line-height: 1.3; }
  .email-body .subtitle { font-size: 14px; color: #64748b; margin: 0 0 24px 0; line-height: 1.6; }
  .email-body p { font-size: 14px; color: #334155; line-height: 1.7; margin: 0 0 14px 0; }

  /* Info Card */
  .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0; }
  .info-card .label { font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 3px 0; }
  .info-card .value { font-size: 15px; font-weight: 600; color: #1e293b; margin: 0 0 14px 0; }
  .info-card .value:last-child { margin-bottom: 0; }
  .info-card .value.password { font-family: 'Courier New', Courier, monospace; background: #0f172a; color: #38bdf8; padding: 8px 14px; border-radius: 8px; display: inline-block; font-size: 16px; letter-spacing: 2px; }

  /* CTA */
  .cta-wrap { text-align: center; margin: 28px 0; }
  .cta-btn { display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; padding: 13px 40px; border-radius: 50px; mso-padding-alt: 13px 40px; }

  /* Warning */
  .warning { background: #fefce8; border-left: 4px solid #eab308; border-radius: 0 8px 8px 0; padding: 12px 16px; margin: 20px 0; font-size: 13px; color: #854d0e; line-height: 1.5; }

  /* Footer */
  .email-footer { background: #f8fafc; padding: 24px 32px; text-align: center; border-top: 1px solid #e2e8f0; }
  .email-footer p { font-size: 11px; color: #94a3b8; margin: 0 0 4px 0; line-height: 1.6; }
  .email-footer a { color: #3b82f6; text-decoration: none; }

  /* Responsive */
  @media only screen and (max-width: 620px) {
    .outer-table { width: 100% !important; }
    .email-card { border-radius: 0 !important; }
    .email-header { padding: 24px 16px !important; }
    .email-body { padding: 24px 20px !important; }
    .email-footer { padding: 20px 16px !important; }
    .email-body h1 { font-size: 20px !important; }
    .cta-btn { padding: 12px 28px !important; font-size: 14px !important; }
    .stat-cell { display: block !important; width: 100% !important; margin-bottom: 8px !important; }
    .stat-spacer { display: none !important; }
  }
</style>
</head>
<body>
<div class="body-wrap" style="background-color: #f1f5f9; padding: 32px 0;">
<!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
<table role="presentation" class="outer-table" width="600" align="center" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; margin: 0 auto;">
  <tr>
    <td style="padding: 0 12px;">
      <div class="email-card" style="background: #ffffff; border-radius: 16px; overflow: hidden;">

        {{-- Header — Light with logos --}}
        <div class="email-header" style="background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 50%, #f5f0ff 100%); padding: 36px 24px; text-align: center; border-bottom: 1px solid #e2e8f0;">
          <img src="{{ $message->embed(public_path('images/dopifuture-logo-gorsel.png')) }}" alt="DopiFuture" width="64" height="64" style="height: 64px; width: 64px; margin: 0 auto 10px auto; display: block;">
          <img src="{{ $message->embed(public_path('images/dopifuture-logo-yazi.png')) }}" alt="dopifuture" height="20" style="height: 20px; margin: 0 auto; display: block;">
        </div>

        {{-- Content --}}
        <div class="email-body" style="padding: 36px 32px;">
          @yield('content')
        </div>

        {{-- Footer --}}
        <div class="email-footer" style="background: #f8fafc; padding: 24px 32px; text-align: center; border-top: 1px solid #e2e8f0;">
          <p style="font-size: 11px; color: #94a3b8; margin: 0 0 4px 0;">© {{ date('Y') }} DopiFuture — {{ __('mail.footer_tagline', [], $locale ?? 'en') }}</p>
          <p style="font-size: 11px; color: #94a3b8; margin: 0;"><a href="https://dopifuture.97.team" style="color: #3b82f6; text-decoration: none;">dopifuture.97.team</a></p>
        </div>

      </div>
    </td>
  </tr>
</table>
<!--[if mso]></td></tr></table><![endif]-->
</div>
</body>
</html>
