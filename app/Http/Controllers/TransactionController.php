<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\AddTransactionRequest;
use Illuminate\Support\Facades\Redirect;
use App\Http\Utility;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{

	public function __construct(){
    	$this->middleware('auth');
    }

    public function addTransaction(AddTransactionRequest $request){

    	$categoryId = DB::table('category')
			->where('slug', '=', $request->get('category'))
			->first()
			->id;

		$accountId = DB::table('account')
			->where('slug', '=', $request->get('account'))
			->first()
			->id;

		if($request->get('subcategory') != ""){
			$subCategoryId = DB::table('subcategory')
				->where('slug', '=', $request->get('subcategory'))
				->first()
				->id;
		}else{
			$subCategoryId = null;
		}

        if($request->get('check_refund') == 'check_refund'){
            $real_amount = 0;
        }else{
            $real_amount = $request->get('real_amount');
        }
    	
    	DB::table('transaction')->insert([
    		'user_id' => Auth::user()->id,
    		'date' => $request->get('date'),
    		'payed_amount' => $request->get('payed_amount'),
    		'real_amount' => $real_amount,
    		'description' => $request->get('description'),
    		'details' => $request->get('detail'),
    		'memo' => $request->get('memo'),
    		'category_id' => $categoryId,
    		'subcategory_id' => $subCategoryId,
    		'account_id' => $accountId
     	]);

     	return Redirect::route('my-profile/transaction')->with('alert-success', 'The transaction has been added');

    }
}
