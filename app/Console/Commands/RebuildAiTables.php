<?php

namespace App\Console\Commands;

use App\Jobs\BuildAiTablesForAssessmentGroup;
use App\Models\FinalScore;
use Illuminate\Console\Command;

class RebuildAiTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:rebuild {company_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild AI summary / insight / LSTM tables from existing assessments';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $companyId = $this->argument('company_id');

        $query = FinalScore::query();

        if ($companyId) {
            $this->info("Rebuilding AI tables only for company_id = {$companyId}");
            $query->where('user_id', $companyId);
        } else {
            $this->info('Rebuilding AI tables for ALL companies...');
        }

        $finalScores = $query
            ->orderBy('user_id')
            ->orderBy('assessment_group_id')
            ->get();

        if ($finalScores->isEmpty()) {
            $this->warn('No FinalScore records found.');
            return Command::SUCCESS;
        }

        foreach ($finalScores as $fs) {
            $this->line("Processing company_id={$fs->user_id}, group={$fs->assessment_group_id} ...");

            // اجرای مستقیم Job
            $job = new BuildAiTablesForAssessmentGroup(
                (int) $fs->user_id,
                $fs->assessment_group_id
            );

            try {
                $job->handle();
                $this->info("✔ Done for company_id={$fs->user_id}, group={$fs->assessment_group_id}");
            } catch (\Throwable $e) {
                $this->error("✖ Failed for company_id={$fs->user_id}, group={$fs->assessment_group_id}: ".$e->getMessage());
            }
        }

        $this->info('All AI tables built (sync mode).');
        return Command::SUCCESS;
    }
}
