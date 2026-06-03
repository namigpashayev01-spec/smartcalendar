<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function destroy(Request $request, Media $media)
    {
        $postId = $media->post_id;
        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return back()->with('success', 'Media silindi.');
    }
}
