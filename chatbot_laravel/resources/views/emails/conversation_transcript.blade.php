<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Chat Transcript</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: linear-gradient(135deg, #ede9fe 0%, #e0e7ff 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 40px 16px;
            min-height: 100vh;
        }

        .wrap {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.18), 0 4px 16px rgba(0,0,0,0.06);
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 36px 40px 32px;
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
        .header h1 {
            color: #fff;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.3px;
        }
        .header p {
            color: rgba(255,255,255,0.78);
            font-size: 12px;
            margin-top: 6px;
            font-weight: 500;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        /* ── Body ── */
        .body { padding: 32px 36px 28px; }

        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #1a1040;
            margin-bottom: 8px;
        }

        .text {
            font-size: 13.5px;
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 22px;
        }

        /* ── Transcript ── */
        .transcript {
            background: #f8f7ff;
            border: 1.5px solid #ede9fe;
            border-radius: 16px;
            padding: 18px 18px 6px;
            margin-bottom: 24px;
        }

        .msg-row {
            display: flex;
            margin-bottom: 14px;
        }
        .msg-row.visitor { justify-content: flex-end; }
        .msg-row.agent   { justify-content: flex-start; }

        .msg-bubble {
            max-width: 78%;
            padding: 10px 14px;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.5;
        }
        .msg-row.visitor .msg-bubble {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #ffffff;
            border-bottom-right-radius: 4px;
        }
        .msg-row.agent .msg-bubble {
            background: #ffffff;
            color: #1e1b4b;
            border: 1px solid #e5e0fb;
            border-bottom-left-radius: 4px;
        }
        .msg-meta {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 3px;
            opacity: 0.7;
        }
        .msg-time {
            font-size: 10px;
            color: #b3aee0;
            margin-top: 6px;
            text-align: center;
        }

        /* ── Footer ── */
        .footer {
            background: linear-gradient(135deg, #f8f7ff 0%, #f0f4ff 100%);
            padding: 18px 40px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #ede9fe;
        }
    </style>
</head>
<body>
    <div class="wrap">

        <!-- Header -->
        <div class="header">
            <h1>{{ $tenantName }}</h1>
            <p>Chat Transcript</p>
        </div>

        <!-- Body -->
        <div class="body">

            <div class="greeting">Hi {{ $visitorName }}! 👋</div>
            <p class="text">
                Thanks for chatting with us. Here's a copy of your conversation for your records.
            </p>

            <!-- Transcript -->
            <div class="transcript">
                @forelse($messages as $message)
                    @php
                        $isVisitor = $message->sender_type === 'visitor';
                    @endphp
                    <div class="msg-row {{ $isVisitor ? 'visitor' : 'agent' }}">
                        <div class="msg-bubble">
                            <div class="msg-meta">{{ $isVisitor ? $visitorName : 'Support Agent' }}</div>
                            @if($message->attachment)
                                <img src="{{ $message->attachment }}" alt="Shared image" style="max-width: 220px; border-radius: 8px; display: block; margin-top: 2px;">
                            @else
                                {{ $message->body }}
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text" style="text-align:center;">No messages in this conversation.</p>
                @endforelse

                <div class="msg-time">Chat ended {{ $conversation->updated_at->format('d M Y, h:i A') }}</div>
            </div>

            <p class="text" style="margin-bottom: 0;">
                If you need further assistance, just reply to this email or start a new chat on our website anytime.
            </p>

        </div>

        <!-- Footer -->
        <div class="footer">
            © {{ date('Y') }} {{ $tenantName }} &nbsp;·&nbsp; This is an automated email
        </div>

    </div>
</body>
</html>