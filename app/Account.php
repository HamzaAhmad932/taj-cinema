<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Account extends Model
{
 
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
}
