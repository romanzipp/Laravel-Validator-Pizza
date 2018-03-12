# Laravel Validator.Pizza

[![Latest Stable Version](https://poser.pugx.org/romanzipp/laravel-validator-pizza/version)](https://packagist.org/packages/romanzipp/laravel-validator-pizza)
[![Total Downloads](https://poser.pugx.org/romanzipp/laravel-validator-pizza/downloads)](https://packagist.org/packages/romanzipp/laravel-validator-pizza)
[![License](https://poser.pugx.org/romanzipp/laravel-validator-pizza/license)](https://packagist.org/packages/romanzipp/laravel-validator-pizza)

A Laravel Wrapper for the [laravel.pizza](https://www.validator.pizza) disposable email API made by [@tompec](https://github.com/tompec).

## Features

- Query the Validator.Pizza API for disposable Emails & Domains
- Cache responses
- Store requested domains in database

## Installation

```
composer require romanzipp/laravel-validator-pizza
```

Or add `romanzipp/laravel-validator-pizza` to your `composer.json`

```
"romanzipp/laravel-validator-pizza": "*"
```

Run composer update to pull the latest version.

**If you use Laravel 5.5+ you are already done, otherwise continue:**

```php
romanzipp\ValidatorPizza\Providers\ValidatorPizzaProvider::class,
```

Add Service Provider to your app.php configuration file:

## Configuration

Copy configuration to config folder:

```
$ php artisan vendor:publish --provider=romanzipp\ValidatorPizza\Providers\ValidatorPizzaProvider
```

Run the migration:

```
$ php artisan migrate
```

Change the config to your desired settings:

```php
return [

    // Database storage enabled
    'store_checks' => true,

    // Database table name
    'checks_table' => 'validator_pizza',

    // Cache enabled (recommended)
    'cache_checks' => true,

    // Duration in minutes to keep the query in cache
    'cache_duration' => 30,

    // Determine which decision should be given if the rate limit is exceeded [allow / deny]
    'decision_rate_limit' => 'allow',

    // Determine which decision should be given if the domain has no MX DNS record [allow / deny]
    'decision_no_mx' => 'allow',
];
```

## Usage

#### Controller Validation

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function handleEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|disposable_pizza',
        ]);

        // ...
    }
}
```

#### Standalone

```php
$checker = new \romanzipp\ValidatorPizza\Checker;

// Validate Email
$validEmail = $checker->allowedEmail('ich@ich.wtf');

// Validate Domain
$validDomain = $checker->allowedDomain('ich.wtf');
```
