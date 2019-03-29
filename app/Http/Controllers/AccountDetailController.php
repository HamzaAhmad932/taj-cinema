<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;

class AccountDetailController extends Controller
{
    //
    private $baseURL = 'vendor.voyager.account-detail';

    public function index(){

    	$accounts = Account::where('type', 'SB')->get();

    	return view($this->baseURL.'.add', compact('accounts'));
    }
}
