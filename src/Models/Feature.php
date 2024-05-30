<?php

namespace Nakanakaii\LaravelSubscriptions\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class);
    }

    public function activate()
    {
        $this->is_active = true;
    }

    public function deactivate()
    {
        $this->is_active = false;
    }
}
