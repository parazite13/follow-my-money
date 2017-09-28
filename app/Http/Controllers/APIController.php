<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Utility;
use App\Http\Requests\AddTransactionRequest;
use Illuminate\Support\Facades\Auth;

class APIController extends Controller
{
	/*
    public function __construct(){
    	$this->middleware('jwt.auth');
    }
    */

    public function getAccounts(){

		$accounts = array();
	
		$results = DB::table('account')
			->where('user_id', '=', Auth::user()->id)
			->get();

		foreach($results as $result){
			$accounts[$result->slug] = $result->name;
		}

		asort($accounts);

		return $accounts;
	}

	public function getCategories(){

		$categories = array();
		$results = DB::table('category')->get();
        foreach($results as $result){
            $categories[$result->slug] = $result->name;
        }

        asort($categories);

        return $categories;
	}

	public function getSubCategories($parentCategory = null){

		$subcategories = array();

		if($parentCategory == null){
			$results = DB::table('subcategory')->get();
			foreach($results as $result){
				$subcategories[$result->slug] = $result->name;
			}
		}else{
			$results = DB::table('subcategory')
				->join('category', 'subcategory.category_id', '=', 'category.id')
				->where('category.slug', '=', $parentCategory)
				->get(['subcategory.name', 'subcategory.slug']);

			foreach($results as $result){
				$subcategories[$result->slug] = $result->name;
			}
		}

		asort($subcategories);

		return $subcategories;
	}

	public function getAccountsAmount(){

		$accounts = $this->getAccounts();

		$accountsInfos = array();
        $total = array('payed_amount' => 0, 'real_amount' => 0);

        // Pour tous les comptes
        foreach($accounts as $slug => $account){

            $accountsInfos[$slug]['name'] = $account;

            // On récupere toutes les transactions
        	$results = DB::table('transaction')
        		->join('category', 'transaction.category_id', '=', 'category.id')
        		->leftJoin('subcategory', 'transaction.subcategory_id', '=', 'subcategory.id')
        		->join('account', 'transaction.account_id', '=', 'account.id')
        		->select('transaction.id', 'transaction.date', 'transaction.payed_amount', 'transaction.real_amount', 'transaction.description', 'transaction.details', 'transaction.memo', 'category.name as category','subcategory.name as subcategory')
        		->where('transaction.user_id', '=', Auth::user()->id)
        		->where('account.slug', '=', $slug)
        		->get();

        	$accountsInfos[$slug]['transactions'] = $results;

            // On récupere tous les transferts pour lesquelles ce compte intervient
            $results = DB::table('transfer')
                ->join('account as from', 'transfer.from_account_id', '=', 'from.id')
                ->join('account as to', 'transfer.to_account_id', '=', 'to.id')
                ->select('transfer.id', 'transfer.date', 'transfer.amount', 'transfer.description', 'from.slug as from', 'to.slug as to', 'from.name as fromName', 'to.name as toName')
                ->where('from.user_id', '=', Auth::user()->id)
                ->where('to.user_id', '=', Auth::user()->id)
                ->where(function($query) use ($slug){
                    $query->where('from.slug', '=', $slug);
                    $query->orWhere('to.slug', '=', $slug);
                })
                ->get();

            $accountsInfos[$slug]['transfer'] = $results;

            // On calcul la somme des transactions
            $results = DB::table('transaction')
                ->join('account', 'transaction.account_id', '=', 'account.id')
                ->where('transaction.user_id', '=', Auth::user()->id)
                ->where('account.slug', '=', $slug)
                ->sum('payed_amount');

            // On ajoute la somme des transfert entrant
            $results += DB::table('transfer')
                ->join('account as to', 'transfer.to_account_id', '=', 'to.id')
                ->where('to.user_id', '=', Auth::user()->id)
                ->where('to.slug', '=', $slug)
                ->sum('amount');

            // On retire la somme des transfert sortant
            $results -= DB::table('transfer')
                ->join('account as from', 'transfer.from_account_id', '=', 'from.id')
                ->where('from.user_id', '=', Auth::user()->id)
                ->where('from.slug', '=', $slug)
                ->sum('amount');

            $accountsInfos[$slug]['sum'] = $results;

            $total['payed_amount'] += $results;

        }

        $results = DB::table('transaction')
                ->select(DB::raw('SUM(payed_amount) as payed_amount, SUM(real_amount) as real_amount'))
                ->where('user_id', '=', Auth::user()->id)
                ->whereNotNull('real_amount')
                ->get();            

        $total['real_amount'] = $results[0]->real_amount - $results[0]->payed_amount;

        return array(
        	'total' => $total,
        	'accounts' => $accountsInfos
        );

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
    	
    	DB::table('transaction')->insert([
    		'user_id' => Auth::user()->id,
    		'date' => $request->get('date'),
    		'payed_amount' => $request->get('payed_amount'),
    		'real_amount' => $request->get('real_amount'),
    		'description' => $request->get('description'),
    		'details' => $request->get('detail'),
    		'memo' => $request->get('memo'),
    		'category_id' => $categoryId,
    		'subcategory_id' => $subCategoryId,
    		'account_id' => $accountId
     	]);

		
	}
}
