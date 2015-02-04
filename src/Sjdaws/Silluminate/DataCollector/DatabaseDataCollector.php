<?php

namespace Sjdaws\Silluminate\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseDataCollector extends DataCollector
{
    /**
     * The connections to the database;
     *
     * @var array $connections
     */
    private $connections = array();

    /**
     * Initialise a new collector
     *
     * @param Capsule $database
     * @return void
     */
    public function __construct(Capsule $database)
    {
        $this->connections = $database->getDatabaseManager()->getConnections();
    }

    /**
     * Collect data about our connections and queries
     *
     * @param Request $request
     * @param Response $response
     * @param Exception $exception
     * @return void
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'activeConnectionCount' => 0,
            'connectionCount' => count($this->connections),
            'queries' => array(),
            'queryCount' => 0,
            'queryTime' => 0,
        );

        // If we haven't made any database connections, don't do any processing
        if (count($this->connections))
        {
            foreach ($this->connections as $connection)
            {
                $connection = Capsule::connection($connection->getName());

                // Log an active connection as long as we have queries
                if (count($connection->getQueryLog())) ++$this->data['activeConnectionCount'];

                // Group queries by connection, add the over count and time to totals
                $this->data['queries'][$connection->getName()] = $connection->getQueryLog();
                $this->data['queryCount'] += count($connection->getQueryLog());

                foreach ($connection->getQueryLog() as $query)
                {
                    $this->data['queryTime'] += $query['time'];
                }
            }
        }
    }

    /**
     * Return the name of the data collector
     *
     * @return string
     */
    public function getName()
    {
        return 'database';
    }

    /**
     * Return the number of connections which were used
     *
     * @return int
     */
    public function getActiveConnectionCount()
    {
        return $this->data['activeConnectionCount'];
    }

    /**
     * Return the number of connections made to the database
     *
     * @return int
     */
    public function getConnectionCount()
    {
        return $this->data['connectionCount'];
    }

    /**
     * Return a list of all queries made grouped by connection
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->data['queries'];
    }

    /**
     * Return a count of all queries made
     *
     * @return int
     */
    public function getQueryCount()
    {
        return $this->data['queryCount'];
    }

    /**
     * Return the total time in milliseconds for all queries to run
     *
     * @return double
     */
    public function getQueryTime()
    {
        return $this->data['queryTime'];
    }
}
