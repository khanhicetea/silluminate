## Silluminate

Use the [Illuminate Database](https://github.com/illuminate/database) and [Eloquent ORM](http://laravel.com/docs/eloquent) from Laravel in your Silex application.

Copyright (c) 2014 [Scott Dawson](https://github.com/sjdaws). Released under the [MIT licence](https://github.com/sjdaws/silluminate/blob/master/licence).

#### Documentation

* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)
* [Profiling](#profiling)

<a name="installation"></a>
#### Installation

The first thing you need to do is add `sjdaws/silluminate` as a requirement to `composer.json`:

```javascript
{
    "require": {
        "sjdaws/silluminate": "1.*"
    }
}
```

Update your packages with `composer update` and you're ready to go.

<a name="configuration"></a>
#### Configuration

The database can be configured using the same options, and even the same [configuration file](https://github.com/laravel/laravel/blob/master/app/config/database.php) you would normally use with Laravel. Some sane settings are used by default:

```php
    array(

        'fetch' => PDO::FETCH_CLASS,

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
```

All of these configuration values can be overridden at run time. If no configuration is specified the default values will be used.

<a name="usage"></a>
#### Usage

Usage is simple, just pull the database service provider into your application and set up configuration via `$app['db.config']`.

```php
    <?php

    use Silex\Application;
    use Sjdaws\Silluminate\DatabaseServiceProvider;

    ...

    // You could define an array or pull in an array from a file here
    $app['db.config'] = array(

        'default' => 'main',

        'connections' => array(

            'one' => array(
                'driver' => 'pgsql',
                'database' => 'database_one',
                'password' => 'rootpassword'
            ),

            'main' => array(
                'database' => 'something_else',
                'host'     => '10.4.20.3',
                'username' => 'silex',
                'password' => 'silex',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci'
            )

        )

    );

    // Then load the service provider
    $app->register(new DatabaseServiceProvider());

    return $app;
```

You can now access the database in the same way you would via Laravel.

**Our model**:
```php
    <?php

    namespace App\Models;

    use Sjdaws\Silluminate\Database\Model;
    // or
    use Illuminate\Database\Eloquent\Model;

    class MyModel extends Model
    {
        protected $fillable = array('name', 'value');

        public function details()
        {
            return $this->hasMany('App\Models\MyModelDetails');
        }
    }
```

**Our app**:
```php
    <?php

    use App\Models\MyModel;
    // or
    use Illuminate\Database\Capsule\Manager as DB;

    $app->get('/hello/{name}', function ($name) use ($app)
    {
        $name1 = DB::connection('one')->table('my_models')->where('name', $name)->first();

        // or
        // Use the default database (which is 'main' in our configuration)
        $name = MyModel::with('details')->where('name', $name)->first();

        // or using query builder on our non-default database
        $name = DB::connection('one')->table('my_models')->where('name', $name)->first();

        return $name;
    });
```

<a name="profiling"></a>
#### Profiling

Silluminate integrates fully with [Silex Web Profiler](https://github.com/silexphp/Silex-WebProfiler). From here you can see:

* How any databases were connected to
* How many of these connections were actually used
* How many queries were run overall
* How long the queries took overall

You can then drill down to individual connections and/or queries to debug them further in much the same way you would with the Doctrine Bundle using the Symfony Web Profiler.

To enable the web profiler you need to load the service provider in `app_dev.php` or similar:
```php
    <?php

    use Sjdaws\Silluminate\DatabaseDataCollectorServiceProvider;

    ...

    $app->register(new DatabaseDataCollectorServiceProvider());
```

**Mini and full profiler views**:
<p align="center">
    <img src="https://sjdaws.github.io/silluminate/mini-profiler.png">
</p>
<p align="center">
    <img src="https://sjdaws.github.io/silluminate/full-profiler.png">
</p>
