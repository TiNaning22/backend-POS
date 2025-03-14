<?php

namespace App\Models;

use App\Models\Outlet;
use App\Models\User;
use App\Models\Customer;
use App\Models\TransactionItems;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $fillable = ['customer_id', 'user_id', 'outlet_id', 'transaction_item_id','total', 'subtotal', 'ppn', 'nomor_invoice', 'payment_method'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Relasi dengan Toko
    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id', 'id');
    }

    // Relasi dengan Transaction_Items
    public function items()
    {
        return $this->belongsTo(TransactionItems::class, 'transaction_item_id', 'id');
    }

    const PPN_RATE = 0.12;

    public function calculateSubtotal()
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            // Make sure you're using the correct field name for price
            $subtotal += $item->quantity * $item->harga;
        }
        return $subtotal;
    }

    // Calculate PPN based on subtotal
    public function calculatePPN()
    {
        return $this->calculateSubtotal() * self::PPN_RATE;
    }

    // Calculate total (subtotal + PPN)
    public function calculateTotal()
    {
        return $this->calculateSubtotal() + $this->calculatePPN();
    }

    // Update transaction amounts
    public function updateAmounts()
    {
        $this->subtotal = $this->calculateSubtotal();
        $this->ppn = $this->calculatePPN();
        $this->total = $this->calculateTotal();
        $this->save();
    }
}
