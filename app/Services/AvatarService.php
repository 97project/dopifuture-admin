<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Setting;

class AvatarService
{
    public function upload(User $user, UploadedFile $file): string
    {
        $this->deleteOld($user);

        $disk = Setting::getValue('storage', 'avatar_disk', 'local');
        $directory = 'avatars/' . $user->id;
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $filename;

        Storage::disk($disk)->putFileAs($directory, $file, $filename);

        // Generate thumbnail using GD (no Node.js)
        $this->generateThumbnail($file, $disk, $directory, $filename);

        $user->update([
            'avatar_path' => $path,
            'avatar_disk' => $disk,
        ]);

        return $path;
    }

    protected function generateThumbnail(UploadedFile $file, string $disk, string $directory, string $filename): void
    {
        try {
            $manager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            $image = $manager->read($file->getPathname());
            $image->cover(150, 150);

            $thumbFilename = 'thumb_' . $filename;
            $thumbPath = $directory . '/' . $thumbFilename;
            $encoded = $image->toJpeg(80);

            Storage::disk($disk)->put($thumbPath, (string) $encoded);
        } catch (\Exception $e) {
            // Thumbnail generation is optional, log error but don't fail
            \Log::warning('Thumbnail generation failed: ' . $e->getMessage());
        }
    }

    public function deleteOld(User $user): void
    {
        if (!$user->avatar_path) {
            return;
        }

        $disk = Storage::disk($user->avatar_disk);

        if ($disk->exists($user->avatar_path)) {
            $disk->delete($user->avatar_path);
        }

        $thumbPath = dirname($user->avatar_path) . '/thumb_' . basename($user->avatar_path);
        if ($disk->exists($thumbPath)) {
            $disk->delete($thumbPath);
        }
    }

    public function getUrl(User $user): ?string
    {
        if (!$user->avatar_path) {
            return null;
        }

        $disk = Storage::disk($user->avatar_disk);
        $isPrivate = Setting::getValue('storage', 'avatar_private', false);

        if ($isPrivate) {
            return $disk->temporaryUrl($user->avatar_path, now()->addMinutes(30));
        }

        return $disk->url($user->avatar_path);
    }

    public function getThumbnailUrl(User $user): ?string
    {
        if (!$user->avatar_path) {
            return null;
        }

        $thumbPath = dirname($user->avatar_path) . '/thumb_' . basename($user->avatar_path);
        $disk = Storage::disk($user->avatar_disk);

        if ($disk->exists($thumbPath)) {
            return $disk->url($thumbPath);
        }

        return $this->getUrl($user);
    }
}
