<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'name', 'min_minutes', 'max_minutes', 'sort_order'])]
class TimeBand extends Model {}
