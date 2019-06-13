<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountDetail;
use Illuminate\Http\Request;

class AccountDetailController extends Controller
{
    //
    private $baseURL = 'vendor.voyager.account-detail';

    public function index(){

    	$accounts = Account::where('type', 'SB')->get();

    	return view($this->baseURL.'.add', compact('accounts'));
    }

    public function jvEntry(Request $request) {

    	foreach($request->data as $ac){

    		$ad = new AccountDetail();
    		$ad->account_id = $ac['account_id'];
    		$ad->date = $ac['date'];
    		$ad->description = $ac['description'];
    		$ad->debit = $ac['debit'];
    		$ad->credit = $ac['credit'];

    		$ad->save();
    	}

    	return response()->json([
    		'status'=> true,
    		'status_code'=>200,
    		'message'=> 'Entries Recorded Successfully.'
    	]);
    }
}
