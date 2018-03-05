<?php

namespace romanzipp\ValidatorPizza\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ValidatedDomain extends Model
{
    protected $fillable = [
        'domain',
        'mx',
        'disposable',
        'hits',
        'last_queried',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('validator-pizza.checks_table'));
    }
}
