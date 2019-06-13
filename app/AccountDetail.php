<?php

namespace App;

use App\Account;
use Illuminate\Database\Eloquent\Model;


class AccountDetail extends Model
{
    public $fillable = [
        'date',
        'account_id',
        'description',
        'debit',
        'credit',
        'show_id'
    ];

    public function account(){
    	return $this->belongsTo(Account::class);
    }
    
}
