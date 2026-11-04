<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventGalleryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    private const MAX_ITEMS = 60;

    public function edit(Event $event)
    {
        $this->checkOwner($event);

        $items = $event->galleryItems;

        return view('organizer.events.gallery', compact('event', 'items'));
    }

    public function store(Request $request, Event $event)
    {
        $this->checkOwner($event);

        $data = $request->validate([
            'section' => ['required', 'string', 'max:60'],
            'images' => ['required', 'array', 'max:12'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'images.required' => 'Choose at least one image.',
            'images.max' => 'You can add up to 12 images at a time.',
            'images.*.uploaded' => 'An image could not be uploaded. It may be bigger than the server allows.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be JPG, PNG, or WEBP.',
            'images.*.max' => 'Each image must be 4 MB or smaller.',
        ]);

        $files = $request->file('images');
        $existing = EventGalleryItem::where('event_id', $event->id)->count();

        if ($existing + count($files) > self::MAX_ITEMS) {
            return back()->withInput()->withErrors([
                'images' => 'A gallery can have up to '.self::MAX_ITEMS.' images. This one has '.$existing.'.',
            ]);
        }

        $position = (int) EventGalleryItem::where('event_id', $event->id)->max('position') + 1;

        foreach ($files as $file) {
            $event->galleryItems()->create([
                'section' => trim($data['section']),
                'path' => $file->store('gallery', 'public'),
                'position' => $position++,
            ]);
        }

        return back()->with('status', count($files) === 1 ? '1 image added.' : count($files).' images added.');
    }

    public function update(Request $request, Event $event, EventGalleryItem $item)
    {
        $this->checkOwner($event);
        abort_unless($item->event_id === $event->id, 404);

        $data = $request->validate([
            'section' => ['required', 'string', 'max:60'],
            'title' => ['nullable', 'string', 'max:100'],
            'caption' => ['nullable', 'string', 'max:300'],
        ]);

        $item->update($data);

        return back()->with('status', 'Saved.');
    }

    public function destroy(Event $event, EventGalleryItem $item)
    {
        $this->checkOwner($event);
        abort_unless($item->event_id === $event->id, 404);

        Storage::disk('public')->delete($item->path);
        $item->delete();

        return back()->with('status', 'Image deleted.');
    }

    // Called after dragging an image to a new place
    public function reorder(Request $request, Event $event): JsonResponse
    {
        $this->checkOwner($event);

        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        foreach (array_values($request->input('order')) as $position => $id) {
            EventGalleryItem::where('event_id', $event->id)
                ->whereKey($id)
                ->update(['position' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    private function checkOwner(Event $event): void
    {
        abort_unless($event->user_id === auth()->id(), 403);
    }
}