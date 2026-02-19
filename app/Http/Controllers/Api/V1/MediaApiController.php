<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(name="Media", description="Authenticated media management endpoints")
 */
class MediaApiController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/media",
     *     operationId="mediaList",
     *     tags={"Media"},
     *     summary="List media files",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="folder", in="query", @OA\Schema(type="string", default="/")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="type", in="query", description="Filter: 'images' for image-only", @OA\Schema(type="string", enum={"images"})),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=24)),
     *     @OA\Response(response=200, description="Paginated list of media files"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $folder = $request->input('folder', '/');
        $query = Media::inFolder($folder)->latest();

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->input('type') === 'images') {
            $query->images();
        }

        $media = $query->paginate($request->input('per_page', 24));
        return $this->success($media);
    }

    /**
     * @OA\Post(
     *     path="/media",
     *     operationId="mediaStore",
     *     tags={"Media"},
     *     summary="Upload a media file",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(property="file", type="string", format="binary", description="File to upload (max 10MB)"),
     *                 @OA\Property(property="folder", type="string", description="Target folder path"),
     *                 @OA\Property(property="alt_text", type="object", description="Alt text translations {tr, en}")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="File uploaded successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'folder' => 'nullable|string|max:255',
            'alt_text' => 'nullable|array',
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder', '/');
        $path = $file->store('media' . ($folder !== '/' ? '/' . trim($folder, '/') : ''), 'public');

        $data = [
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'folder' => $folder,
            'uploaded_by' => auth()->id(),
            'alt_text' => $request->input('alt_text'),
        ];

        if (str_starts_with($file->getMimeType(), 'image/')) {
            $dimensions = @getimagesize($file->getPathname());
            if ($dimensions) {
                $data['width'] = $dimensions[0];
                $data['height'] = $dimensions[1];
            }
        }

        $media = Media::create($data);

        return $this->success($media, [], 201);
    }

    /**
     * @OA\Delete(
     *     path="/media/{media}",
     *     operationId="mediaDestroy",
     *     tags={"Media"},
     *     summary="Delete a media file",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="media", in="path", required=true, description="Media ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="File deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Media not found")
     * )
     */
    public function destroy(Media $media)
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
        return $this->success(['message' => __('api.file_deleted')]);
    }
}
