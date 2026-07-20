<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: linear-gradient(135deg, #ede9fe 0%, #e0e7ff 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 40px 16px;
            min-height: 100vh;
        }

        .wrap {
            max-width: 540px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.18), 0 4px 16px rgba(0,0,0,0.06);
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 40px 36px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 160px; height: 160px;
            background: rgba(255,255,255,0.07);
            border-radius: 50%;
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: -30px; left: -30px;
            width: 120px; height: 120px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .header h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.3px;
        }
        .header p {
            color: rgba(255,255,255,0.78);
            font-size: 13px;
            margin-top: 6px;
            font-weight: 500;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        /* ── Body ── */
        .body { padding: 36px 40px 32px; }

        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #1a1040;
            margin-bottom: 10px;
        }

        .text {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.75;
            margin-bottom: 28px;
        }

        /* ── Credentials Box ── */
        .creds {
            background: linear-gradient(135deg, #f5f3ff 0%, #eff6ff 100%);
            border: 1.5px solid #ddd6fe;
            border-radius: 16px;
            padding: 6px 0;
            margin-bottom: 28px;
            overflow: hidden;
        }

        .creds-row {
            display: flex;
            align-items: center;
            padding: 14px 22px;
            border-bottom: 1px solid rgba(167,139,250,0.15);
            gap: 12px;
        }
        .creds-row:last-child { border-bottom: none; }

        .creds-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .creds-icon.name  { background: #ede9fe; }
        .creds-icon.email { background: #dbeafe; }
        .creds-icon.pass  { background: #fce7f3; }

        .creds-info { flex: 1; }
        .creds-label {
            font-size: 10px;
            font-weight: 700;
            color: #a78bfa;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 2px;
        }
        .creds-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e1b4b;
        }

        /* ── Button ── */
        .btn-wrap { margin-bottom: 24px; }
        .btn {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            text-align: center;
            padding: 16px 24px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.2px;
            box-shadow: 0 8px 24px rgba(102,126,234,0.40);
        }
        .btn-arrow { margin-left: 6px; }

        /* ── Warning ── */
        .warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef9c3 100%);
            border: 1.5px solid #fde68a;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 13px;
            color: #78350f;
            line-height: 1.65;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }
        .warning-icon { flex-shrink: 0; font-size: 16px; margin-top: 1px; }

        /* ── Footer ── */
        .footer {
            background: linear-gradient(135deg, #f8f7ff 0%, #f0f4ff 100%);
            padding: 18px 40px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #ede9fe;
        }
        .footer a { color: #a78bfa; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrap">

        <!-- Header -->
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>You've been added as an agent</p>
        </div>

        <!-- Body -->
        <div class="body">

            <div class="greeting">Hi {{ $agentName }}! 👋</div>
            <p class="text">
                You have been added as a support agent. Here are your login credentials.
                Please change your password after your first login.
            </p>

            <!-- Credentials -->
            <div class="creds">
                <div class="creds-row">
                    <div class="creds-icon name">👤</div>
                    <div class="creds-info">
                        <div class="creds-label">Name</div>
                        <div class="creds-value">{{ $agentName }}</div>
                    </div>
                </div>
                <div class="creds-row">
                    <div class="creds-icon email">✉️</div>
                    <div class="creds-info">
                        <div class="creds-label">Email</div>
                        <div class="creds-value">{{ $agentEmail }}</div>
                    </div>
                </div>
                <div class="creds-row">
                    <div class="creds-icon pass">🔑</div>
                    <div class="creds-info">
                        <div class="creds-label">Password</div>
                        <div class="creds-value">{{ $agentPassword }}</div>
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="btn-wrap">
                <a href="{{ $loginUrl }}" class="btn">
                    Login to Dashboard <span class="btn-arrow">→</span>
                </a>
            </div>

            <!-- Warning -->
            <div class="warning">
                <span class="warning-icon">⚠️</span>
                <span>Please change your password after logging in for the first time. Keep your credentials safe and do not share them.</span>
            </div>

        </div>

        <!-- Footer -->
        <div class="footer">
            © {{ date('Y') }} {{ config('app.name') }} &nbsp;·&nbsp; This is an automated email
        </div>

    </div>
</body>
</html>