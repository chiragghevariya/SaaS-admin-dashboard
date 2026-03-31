<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = ['tenant_id', 'name', 'key', 'last_used_at'];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function maskedKey(): string
    {
        return substr($this->key, 0, 7) . '...' . substr($this->key, -4);
    }
}
