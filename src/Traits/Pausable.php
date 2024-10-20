<?php

namespace Itsemon245\PausableJob\Traits;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

/**
 * Enables the pause method for a Job
 * @author Mojahidul Islam <itsemon245@gmail.com>
 */
trait Pausable
{
    
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
     * Set the model that will be responsible for pausing the job
     * @param Model $model
     * @return void
     */
    public function setPausedBy(Model $model){
        $this->paused_by_id = $model->id;
        $this->paused_by_type = $model::class;
    }

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
