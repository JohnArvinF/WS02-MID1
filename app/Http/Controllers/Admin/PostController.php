<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class PostController extends Controller
{
    public function index()
    {
        return view('admin.posts.index', [
            'posts' => Post::latest()->paginate(10),
        ]);
    }

    public function news()
    {
        return $this->categoryIndex('news');
    }

    public function advisories()
    {
        return $this->categoryIndex('advisories');
    }

    public function events()
    {
        return $this->categoryIndex('events');
    }

    private function categoryIndex(string $category)
    {
        return view('admin.posts.index', [
            'posts' => Post::where('category', $category)
                ->latest()
                ->paginate(10),
            'category' => $category,
        ]);
    }

    public function create()
    {
        return view('admin.posts.create');
    }

    public function createNews()
    {
        return view('admin.posts.create', ['category' => 'news']);
    }

    public function createAdvisories()
    {
        return view('admin.posts.create', ['category' => 'advisories']);
    }

    public function createEvents()
    {
        return view('admin.posts.create', ['category' => 'events']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:news,advisories,events',
            'published_at' => 'required|date',
            'authors' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $dt = Carbon::parse($data['published_at']);
        $data['date'] = $dt->toDateString();
        $data['time'] = $dt->format('H:i');

        unset($data['published_at']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        Post::create($data);

        return redirect()->route($data['category'] . '.index')
            ->with('success', ucfirst($data['category']) . ' created successfully.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'published_at' => 'required|date',
            'authors' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);


        $data['category'] = $post->category;

        $dt = Carbon::parse($data['published_at']);
        $data['date'] = $dt->toDateString();
        $data['time'] = $dt->format('H:i');

        unset($data['published_at']);

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }

            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        return redirect()
            ->route($post->category . '.index')
            ->with('success', ucfirst($post->category) . ' updated successfully.');
    }

    public function destroy(Post $post)
    {

        $post->delete();

        return back()->with('success', 'Post moved to trash.');
    }

    public function trash(?string $category = null)
    {
    $query = Post::onlyTrashed();

    if ($category) {
        $query->where('category', $category);
    }

    $items = $query->orderByDesc('deleted_at')->paginate(10);

    return view('admin.posts.trash', compact('items', 'category'));
    }





    public function restore(string $post)
    {
        $post = Post::withTrashed()->findOrFail($post);

        $post->restore();

        return redirect()
            ->route($post->category . '.index')
            ->with('success', 'Post restored successfully.');
    }

    public function forceDelete(string $post)
    {
        $post = Post::withTrashed()->findOrFail($post);

        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }

        $post->forceDelete();

        return back()->with('success', 'Post permanently deleted.');
    }
}