<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Spatie\Backup\BackupDestination\BackupDestination;
use App\Jobs\CreateBackupJob;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    /**
     * Display a listing of the backups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $backups = $this->getAllBackups();
            $stats = $this->getBackupStats($backups);
            
            return view('pages.backups.index', compact('backups', 'stats'));
        } catch (\Exception $e) {
            Log::error('Failed to load backups: ' . $e->getMessage());
            return view('pages.backups.index', [
                'backups' => collect(),
                'stats' => (object) [
                    'total_backups' => 0,
                    'total_size' => '0 B',
                    'oldest_backup' => null,
                    'newest_backup' => null,
                ]
            ])->with('error', 'Failed to load backups: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified backup.
     *
     * @param  string  $backupName
     * @return \Illuminate\View\View
     */
    public function show($backupName)
    {
        try {
            $backup = $this->findBackup($backupName);
            
            if (!$backup) {
                abort(404, 'Backup not found');
            }

            return view('pages.backups.show', compact('backup'));
        } catch (\Exception $e) {
            Log::error('Failed to show backup: ' . $e->getMessage());
            return redirect()->route('backups.index')
                ->with('error', 'Failed to load backup details: ' . $e->getMessage());
        }
    }

    /**
     * Download the specified backup.
     *
     * @param  string  $backupName
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($backupName)
    {
        try {
            $backup = $this->findBackup($backupName);
            
            if (!$backup) {
                abort(404, 'Backup not found');
            }

            $backupDisk = $backup->disk;
            $path = $backup->path;

            if (!Storage::disk($backupDisk)->exists($path)) {
                abort(404, 'Backup file not found');
            }

            return response()->download(
                Storage::disk($backupDisk)->path($path), 
                $backup->filename
            );
        } catch (\Exception $e) {
            Log::error('Failed to download backup: ' . $e->getMessage());
            return redirect()->route('backups.index')
                ->with('error', 'Failed to download backup: ' . $e->getMessage());
        }
    }

    /**
     * Create a new backup.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
            CreateBackupJob::dispatch();
            
            Log::info('Backup job dispatched to queue');
            
            return redirect()->route('backups.index')
                ->with('success', 'Backup has been queued and will be processed shortly!');
    }

    /**
     * Remove the specified backup from storage.
     *
     * @param  string  $backupName
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($backupName)
    {
        try {
            $backup = $this->findBackup($backupName);
            
            if (!$backup) {
                abort(404, 'Backup not found');
            }

            Storage::disk($backup->disk)->delete($backup->path);
            
            Log::info('Backup deleted: ' . $backupName);
            
            return redirect()->route('backups.index')
                ->with('success', 'Backup deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete backup: ' . $e->getMessage());
            return redirect()->route('backups.index')
                ->with('error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }

    /**
     * Clean old backups.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clean()
    {
        try {
            Artisan::call('backup:clean');
            
            Log::info('Backup cleanup completed');
            
            return redirect()->route('backups.index')
                ->with('success', 'Old backups cleaned successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to clean backups: ' . $e->getMessage());
            return redirect()->route('backups.index')
                ->with('error', 'Failed to clean backups: ' . $e->getMessage());
        }
    }

    /**
     * Get backup statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        try {
            $backups = $this->getAllBackups();
            $stats = $this->getBackupStats($backups);

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Failed to get backup stats: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to get backup statistics'
            ], 500);
        }
    }

    /**
     * Get all backups from all configured destinations.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getAllBackups()
    {
        $allBackups = collect();
        
        // Get backup destinations from config
        $backupDestinations = config('backup.backup.destination.disks', ['local']);
        
        foreach ($backupDestinations as $disk) {
            try {
                $backupDestination = BackupDestination::create($disk, config('backup.backup.name'));
                $backups = $backupDestination->backups();
                
                foreach ($backups as $backup) {
                    $allBackups->push((object) [
                        'filename' => basename($backup->path()),
                        'path' => $backup->path(),
                        'size' => $backup->sizeInBytes(),
                        'date' => $backup->date(),
                        'disk' => $disk,
                        'backup' => $backup,
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("Failed to get backups from disk {$disk}: " . $e->getMessage());
                continue;
            }
        }
        
        return $allBackups->sortByDesc('date');
    }

    /**
     * Find a backup by its name.
     *
     * @param  string  $backupName
     * @return object|null
     */
    private function findBackup($backupName)
    {
        $backups = $this->getAllBackups();
        
        return $backups->first(function ($backup) use ($backupName) {
            return basename($backup->path) === $backupName;
        });
    }

    /**
     * Get backup statistics.
     *
     * @param  \Illuminate\Support\Collection  $backups
     * @return object
     */
    private function getBackupStats($backups)
    {
        $totalBackups = $backups->count();
        $totalSize = $backups->sum('size');
        $oldestBackup = $backups->last();
        $newestBackup = $backups->first();

        return (object) [
            'total_backups' => $totalBackups,
            'total_size' => $this->formatBytes($totalSize),
            'oldest_backup' => $oldestBackup ? $oldestBackup->date->format('Y-m-d H:i:s') : null,
            'newest_backup' => $newestBackup ? $newestBackup->date->format('Y-m-d H:i:s') : null,
            'average_size' => $totalBackups > 0 ? $this->formatBytes($totalSize / $totalBackups) : '0 B',
        ];
    }

    /**
     * Format bytes to human readable format.
     *
     * @param  int  $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Test backup configuration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function test()
    {
        try {
            // Test if backup configuration is valid
            $backupDestinations = config('backup.backup.destination.disks', ['local']);
            
            foreach ($backupDestinations as $disk) {
                if (!config("filesystems.disks.{$disk}")) {
                    throw new \Exception("Disk '{$disk}' is not configured");
                }
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Backup configuration is working correctly'
            ]);
        } catch (\Exception $e) {
            Log::error('Backup test failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Backup test failed: ' . $e->getMessage()
            ], 500);
        }
    }

}
