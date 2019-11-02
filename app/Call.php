<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    /**
     * The attribute that specifies which guard to use.
     *
     * @var string
     */
    protected $guard_name = 'api';
}
