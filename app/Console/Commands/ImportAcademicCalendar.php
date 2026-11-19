<?php

namespace App\Console\Commands;

use App\Services\AcademicCalendarImporter;
use Illuminate\Console\Command;

class ImportAcademicCalendar extends Command
{
    protected $signature = 'calendar:import
        {file=database/data/academic_calendar_2019.php : Calendar data file}
        {--dry-run : Validate and report without saving}
        {--align-terms : Move existing quarter/semester dates to the official calendar (audited)}
        {--refresh : Re-apply source values to records already imported (overwrites admin edits)}';

    protected $description = 'Import an official academic calendar (weeks, exam windows, breaks, milestones). Safe to re-run.';

    public function handle(AcademicCalendarImporter $importer): int
    {
        $path = base_path($this->argument('file'));
        $data = AcademicCalendarImporter::loadFile($path);

        if ($this->option('refresh') && ! $this->option('dry-run')
            && ! $this->confirm('--refresh overwrites administrator edits to imported weeks/events. Continue?')) {
            return self::FAILURE;
        }

        $report = $importer->import($data, (bool) $this->option('dry-run'), (bool) $this->option('align-terms'), (bool) $this->option('refresh'));

        $this->info(($report['dry_run'] ? '[DRY RUN — nothing saved] ' : '')."Academic year: {$report['year']->name} ({$report['year']->calendar_label})");
        $this->table(['', 'Years', 'Terms', 'Weeks', 'Events'], [
            ['Created', $report['created']['years'], $report['created']['terms'], $report['created']['weeks'], $report['created']['events']],
            ['Updated', $report['updated']['years'], $report['updated']['terms'], $report['updated']['weeks'], $report['updated']['events']],
            ['Unchanged', '-', $report['unchanged']['terms'], $report['unchanged']['weeks'], $report['unchanged']['events']],
        ]);

        if ($report['issues']) {
            $this->newLine();
            $this->warn(count($report['issues']).' item(s) for administrative review:');
            foreach ($report['issues'] as $issue) {
                $this->line(sprintf('  [%s] %s', strtoupper($issue['level']), $issue['message']));
            }
        }

        return self::SUCCESS;
    }
}
