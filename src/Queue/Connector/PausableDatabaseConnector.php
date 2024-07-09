<?php

namespace Itsemon245\PausableJob\Queue\Connector;

use Illuminate\Queue\Connectors\DatabaseConnector;
use Itsemon245\PausableJob\Queue\PausableDatabaseQueue;

/**
 * Replace the `DatabaseQueue` with `PausableDatabaseQueue`
 * @author Mojahidul Islam <itsemon245@gmail.com>
 */
class PausableDatabaseConnector extends DatabaseConnector
{
    
    public function connect(array $config)
    {
        return new PausableDatabaseQueue(
            $this->connections->connection($config['connection'] ?? null),
            $config['table'],
            $config['queue'],
            $config['retry_after'] ?? 60
        );
    }
}
