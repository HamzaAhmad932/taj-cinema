<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    //
    private $baseURL = 'vendor.voyager.ledger';

    public function index(){

    	return view($this->baseURL.'.browse');
    }
}
