<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;

class EventFiles
{
    // Deletes the poster and the gallery images of an event from the disk
    public static function delete(Event $event): void
    {
        if ($event->thumbnail) {
            Storage::disk('public')->delete($event->thumbnail);
        }

        foreach ($event->galleryItems as $item) {
            Storage::disk('public')->delete($item->path);
        }
    }
}