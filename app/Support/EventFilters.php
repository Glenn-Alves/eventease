<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventFilters
{
    public const PRESETS = [
        'today' => 'Today',
        'week' => 'Next 7 days',
        'month' => 'This month',
        'days30' => 'Next 30 days',
    ];

    // Reads the search and date choices from the address and cleans them
    public static function fromRequest(Request $request): array
    {
        $q = $request->query('q');
        $q = is_string($q) ? mb_substr(trim((string) preg_replace('/\s+/', ' ', $q)), 0, 100) : '';

        $when = $request->query('when');
        $when = (is_string($when) && isset(self::PRESETS[$when])) ? $when : null;

        return [
            'q' => $q,
            'when' => $when,
            'from' => self::date($request->query('from')),
            'to' => self::date($request->query('to')),
            'open' => $request->boolean('open'),
        ];
    }

    public static function active(array $filters): bool
    {
        return $filters['q'] !== ''
            || $filters['when'] !== null
            || $filters['from'] !== null
            || $filters['to'] !== null
            || $filters['open'];
    }

    // The first and last day to show. A custom From or To replaces the quick option.
    public static function range(array $filters): array
    {
        $from = $filters['from'];
        $to = $filters['to'];

        if (! $from && ! $to) {
            [$from, $to] = match ($filters['when']) {
                'today' => [today(), today()],
                'week' => [today(), today()->addDays(7)],
                'month' => [today(), today()->endOfMonth()],
                'days30' => [today(), today()->addDays(30)],
                default => [null, null],
            };
        }

        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    public static function apply(Builder $query, array $filters): Builder
    {
        // Every word must be found in the name or in the place
        $terms = array_slice(array_filter(explode(' ', $filters['q'])), 0, 6);

        foreach ($terms as $term) {
            $like = '%'.addcslashes($term, '%_\\').'%';

            $query->where(fn ($inner) => $inner
                ->where('title', 'like', $like)
                ->orWhere('location', 'like', $like));
        }

        [$from, $to] = self::range($filters);

        if ($from) {
            $query->whereDate('event_date', '>=', $from->toDateString());
        }

        if ($to) {
            $query->whereDate('event_date', '<=', $to->toDateString());
        }

        if ($filters['open']) {
            $now = now()->format('Y-m-d H:i:s');

            // The deadline has not passed (without a deadline, registration closes when the event starts)
            $query->where(function ($inner) use ($now) {
                $inner->where(fn ($none) => $none
                    ->whereNull('registration_deadline')
                    ->whereRaw('TIMESTAMP(event_date, event_time) > ?', [$now]))
                    ->orWhere('registration_deadline', '>', $now);
            });

            // And there is still a slot (events on an outside form have no slot count)
            $query->where(function ($inner) {
                $inner->where('registration_mode', 'external')
                    ->orWhereRaw('(select count(*) from registrations where registrations.event_id = events.id) < events.total_slots');
            });
        }

        return $query;
    }

    private static function date(mixed $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        // Rejects dates that do not exist, like 2026-02-31
        return ($date && $date->format('Y-m-d') === $value) ? $date->startOfDay() : null;
    }
}