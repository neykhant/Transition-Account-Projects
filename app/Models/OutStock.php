<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutStock extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'category_id', 'user_id', 'acceptor', 'quantity'];

    public function items()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function stocks()
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }
}