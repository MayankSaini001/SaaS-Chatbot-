<?php

namespace App\Mail;

class AgentMailHelper
{
    public static function sendAssigned(string $toEmail, string $agentName, string $visitorName, int $conversationId): void
    {
        $appName = config('app.name');
        $url     = config('app.url') . '/agent/conversations/' . $conversationId;
        $subject = "New Chat Assigned — {$appName}";

        $body = "
        <html>
        <body style='font-family:sans-serif;background:#f1f5f9;padding:30px'>
            <div style='max-width:520px;margin:auto;background:#fff;border-radius:12px;overflow:hidden'>
                <div style='background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:30px;text-align:center'>
                    <h1 style='color:#fff;margin:0'>{$appName}</h1>
                    <p style='color:rgba(255,255,255,0.8);margin:6px 0 0'>New chat assigned to you</p>
                </div>
                <div style='padding:30px'>
                    <p style='font-size:18px;font-weight:600;color:#0f172a'>Hi {$agentName}! 👋</p>
                    <p style='color:#64748b'>A new conversation has been assigned to you.</p>
                    <table style='width:100%;background:#f8fafc;border-radius:10px;padding:16px;margin:20px 0'>
                        <tr><td style='color:#94a3b8;font-size:12px;text-transform:uppercase'>Visitor</td><td style='font-weight:600;text-align:right'>{$visitorName}</td></tr>
                        <tr><td style='color:#94a3b8;font-size:12px;text-transform:uppercase'>Assigned To</td><td style='font-weight:600;text-align:right'>{$agentName}</td></tr>
                    </table>
                    <a href='{$url}' style='display:block;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;text-decoration:none;text-align:center;padding:14px;border-radius:10px;font-weight:700'>Open Conversation →</a>
                </div>
                <div style='background:#f8fafc;padding:16px;text-align:center;font-size:12px;color:#94a3b8'>
                    © " . date('Y') . " {$appName} · Automated email
                </div>
            </div>
        </body>
        </html>";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$appName} <noreply@topscripts.in>\r\n";

        mail($toEmail, $subject, $body, $headers);
    }
}