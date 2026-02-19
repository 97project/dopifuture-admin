<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Media::class);

        $folder = $request->input('folder', '/');
        $query = Media::inFolder($folder)->with('uploader');

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('type') && $request->input('type') === 'images') {
            $query->images();
        }

        $media = $query->latest()->paginate(24)->withQueryString();

        // Get folder list
        $folders = Media::select('folder')
            ->distinct()
            ->orderBy('folder')
            ->pluck('folder')
            ->filter(fn($f) => $f !== '/')
            ->values();

        $stats = [
            'total' => Media::count(),
            'images' => Media::images()->count(),
            'total_size' => Media::sum('size'),
        ];

        return view('admin.media.index', compact('media', 'folder', 'folders', 'stats'));
    }

    public function upload(Request $request)
    {
        $this->authorize('create', Media::class);

        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240', // 10MB max
            'folder' => 'nullable|string|max:255',
        ]);

        $folder = $request->input('folder', '/');
        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $path = $file->store('media' . ($folder !== '/' ? '/' . trim($folder, '/') : ''), 'public');

            $mediaData = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'folder' => $folder,
                'uploaded_by' => auth()->id(),
            ];

            // Get image dimensions
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $dimensions = @getimagesize($file->getPathname());
                if ($dimensions) {
                    $mediaData['width'] = $dimensions[0];
                    $mediaData['height'] = $dimensions[1];
                }
            }

            $uploaded[] = Media::create($mediaData);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'count' => count($uploaded)]);
        }

        return back()->with('success', __('admin.files_uploaded', ['count' => count($uploaded)]));
    }

    public function destroy(Media $media)
    {
        $this->authorize('delete', $media);

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('admin.file_deleted'));
    }

    public function createFolder(Request $request)
    {
        $this->authorize('create', Media::class);

        $request->validate([
            'name' => 'required|string|max:100|regex:/^[a-z0-9\-_]+$/',
            'parent' => 'nullable|string',
        ]);

        $parent = $request->input('parent', '/');
        $folderPath = $parent === '/' ? '/' . $request->input('name') : $parent . '/' . $request->input('name');

        // Create an empty tracker — folder will appear in distinct folders
        Storage::disk('public')->makeDirectory('media/' . trim($folderPath, '/'));

        return back()->with('success', __('admin.folder_created'));
    }
}
