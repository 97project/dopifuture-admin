<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    protected string $disk = 'local';
    protected string $path;

    public function __construct()
    {
        $this->path = config('backup.backup.name', 'Panel26') . '/';
    }

    public function index()
    {
        abort_unless(auth()->user()->can('backups.view'), 403);

        $backups = [];
        $disk = Storage::disk($this->disk);

        if ($disk->exists($this->path)) {
            $files = $disk->files($this->path);
            foreach ($files as $file) {
                if (str_ends_with($file, '.zip')) {
                    $backups[] = [
                        'path' => $file,
                        'name' => basename($file),
                        'size' => $disk->size($file),
                        'size_human' => $this->formatBytes($disk->size($file)),
                        'date' => $disk->lastModified($file),
                        'date_human' => date('Y-m-d H:i:s', $disk->lastModified($file)),
                    ];
                }
            }
        }

        // Sort by date descending
        usort($backups, fn($a, $b) => $b['date'] - $a['date']);

        return view('admin.backups.index', compact('backups'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('backups.view'), 403);

        try {
            Artisan::call('backup:run', ['--only-db' => true]);
            return back()->with('success', __('admin.backup_created'));
        } catch (\Exception $e) {
            return back()->with('error', __('admin.backup_failed') . ': ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        abort_unless(auth()->user()->can('backups.view'), 403);

        $path = $this->path . $filename;
        $disk = Storage::disk($this->disk);

        if (!$disk->exists($path)) {
            return back()->with('error', __('admin.backup_not_found'));
        }

        return $disk->download($path, $filename);
    }

    public function destroy(string $filename)
    {
        abort_unless(auth()->user()->can('backups.view'), 403);

        $path = $this->path . $filename;
        $disk = Storage::disk($this->disk);

        if (!$disk->exists($path)) {
            return back()->with('error', __('admin.backup_not_found'));
        }

        $disk->delete($path);

        return back()->with('success', __('admin.backup_deleted'));
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
