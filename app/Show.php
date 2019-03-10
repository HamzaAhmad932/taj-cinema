<?php

namespace App;

use App\Movie;
use App\Screen;
use Illuminate\Database\Eloquent\Model;


class Show extends Model
{

	public function movie(){
		return $this->belongsTo(Movie::class);
	}
    
    public function screen(){
    	return $this->belongsTo(Screen::class);
    }
}
