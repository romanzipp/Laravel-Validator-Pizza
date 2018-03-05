# Laravel Validator.Pizza

A Laravel Wrapper for the [laravel.pizza](https://www.validator.pizza) disposable email API made by [@tompec](https://github.com/tompec).

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
