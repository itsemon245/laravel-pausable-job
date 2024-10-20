<?php

namespace Itsemon245\PausableJob\Queue;

use Illuminate\Queue\DatabaseQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Jobs\DatabaseJobRecord;

/**
 * Overrides the `DatabaseQueue` class to add pausable columns in the jobs table in database.
 * @author Mojahidul Islam <itsemon245@gmail.com>
 */
class PausableDatabaseQueue extends DatabaseQueue {
    protected ?string $paused_at = null;
    protected ?string $paused_by_type = null;
    protected ?int $paused_by_id = null;

     /**
     * Push a new job onto the queue.
     *
     * @param  mixed  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        $this->paused_by_id = $job->paused_by_id;
        $this->paused_by_type = $job->paused_by_type;
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $this->getQueue($queue), $data),
            $queue,
            null,
            function ($payload, $queue) {
                return $this->pushToDatabase($queue, $payload);
            }
        );
    }

    /**
     * Delete a reserved job from the reserved queue and release it.
     *
     * @param  string  $queue
     * @param  \Illuminate\Queue\Jobs\DatabaseJob  $job
     * @param  int  $delay
     * @return void
     */
    public function deleteAndRelease($queue, $job, $delay) {

        $jobRecord = $this->database->table($this->table)->where('id', $job->getJobId())->first();
        $this->paused_at = $jobRecord->paused_at;
        $this->paused_by_id = $jobRecord->paused_by_id;
        $this->paused_by_type = $jobRecord->paused_by_type;
        $this->database->transaction(function () use ($queue, $job, $delay) {
            if ($this->database->table($this->table)->lockForUpdate()->find($job->getJobId())) {
                $this->database->table($this->table)->where('id', $job->getJobId())->delete();
            }

            $this->release($queue, $job->getJobRecord(), $delay);
        });
    }

    /**
     * Create an array to insert for the given job.
     *
     * @param  string|null  $queue
     * @param  string  $payload
     * @param  int  $availableAt
     * @param  int  $attempts
     * @return array
     */
    protected function buildDatabaseRecord($queue, $payload, $availableAt, $attempts = 0) {
        return [
            'queue' => $queue,
            'attempts' => $attempts,
            'reserved_at' => null,
            // Paused at values from old job
            'paused_at' => $this->paused_at,
            'paused_by_id' => $this->paused_by_id,
            'paused_by_type' => $this->paused_by_type,
            // Paused at values from old job
            'available_at' => $availableAt,
            'created_at' => $this->currentTime(),
            'payload' => $payload,
        ];
    }

    /**
     * Get the next available job for the queue.
     *
     * @param  string|null  $queue
     * @return \Illuminate\Queue\Jobs\DatabaseJobRecord|null
     */
    protected function getNextAvailableJob($queue) {
        $job = $this->database->table($this->table)
                    ->lock($this->getLockForPopping())
                    ->where('queue', $this->getQueue($queue))
                    // Skip the paused jobs
                    ->where('paused_at', null)
                    ->where(function ($query) {
                        $this->isAvailable($query);
                        $this->isReservedButExpired($query);
                    })
                    ->orderBy('id', 'asc')
                    ->first();

        return $job ? new DatabaseJobRecord((object) $job) : null;
    }

}
