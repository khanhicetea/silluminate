<?php

namespace Sjdaws\Silluminate;

use Silex\Application;
use Silex\ServiceProviderInterface;
use PDO;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * The configuration used
     *
     * @var array
     */
    private $config = array();

    /**
     * Register Illuminate with the application
     *
     * @param array $config
     * @param Application $app
     * @return void
     */
    public function register(Application $app)
    {
        // Set some sane defaults
        $defaults = array(

            'boot' => true,
            'eloquent' => true,
            'fetch' => PDO::FETCH_CLASS,
            'global' => true,
            'default' => 'default',
            'connections' => array(

                'default' => array(
                    'driver' => 'mysql',
                    'host' => 'localhost',
                    'database' => 'silex',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => null,
                )

            )

        );

        // Merge in any passed configuration
        $this->config = array_merge($defaults, $app['db.config']);

        // Make sure all connections have all required fields
        if (isset($this->config['connections']))
        {
            foreach ($this->config['connections'] as $index => $connection)
            {
                $this->config['connections'][$index] = array_merge($defaults['connections']['default'], $connection);
            }
        }

        // Create a container for the database pool
        $app['db.container'] = $app->share(function() {
            return new Container;
        });

        // Create the connections to the datbase
        $app['db'] = $app->share(function() use ($app)
        {
            $db = new Capsule($app['db.container']);

            // Set PDO fetch mode as per configuration
            $db->setFetchMode($this->config['fetch']);

            // Set as global, use eloquent as per configuration
            if ($this->config['eloquent']) $db->bootEloquent();
            if ($this->config['global']) $db->setAsGlobal();

            // Set up the event dispatcher if we have it available
            if (class_exists('Illuminate\Events\Dispatcher')) $db->setEventDispatcher(new Dispatcher($app['db.container']));

            // Initialise each connection
            foreach($this->config['connections'] as $connection => $options)
            {
                $db->addConnection(array_replace($this->config['connections'][$this->config['default']], $options), $connection);

                // If we're in debug mode we're going to want to use the query log, otherwise leave it disabled
                // to speed up queries and reduce memory usage
                if ($app['debug']) $db->getConnection($connection)->enableQueryLog();
            }

            // Finally set default connection
            $db->getDatabaseManager()->setDefaultConnection($this->config['default']);

            return $db;
        });
    }

    /**
     * Bootstrap application events
     *
     * @param Application $app
     * @return void
     */
    public function boot(Application $app)
    {
        // Bootstrap if the config says we should
        if ($this->config['boot'])
        {
            $app->before(function() use($app)
            {
                $app['db'];
            },
            Application::EARLY_EVENT);
        }
    }
}
