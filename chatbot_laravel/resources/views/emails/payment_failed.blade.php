<!DOCTYPE html><html><body style="font-family:sans-serif;background:#f8fafc;padding:40px 20px;">
<div style="max-width:520px;margin:0 auto;background:white;border-radius:16px;padding:40px;border:1px solid #e2e8f0;">
  <h2 style="color:#dc2626;margin-bottom:8px;">Payment failed ⚠️</h2>
  <p style="color:#64748b;">Hi {{ $tenant->name }},</p>
  <p style="color:#64748b;">We could not process your last payment. Please update your payment method to avoid service interruption.</p>
  <a href="{{ config('app.url') }}/billing" style="display:inline-block;margin-top:24px;padding:12px 28px;background:#dc2626;color:white;border-radius:10px;text-decoration:none;font-weight:600;">Update Payment Method</a>
</div>
</body></html>