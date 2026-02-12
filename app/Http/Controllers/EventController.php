<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function publicIndex()
    {
        return view('events.index', [
            'events' => Event::whereDate('end_date', '>=', now()->startOfDay())
                ->orderBy('start_date')
                ->paginate(12),
        ]);
    }

    public function index()
    {
        return view('admin.events.index', [
            'events' => Event::latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return view('admin.events.create', [
            'event' => new Event(),
            'availableNews' => Post::where('category', 'news')->latest()->get(),
            'availableAdvisories' => Post::where('category', 'advisories')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'authors' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'news' => 'nullable|array',
            'news.*' => 'exists:posts,id',
            'advisories' => 'nullable|array',
            'advisories.*' => 'exists:posts,id',
        ]);

        // OPTIONAL kung may category column ka
        $data['category'] = 'events';

        // Combine date and time for datetime fields
        if (!empty($data['start_time'])) {
            $data['start_datetime'] = $data['start_date'] . ' ' . $data['start_time'] . ':00';
        } else {
            $data['start_datetime'] = $data['start_date'] . ' 00:00:00';
        }

        if (!empty($data['end_time'])) {
            $data['end_datetime'] = $data['end_date'] . ' ' . $data['end_time'] . ':00';
        } else {
            $data['end_datetime'] = $data['end_date'] . ' 23:59:59';
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        $event = Event::create($data);
        
        if (isset($data['news'])) {
            $event->news()->attach($data['news']);
        }
        
        if (isset($data['advisories'])) {
            $event->advisories()->attach($data['advisories']);
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully.');
    }

    public function publicShow(Event $event)
    {
        return view('events.show', [
            'event' => $event->load(['news', 'advisories'])
        ]);
    }

    public function show(Event $event)
    {
        return view('admin.events.show', [
            'event' => $event->load(['news', 'advisories'])
        ]);
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', [
            'event' => $event->load(['news', 'advisories']),
            'availableNews' => Post::where('category', 'news')->get(),
            'availableAdvisories' => Post::where('category', 'advisories')->get(),
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'authors' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'news' => 'nullable|array',
            'news.*' => 'exists:posts,id',
            'advisories' => 'nullable|array',
            'advisories.*' => 'exists:posts,id',
        ]);

        // Combine date and time for datetime fields
        if (!empty($data['start_time'])) {
            $data['start_datetime'] = $data['start_date'] . ' ' . $data['start_time'] . ':00';
        } else {
            $data['start_datetime'] = $data['start_date'] . ' 00:00:00';
        }

        if (!empty($data['end_time'])) {
            $data['end_datetime'] = $data['end_date'] . ' ' . $data['end_time'] . ':00';
        } else {
            $data['end_datetime'] = $data['end_date'] . ' 23:59:59';
        }

        if ($request->hasFile('image')) {

            if ($event->image && Storage::disk('public')->exists($event->image)) {
                Storage::disk('public')->delete($event->image);
            }

            $data['image'] = $request->file('image')->store('events', 'public');
        }
        
        $event->update($data);
        
        $event->news()->sync(isset($data['news']) ? $data['news'] : []);
        $event->advisories()->sync(isset($data['advisories']) ? $data['advisories'] : []);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return back()->with('success', 'Event moved to trash.');
    }

    public function trash()
    {
        $items = Event::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(10);

        return view('admin.events.trash', compact('items'));
    }

    public function restore(string $event)
    {
        $event = Event::withTrashed()->findOrFail($event);

        $event->restore();

        return back()->with('success', 'Event restored successfully.');
    }

    public function forceDelete(string $event)
    {
        $event = Event::withTrashed()->findOrFail($event);

        if ($event->image && Storage::disk('public')->exists($event->image)) {
            Storage::disk('public')->delete($event->image);
        }

        $event->forceDelete();

        return back()->with('success', 'Event permanently deleted.');
    }
}