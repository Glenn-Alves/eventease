<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['registration_id', 'event_field_id', 'answer'])]
class RegistrationAnswer extends Model
{
    public function field(): BelongsTo
    {
        return $this->belongsTo(EventField::class, 'event_field_id');
    }
}