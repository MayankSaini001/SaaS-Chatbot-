<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Widget extends Model
{
    protected $fillable = [
		'tenant_id', 'embed_token', 'color', 
		'position', 'greeting', 'is_active', 'title',
		'business_hours_enabled', 'business_hours_timezone', 'business_hours',
		'hide_branding',
	];

    protected $casts = [
        'business_hours_enabled' => 'boolean',
        'business_hours'         => 'array',
        'hide_branding'          => 'boolean',
    ];

    const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Default schedule used when a widget hasn't configured business hours
     * yet: Mon-Fri 9am-6pm, closed Sat/Sun.
     */
    public static function defaultBusinessHours(): array
    {
        $schedule = [];
        foreach (self::DAYS as $day) {
            $schedule[$day] = [
                'enabled' => !in_array($day, ['sat', 'sun']),
                'start'   => '09:00',
                'end'     => '18:00',
            ];
        }
        return $schedule;
    }

    /**
     * Is "now" within this widget's configured business hours?
     * If the feature is disabled, always returns true (no restriction).
     */
    public function isWithinBusinessHours(): bool
    {
        if (!$this->business_hours_enabled) {
            return true;
        }

        $schedule = $this->business_hours ?: self::defaultBusinessHours();
        $timezone = $this->business_hours_timezone ?: 'Asia/Kolkata';

        try {
            $now = Carbon::now($timezone);
        } catch (\Throwable $e) {
            $now = Carbon::now();
        }

        $dayKey = self::DAYS[$now->dayOfWeekIso - 1] ?? 'mon';
        $today  = $schedule[$dayKey] ?? null;

        if (!$today || empty($today['enabled'])) {
            return false;
        }

        $start = $today['start'] ?? '00:00';
        $end   = $today['end'] ?? '23:59';

        $nowTime = $now->format('H:i');

        return $nowTime >= $start && $nowTime <= $end;
    }

    /**
     * Human-readable summary for the "we're closed" widget banner,
     * e.g. "Mon-Fri 9:00 AM - 6:00 PM".
     */
    public function businessHoursSummary(): ?string
    {
        $schedule = $this->business_hours ?: self::defaultBusinessHours();
        $openDays = array_filter($schedule, fn($d) => !empty($d['enabled']));

        if (empty($openDays)) {
            return null;
        }

        $first = reset($openDays);
        $labels = ['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'];
        $dayLabels = array_map(fn($k) => $labels[$k], array_keys($openDays));

        return implode(', ', $dayLabels) . ' ' . $this->formatTime($first['start']) . ' - ' . $this->formatTime($first['end']);
    }

    private function formatTime(string $time): string
    {
        try {
            return Carbon::createFromFormat('H:i', $time)->format('g:i A');
        } catch (\Throwable $e) {
            return $time;
        }
    }
}
