<?php

namespace App;

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
    
}
