<?php

namespace App;

use App\AccountDetail;
use Illuminate\Database\Eloquent\Model;


class Account extends Model
{
    const TYPE_MAIN = 'M';
    const TYPE_SUB = 'S';
    const TYPE_SUBSIDIARY = 'SB';
 
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
    public function childrenRecursive()
	{
	   return $this->children()->with('childrenRecursive');
	}

    public function account_detail(){
        return $this->hasMany(AccountDetail::class);
    }
}
