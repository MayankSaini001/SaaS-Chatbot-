<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Welcome</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #ede9fe; font-family: Arial, Helvetica, sans-serif; padding: 40px 16px; }
  .wrap { max-width: 540px; margin: 0 auto; background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 32px rgba(109,40,217,0.10); border: 1px solid #ddd6fe; }

  /* Header */
  .header { background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%); padding: 44px 40px 36px; text-align: center; }
  .header-badge { display: inline-block; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: #fff; font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; padding: 5px 16px; border-radius: 20px; margin-bottom: 18px; }
  .header-title { font-size: 26px; font-weight: 900; color: #fff; letter-spacing: -0.5px; margin-bottom: 6px; }
  .header-sub { color: rgba(255,255,255,0.75); font-size: 12px; letter-spacing: 0.5px; }
  .accent-line { height: 3px; background: linear-gradient(90deg, #7c3aed, #6366f1); }

  /* Body */
  .body { padding: 36px 40px 32px; }
  .greeting { font-size: 21px; font-weight: 800; color: #1e1b4b; margin-bottom: 10px; }
  .intro { font-size: 14px; color: #6b7280; line-height: 1.78; margin-bottom: 30px; }

  /* Credentials */
  .section-label { font-size: 10px; font-weight: 800; color: #7c3aed; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
  .creds { border: 1.5px solid #ddd6fe; border-radius: 14px; overflow: hidden; margin-bottom: 28px; background: #faf5ff; }
  .creds-row { display: table; width: 100%; padding: 13px 18px; border-bottom: 1px solid #ede9fe; }
  .creds-row:last-child { border-bottom: none; }
  .creds-left { display: table-cell; vertical-align: middle; width: 42px; }
  .creds-right { display: table-cell; vertical-align: middle; padding-left: 4px; }

  /* Icon box — plain colored div, Gmail-safe */
  .icon-box { width: 34px; height: 34px; border-radius: 8px; text-align: center; line-height: 34px; font-size: 13px; font-weight: 900; color: #fff; }
  .icon-purple { background: #7c3aed; }
  .icon-blue   { background: #4f46e5; }
  .icon-indigo { background: #6366f1; }
  .icon-violet { background: #8b5cf6; }

  .creds-label { font-size: 10px; font-weight: 700; color: #8b5cf6; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
  .creds-value { font-size: 14px; font-weight: 700; color: #1e1b4b; word-break: break-all; }

  /* Steps */
  .steps { background: #fefce8; border: 1.5px solid #fde68a; border-radius: 14px; padding: 20px 22px; margin-bottom: 26px; }
  .steps-title { font-size: 10px; font-weight: 800; color: #92400e; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; }
  .step { display: table; width: 100%; margin-bottom: 11px; }
  .step:last-child { margin-bottom: 0; }
  .step-left { display: table-cell; vertical-align: top; width: 30px; }
  .step-right { display: table-cell; vertical-align: top; padding-left: 2px; padding-top: 3px; }
  .step-n { width: 22px; height: 22px; background: #f59e0b; color: #fff; font-size: 12px; font-weight: 800; border-radius: 50%; text-align: center; line-height: 22px; }
  .step-t { font-size: 13.5px; color: #78350f; line-height: 1.55; }
  .step-t strong { color: #92400e; }

  /* Buttons */
  .btn-login { display: block; background: linear-gradient(135deg, #7c3aed, #6366f1); color: #fff !important; text-decoration: none; text-align: center; padding: 16px; border-radius: 12px; font-size: 15px; font-weight: 800; margin-bottom: 10px; box-shadow: 0 4px 16px rgba(124,58,237,0.3); }
  .btn-plans { display: block; background: #f5f3ff; border: 2px solid #7c3aed; color: #7c3aed !important; text-decoration: none; text-align: center; padding: 14px; border-radius: 12px; font-size: 14px; font-weight: 700; margin-bottom: 26px; }

  /* Security */
  .security { background: #fff1f2; border: 1.5px solid #fecdd3; border-left: 3px solid #e11d48; border-radius: 8px; padding: 14px 16px; font-size: 13px; color: #9f1239; line-height: 1.65; }
  .security strong { color: #881337; }

  /* Footer */
  .footer { background: linear-gradient(135deg, #1e1b4b, #2e1065); padding: 22px 40px; text-align: center; }
  .footer-name { font-size: 14px; font-weight: 900; color: #fff; margin-bottom: 8px; }
  .footer-line { width: 32px; height: 1px; background: #4c1d95; margin: 0 auto 10px; }
  .footer-text { font-size: 12px; color: #6d28d9; line-height: 1.7; }
  .footer-text a { color: #a78bfa; text-decoration: none; }
</style>
</head>
<body>
<div class="wrap">

  <div class="header">
    <div class="header-badge">Account Created Successfully</div>
    <div class="header-title">{{ $appName }}</div>
    <div class="header-sub">AI-Powered Customer Support Platform</div>
  </div>
  <div class="accent-line"></div>

  <div class="body">
    <div class="greeting">Welcome, {{ $ownerName }}!</div>
    <p class="intro">Your <strong>{{ $companyName }}</strong> account has been set up successfully. Here are your login credentials — use them to access your dashboard, choose a plan, and start building your chatbot.</p>

    <div class="section-label">Your Login Details</div>
    <div class="creds">

      <div class="creds-row">
        <div class="creds-left"><div class="icon-box icon-purple">N</div></div>
        <div class="creds-right">
          <div class="creds-label">Full Name</div>
          <div class="creds-value">{{ $ownerName }}</div>
        </div>
      </div>

      <div class="creds-row">
        <div class="creds-left"><div class="icon-box icon-blue">C</div></div>
        <div class="creds-right">
          <div class="creds-label">Company</div>
          <div class="creds-value">{{ $companyName }}</div>
        </div>
      </div>

      <div class="creds-row">
        <div class="creds-left"><div class="icon-box icon-indigo">@</div></div>
        <div class="creds-right">
          <div class="creds-label">Email (Login ID)</div>
          <div class="creds-value">{{ $ownerEmail }}</div>
        </div>
      </div>

      <div class="creds-row">
        <div class="creds-left"><div class="icon-box icon-violet">P</div></div>
        <div class="creds-right">
          <div class="creds-label">Password</div>
          <div class="creds-value">{{ $ownerPassword }}</div>
        </div>
      </div>

    </div>

    <div class="steps">
      <div class="steps-title">Get Started in 3 Steps</div>
      <div class="step">
        <div class="step-left"><div class="step-n">1</div></div>
        <div class="step-right"><div class="step-t"><strong>Login</strong> using your credentials above.</div></div>
      </div>
      <div class="step">
        <div class="step-left"><div class="step-n">2</div></div>
        <div class="step-right"><div class="step-t"><strong>Purchase a Plan</strong> that fits your business needs.</div></div>
      </div>
      <div class="step">
        <div class="step-left"><div class="step-n">3</div></div>
        <div class="step-right"><div class="step-t"><strong>Add your content,</strong> customize your chatbot &amp; invite agents.</div></div>
      </div>
    </div>

    <a href="{{ $loginUrl }}" class="btn-login">Login to Dashboard &rarr;</a>
    <a href="{{ $pricingUrl }}" class="btn-plans">View Plans &amp; Pricing &rarr;</a>

    <div class="security">Please <strong>change your password</strong> after your first login. Never share your credentials with anyone.</div>
  </div>

  <div class="footer">
    <div class="footer-name">{{ $appName }}</div>
    <div class="footer-line"></div>
    <div class="footer-text">
      &copy; {{ date('Y') }} {{ $appName }}. All rights reserved.<br>
      <a href="{{ $loginUrl }}">Login</a> &nbsp;&middot;&nbsp; <a href="{{ $pricingUrl }}">Pricing</a>
    </div>
  </div>

</div>
</body>
</html>