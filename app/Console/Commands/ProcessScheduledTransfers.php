<?php

namespace App\Console\Commands;

use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessScheduledTransfers extends Command
{
    protected $signature = 'transfers:process-scheduled';
    protected $description = 'Process all scheduled transfers that are due';

    public function handle()
    {
        $this->info('Starting to process scheduled transfers...');
        
        $transfers = Transfer::with(['fromAccount', 'toAccount'])
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($transfers->isEmpty()) {
            $this->info('No scheduled transfers to process.');
            return;
        }

        $this->info(sprintf('Found %d transfers to process.', $transfers->count()));
        $processed = 0;
        $failed = 0;

        foreach ($transfers as $transfer) {
            try {
                DB::beginTransaction();

                // Process the transfer
                $transfer->fromAccount->decrement('balance', $transfer->amount);
                $transfer->toAccount->increment('balance', $transfer->amount);

                // Update transfer status
                $transfer->update([
                    'status' => 'completed',
                    'executed_at' => now()
                ]);

                DB::commit();
                $processed++;

                $this->info(sprintf(
                    'Processed transfer #%d: %s %s from %s to %s',
                    $transfer->id,
                    number_format($transfer->amount, 2),
                    $transfer->fromAccount->currency,
                    $transfer->fromAccount->name,
                    $transfer->toAccount->name
                ));

            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;

                $error = sprintf(
                    'Failed to process transfer #%d: %s',
                    $transfer->id,
                    $e->getMessage()
                );
                
                $this->error($error);
                Log::error($error);
            }
        }

        $this->info(sprintf(
            'Finished processing transfers. Processed: %d, Failed: %d',
            $processed,
            $failed
        ));
    }
}
