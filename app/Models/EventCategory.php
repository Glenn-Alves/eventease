<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['event_id', 'name', 'price', 'slots', 'inclusions', 'registration_ends_at', 'position'])]
class EventCategory extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'registration_ends_at' => 'date',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'event_category_id');
    }

    public function isClosed(): bool
    {
        return $this->registration_ends_at !== null
            && $this->registration_ends_at->isBefore(today());
    }

    public function inclusionList(): array
    {
        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', $this->inclusions ?? '')
        )));
    }
}