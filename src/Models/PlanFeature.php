<?php

namespace Nakanakaii\LaravelSubscriptions\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanFeature extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'limits' => 'array',
    ];

    public function plan(): HasOne
    {
        return $this->hasOne(Plan::class);
    }

    public function feature(): HasOne
    {
        return $this->hasOne(Feature::class);
    }
}
