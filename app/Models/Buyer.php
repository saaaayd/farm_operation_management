<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'type',
        'status',
        'payment_terms',
        'credit_limit',
        'notes',
        'user_id',
        'contact_info',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'buyer_id');
    }

    /**
     * The farmer who owns the buyer record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
