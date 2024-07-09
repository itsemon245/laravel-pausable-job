<?php

namespace Itsemon245\PausableJob\Traits;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Enables the resumeJobs method for a model
 * @author Mojahidul Islam <itsemon245@gmail.com>
 */
trait HasPausableJobs
{
    /**
     * Resumes all paused jobs by this model
     *
     * @return int count of the jobs resumed
     */
    public function resumeJobs()
    {
        return DB::table('jobs')
        ->where(function(Builder $builder){
            $builder->whereNotNull('paused_at');
            $builder->where('paused_by_id', $this->id);
            $builder->where('paused_by_type', $this::class);
        })
        ->update([
            'paused_at'=> null
        ]);
    }
}
