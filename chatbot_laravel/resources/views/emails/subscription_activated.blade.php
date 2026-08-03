<!DOCTYPE html><html><body style="font-family:sans-serif;background:#f8fafc;padding:40px 20px;">
<div style="max-width:520px;margin:0 auto;background:white;border-radius:16px;padding:40px;border:1px solid #e2e8f0;">
  <h2 style="color:#1e293b;margin-bottom:8px;">Your subscription is active! 🎉</h2>
  <p style="color:#64748b;">Hi {{ $tenant->name }},</p>
  <p style="color:#64748b;">Your <strong style="color:#4f46e5;text-transform:capitalize;">{{ $plan }}</strong> plan is now active. You can start using your chatbot widget right away.</p>
  <a href="{{ config('app.url') }}/dashboard" style="display:inline-block;margin-top:24px;padding:12px 28px;background:#4f46e5;color:white;border-radius:10px;text-decoration:none;font-weight:600;">Go to Dashboard</a>
  <p style="color:#94a3b8;font-size:12px;margin-top:32px;">If you have any questions, reply to this email.</p>
</div>
</body></html>