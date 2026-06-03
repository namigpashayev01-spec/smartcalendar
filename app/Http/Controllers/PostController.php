<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Post;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PostController extends Controller
{
    private array $statuses = [
        'planned'   => 'Planlaşdırılıb',
        'progress'  => 'Hazırlanır',
        'pending'   => 'Təsdiq gözləyir',
        'published' => 'Paylaşılıb',
    ];

    private array $colors = [
        '#0176D3','#2E844A','#B45309','#BA0517',
        '#7C3AED','#0891B2','#DB2777','#475569',
    ];

    public function day(string $date)
    {
        $posts = Post::with('media')
            ->where('date', $date)
            ->orderBy('id')
            ->get();

        return view('posts.day', compact('date', 'posts'));
    }

    public function create(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        return view('posts.form', [
            'post'     => null,
            'date'     => $date,
            'statuses' => $this->statuses,
            'colors'   => $this->colors,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'        => 'required|date',
            'company'     => 'required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:7',
            'status'      => 'required|in:planned,progress,pending,published',
            'media.*'     => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm|max:51200',
        ]);

        $post = Post::create([
            'date'        => $data['date'],
            'company'     => $data['company'],
            'description' => $data['description'] ?? null,
            'color'       => $data['color'] ?? '#0176D3',
            'status'      => $data['status'],
            'user_id'     => Auth::id(),
        ]);

        $this->handleMediaUpload($request, $post);

        $this->log('Yaratdı', "{$post->company} ({$post->date->format('Y-m-d')})");

        return redirect()->route('day', $post->date->format('Y-m-d'))
            ->with('success', 'Paylaşım yaradıldı.');
    }

    public function show(Post $post)
    {
        $post->load('media');
        return view('posts.show', [
            'post'     => $post,
            'statuses' => $this->statuses,
        ]);
    }

    public function edit(Post $post)
    {
        $post->load('media');
        return view('posts.form', [
            'post'     => $post,
            'date'     => $post->date->format('Y-m-d'),
            'statuses' => $this->statuses,
            'colors'   => $this->colors,
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'date'           => 'required|date',
            'company'        => 'required|string|max:255',
            'description'    => 'nullable|string',
            'color'          => 'nullable|string|max:7',
            'status'         => 'required|in:planned,progress,pending,published',
            'media.*'        => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm|max:51200',
            'delete_media.*' => 'nullable|integer',
        ]);

        // Seçilmiş media-ları sil
        if (!empty($data['delete_media'])) {
            foreach ($post->media()->whereIn('id', $data['delete_media'])->get() as $m) {
                Storage::disk('public')->delete($m->file_path);
                $m->delete();
            }
        }

        $post->update([
            'date'        => $data['date'],
            'company'     => $data['company'],
            'description' => $data['description'] ?? null,
            'color'       => $data['color'] ?? '#0176D3',
            'status'      => $data['status'],
        ]);

        // Mövcud media description-larını yenilə
        foreach ($request->input('media_desc_existing', []) as $id => $desc) {
            $post->media()->where('id', $id)->update(['description' => $desc ?: null]);
        }

        $this->handleMediaUpload($request, $post);

        $this->log('Redaktə etdi', "{$post->company} ({$post->date->format('Y-m-d')})");

        return redirect()->route('posts.show', $post)
            ->with('success', 'Paylaşım yeniləndi.');
    }

    public function destroy(Post $post)
    {
        $label = "{$post->company} ({$post->date->format('Y-m-d')})";
        $date  = $post->date->format('Y-m-d');

        foreach ($post->media as $m) {
            Storage::disk('public')->delete($m->file_path);
        }
        $post->delete();

        $this->log('Sildi', $label);

        return redirect()->route('day', $date)
            ->with('success', 'Paylaşım silindi.');
    }

    private function handleMediaUpload(Request $request, Post $post): void
    {
        if (!$request->hasFile('media')) return;

        $order        = $post->media()->max('sort_order') ?? -1;
        $descriptions = $request->input('media_descriptions', []);

        foreach ($request->file('media') as $i => $file) {
            $order++;
            $isImage = str_starts_with($file->getMimeType(), 'image/');

            if ($isImage) {
                $manager  = new ImageManager(new Driver());
                $img      = $manager->read($file->getRealPath());
                if ($img->width() > 3000 || $img->height() > 3000) {
                    $img->scaleDown(3000, 3000);
                }
                $ext = strtolower($file->getClientOriginalExtension());
                if ($ext === 'png') {
                    $filename = uniqid() . '.png';
                    $path     = 'media/' . $filename;
                    Storage::disk('public')->put($path, $img->toPng()->toString());
                } elseif ($ext === 'webp') {
                    $filename = uniqid() . '.webp';
                    $path     = 'media/' . $filename;
                    Storage::disk('public')->put($path, $img->toWebp(95)->toString());
                } else {
                    $filename = uniqid() . '.jpg';
                    $path     = 'media/' . $filename;
                    Storage::disk('public')->put($path, $img->toJpeg(95)->toString());
                }
            } else {
                $path = $file->storeAs('media', uniqid() . '.' . $file->getClientOriginalExtension(), 'public');
            }

            Media::create([
                'post_id'       => $post->id,
                'type'          => $isImage ? 'image' : 'video',
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'description'   => $descriptions[$i] ?? null,
                'sort_order'    => $order,
            ]);
        }
    }

    private function log(string $action, string $target): void
    {
        AuditLog::create(['user_id' => Auth::id(), 'action' => $action, 'target' => $target, 'created_at' => now()]);
    }
}
