<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Share extends Model
{
    public $fillable = ['staff', 'representative', 'distributor', 'collection', 'others'];


	/**
	*  Accessors
	*/
    public function getStaffAttribute($value) {
    	return json_decode($value);
    }
    public function getRepresentativeAttribute($value) {
    	return json_decode($value);
    }
    public function getDistributorAttribute($value) {
    	return json_decode($value);
    }
    public function getCollectionAttribute($value) {
    	return json_decode($value);
    }
    public function getOthersAttribute($value) {
    	return json_decode($value);
    }
	
	/**
	*  Mutators
	*/
    public function setStaffAttribute($value) {
    	$this->attributes['staff'] = json_encode($value);
    }
    public function setRepresentativeAttribute($value) {
    	$this->attributes['representative'] = json_encode($value);
    }
    public function setDistributorAttribute($value) {
    	$this->attributes['distributor'] = json_encode($value);
    }
    public function setCollectionAttribute($value) {
    	$this->attributes['collection'] = json_encode($value);
    }
    public function setOthersAttribute($value) {
    	$this->attributes['others'] = json_encode($value);
    }
}
