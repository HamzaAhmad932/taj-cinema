<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountDetailController extends Controller
{
    //
    private $baseURL = 'vendor.voyager.account-detail';

    public function index(){

    	return view($this->baseURL.'.add');
    }
}
