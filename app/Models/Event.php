<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'title', 'type', 'thumbnail', 'description', 'event_date', 'event_time',
    'location', 'total_slots', 'registration_mode', 'external_form_url', 'form_theme',
])]
class Event extends Model
{
    public const TYPES = [
        'concert' => 'Concert',
        'funrun' => 'Fun Run',
        'festival' => 'Festival',
        'others' => 'Others',
    ];

    public const FONTS = [
        'sans' => 'ui-sans-serif, system-ui, sans-serif',
        'serif' => 'ui-serif, Georgia, serif',
        'mono' => 'ui-monospace, Consolas, monospace',
        'rounded' => "'DM Sans', ui-sans-serif, sans-serif",
    ];

    public const RADII = [
        'none' => '0px',
        'md' => '8px',
        'xl' => '18px',
    ];

    public const THEME_DEFAULTS = [
        'primary' => '#1f2937',
        'background' => '#f3f4f6',
        'card' => '#ffffff',
        'text' => '#111827',
        'font' => 'sans',
        'radius' => 'md',
        'title' => '',
        'intro' => '',
        'button' => 'Register',
        'show_banner' => true,
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'form_theme' => 'array',
        ];
    }

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail ? asset('storage/'.$this->thumbnail) : null;
    }

    public function theme(): array
    {
        return array_merge(self::THEME_DEFAULTS, $this->form_theme ?? []);
    }

    // Black or white text, whichever is easier to read on the given color
    public static function readableOn(string $hex): string
    {
        [$r, $g, $b] = array_map('hexdec', str_split(ltrim($hex, '#'), 2));

        return (($r * 299 + $g * 587 + $b * 114) / 1000) > 150 ? '#111827' : '#ffffff';
    }

    public function themeStyle(): string
    {
        $t = $this->theme();

        return implode(';', [
            '--ee-primary:'.$t['primary'],
            '--ee-primary-text:'.self::readableOn($t['primary']),
            '--ee-bg:'.$t['background'],
            '--ee-card:'.$t['card'],
            '--ee-text:'.$t['text'],
            '--ee-font:'.(self::FONTS[$t['font']] ?? self::FONTS['sans']),
            '--ee-radius:'.(self::RADII[$t['radius']] ?? self::RADII['md']),
        ]);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(EventField::class)->orderBy('position');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(EventCategory::class)->orderBy('position');
    }
}