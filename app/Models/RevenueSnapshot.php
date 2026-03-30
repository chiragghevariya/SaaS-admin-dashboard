<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueSnapshot extends Model
{
    protected $fillable = ['tenant_id', 'month', 'mrr', 'new_customers', 'churned_customers'];

    protected $casts = [
        'mrr' => 'decimal:2',
        'new_customers' => 'integer',
        'churned_customers' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
