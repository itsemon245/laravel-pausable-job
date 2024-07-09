<?php

namespace Itsemon245\PausableJob\Traits;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Enables the pause method for a job
 * @author Mojahidul Islam <itsemon245@gmail.com>
 */
trait Pausable
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    
    /**
     * Id of the model that paused the job
     *
     * @var ?int
     */
    public ?int $paused_by_id = null;

    /**
     * Namespace of the model that paused the job
     *
     * @var ?string
     */
    public ?string $paused_by_type = null;

    /**
     * Pause the job in database and release it
     *
     * @param int $releaseDelay argument for `$job->release()` method in seconds
     * @return void
     */
    public function pause($releaseDelay = 0)
    {
        DB::table(config('queue.connections.database.table'))
        ->where('id', $this->job->getJobId())
        ->update([
            'paused_at' => now()->toDateTimeString(),
            'paused_by_id' => $this->paused_by_id,
            'paused_by_type' => $this->paused_by_type
        ]);
        Log::info($this::class.' job has been paused with id of '. $this->job->getJobId());
        $this->release($releaseDelay);
    }

    public function pauseIf(Closure|bool|null $condition, $releaseDelay = 0): void
    {
        if ($condition) {
            $this->pause($releaseDelay);
        }
    }
}
