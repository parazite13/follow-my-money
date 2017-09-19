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
