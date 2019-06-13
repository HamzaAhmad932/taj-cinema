<?php

namespace App\Http\Controllers;

use App\Account;
use App\ShowClosed;
use App\AccountDetail;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    //
    private $baseURL = 'vendor.voyager.ledger';

    public function index(){
		
		$accounts = Account::where('type', Account::TYPE_SUBSIDIARY)->get();
		$shows = ShowClosed::select('show_id')->get();

    	return view($this->baseURL.'.browse', compact('accounts', 'shows'));
    }

    public function showLedger(Request $request){

		if($request->show_wise){
			$acc_d = AccountDetail::where('account_id', $request->account_id)
				->where('show_id', $request->show_id)
				->whereBetween('date', [$request->from_date, $request->to_date])
				->get();
		}
		else{
			$acc_d = AccountDetail::where('account_id', $request->account_id)
					->whereBetween('date', [$request->from_date, $request->to_date])
					->get();
		}


		$sum_debit = 0;
		$sum_credit = 0;
		$c_balance = 0;
		$helperBalance = 0;
		
		foreach($acc_d as $ac_d){
			$sum_debit += $ac_d->debit;
			$sum_credit += $ac_d->credit;

			$helperBalance =$helperBalance - $ac_d->debit + $ac_d->credit;
			$ac_d->balance = abs($helperBalance);
			$ac_d->account_name = $ac_d->account->account_name;
		}

		if($sum_credit > $sum_debit){
			$bal = $sum_credit - $sum_debit;
			$bal = abs($bal)." Cr.";
		}
		else if($sum_debit > $sum_credit){
			$bal = $sum_debit - $sum_credit;
			$bal = abs($bal)." Dr.";
		}
		else{
			$bal = "0";
		}

		$data = [
			'total_balance'=> $bal, 
			'enteries'=> $acc_d, 
			'total_debit'=> $sum_debit, 
			'total_credit'=> $sum_credit
		];
		
		return response()->json($data);
    }
}
