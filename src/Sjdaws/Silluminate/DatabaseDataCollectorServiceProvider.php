<?php

namespace Sjdaws\Silluminate;

use Silex\ServiceProviderInterface;
use Silex\Application;
use Sjdaws\Silluminate\DataCollector\DatabaseDataCollector;
use Sjdaws\Silluminate\Twig\DatabaseExtension;
use Sjdaws\Silluminate\Twig\DoctrineExtension;
use Symfony\Bridge\Twig\Extension\YamlExtension;

class DatabaseDataCollectorServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the data collector with the application
     *
     * @param Application $app
     * @return void
     */
    public function register(Application $app)
    {
        if (isset($app['data_collectors']['silluminate'])) return;

        // Register with application data collectors
        $app['data_collectors'] = array_merge($app['data_collectors'], array(
            'silluminate' => $app->share(function() use ($app)
            {
                return new DatabaseDataCollector($app['db']);
            }),
        ));

        // Add template for profiler
        $app['data_collector.templates'] = array_merge($app['data_collector.templates'], array(
            array('database', '@Silluminate/database.html.twig'),
        ));

        // Register silluminate view path with twig
        $app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem', function($loader, $app)
        {
            $loader->addPath(realpath(__DIR__ . '/../../../views/'), 'Silluminate');
            return $loader;
        }));

        $app['twig'] = $app->share($app->extend('twig', function($twig, $app)
        {
            $twig->addExtension(new DatabaseExtension($app));
            $twig->addExtension(new YamlExtension($app));
            return $twig;
        }));
    }

    /**
     * Bootstrap application events
     *
     * @param Application $app
     * @return void
     */
    public function boot(Application $app) {}
}
