<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'amount',
        'currency',
        'status',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /** Amount in dollars (for display). */
    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }
}
