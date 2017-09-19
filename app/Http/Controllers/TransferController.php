<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\AddTransferRequest;
use Illuminate\Support\Facades\Redirect;
use App\Http\Utility;
use Illuminate\Support\Facades\Auth;

class TransferController extends Controller
{
    public function addTransfer(AddTransferRequest $request){

    	$fromAccountId = DB::table('account')
			->where('slug', '=', $request->get('from_account'))
			->first()
			->id;

		$toAccountId = DB::table('account')
			->where('slug', '=', $request->get('to_account'))
			->first()
			->id;

		DB::table('transfer')->insert([
    		'date' => $request->get('date'),
    		'amount' => $request->get('amount'),
    		'description' => $request->get('description'),
    		'from_account_id' => $fromAccountId,
    		'to_account_id' => $toAccountId
     	]);

     	return Redirect::route('my-profile/transaction')->with('alert-success', 'The transfer has been added');
    }
}
