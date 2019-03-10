<?php

namespace App;

use App\Show;
use App\Distributor;
use Illuminate\Database\Eloquent\Model;


class ShowClosed extends Model
{

	public $table = 'show_closed';

	public function show(){
		return $this->belongsTo(Show::class);
	}

	public function distributor(){
		return $this->belongsTo(Distributor::class);
	}
    
}
