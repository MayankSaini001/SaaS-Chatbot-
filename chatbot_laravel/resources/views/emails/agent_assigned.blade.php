<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Chat Assigned</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrap { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 36px 40px; text-align: center; }
        .header h1 { color: #fff; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }
        .header p { color: rgba(255,255,255,0.8); font-size: 14px; margin-top: 6px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 18px; font-weight: 600; color: #0f172a; margin-bottom: 12px; }
        .text { font-size: 14px; color: #64748b; line-height: 1.7; margin-bottom: 24px; }
        .info-box { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; }
        .info-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-size: 14px; font-weight: 600; color: #0f172a; }
        .btn { display: block; width: 100%; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; text-decoration: none; text-align: center; padding: 14px; border-radius: 10px; font-size: 15px; font-weight: 700; margin-bottom: 24px; }
        .footer { background: #f8fafc; padding: 20px 40px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>New chat has been assigned to you</p>
        </div>
        <div class="body">
            <div class="greeting">Hi {{ $agentName }}! 👋</div>
            <p class="text">
                A new conversation has been assigned to you. Please open it and respond to the visitor as soon as possible.
            </p>

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Visitor</span>
                    <span class="info-value">{{ $visitorName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Assigned To</span>
                    <span class="info-value">{{ $agentName }}</span>
                </div>
            </div>

            <a href="{{ $conversationUrl }}" class="btn">Open Conversation →</a>
        </div>
        <div class="footer">
            © {{ date('Y') }} {{ config('app.name') }} · This is an automated email
        </div>
    </div>
</body>
</html>