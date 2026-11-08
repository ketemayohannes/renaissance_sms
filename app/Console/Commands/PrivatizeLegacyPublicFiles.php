<?php

namespace App\Console\Commands;

use App\Models\ActivityAttachment;
use App\Models\EmployeeDocument;
use App\Models\ExportRequest;
use App\Models\StudentDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Older versions of the app wrote private records (staff and student documents,
 * report-card exports, activity attachments, student submissions) to the PUBLIC disk,
 * where anyone with the URL could download them. New uploads go to the private disk.
 *
 * This moves the old files to the private disk under the same relative path, so the
 * existing database paths keep working (every download action checks the private disk
 * first). Safe to re-run: files already on the private disk are skipped.
 */
class PrivatizeLegacyPublicFiles extends Command
{
    protected $signature = 'storage:privatize-legacy {--dry-run : Only report what would be moved}';

    protected $description = 'Move private records that older versions stored on the public disk to the private disk';

    public function handle(): int
    {
        $sources = [
            'Staff documents' => EmployeeDocument::query(),
            'Student documents' => StudentDocument::query(),
            'Report-card exports' => ExportRequest::query()->whereNotNull('file_path'),
            'Activity attachments and submissions' => ActivityAttachment::query(),
        ];

        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $dryRun = (bool) $this->option('dry-run');
        $failed = 0;

        foreach ($sources as $label => $query) {
            $moved = 0;

            foreach ($query->pluck('file_path')->filter()->unique() as $path) {
                if ($private->exists($path) || ! $public->exists($path)) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("  would move: {$path}");
                    $moved++;

                    continue;
                }

                $private->writeStream($path, $public->readStream($path));

                // Only delete the public copy once the private copy is complete.
                if ($private->exists($path) && $private->size($path) === $public->size($path)) {
                    $public->delete($path);
                    $moved++;
                } else {
                    $this->error("  could not verify copy, left in place: {$path}");
                    $failed++;
                }
            }

            $this->info(($dryRun ? 'Would move' : 'Moved')." {$moved} file(s): {$label}");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
