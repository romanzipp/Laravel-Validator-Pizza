<?php

namespace romanzipp\ValidatorPizza\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $hits
 * @property \Carbon\Carbon $last_queried
 */
class ValidatedDomain extends Model
{
    /**
     * @var string[]
     */
    protected $guarded = [];

    /**
     * @var string[]
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'last_queried',
    ];

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('validator-pizza.checks_table'));
    }
}
