<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\User;

class MyProfileController extends Controller
{

    public function __construct(){
    	$this->middleware('auth');
    }
  
    public function displayOverview(){

        $end = date('Y-m');
        $explode = explode('-', $end);
        $start = ((intval($explode[0]) - 1) . '-' . $explode[1]);

        $APIInfos = app('App\Http\Controllers\APIController')->getOverviewInfos($start, $end);

        return view('profile.overview')->with([
            'title' => 'overview',
            'accountsInfos' => $APIInfos['accountsInfos'],
            'categoryInfos' => $APIInfos['categoryInfos']
        ]);
    }

	public function displayDetails(){

		$APIInfos = app('App\Http\Controllers\APIController')->getAccountsAmount();

        return view('profile.details')->with([
        	'title' => 'details',
        	'accountsInfos' => $APIInfos['accounts'],
            'total' => $APIInfos['total']
        ]);
	}

    public function displayTransaction(){

        $accounts = app('App\Http\Controllers\AccountController')->getAccounts();
        $categories = app('App\Http\Controllers\CategoryController')->getCategories();
        $subcategories = app('App\Http\Controllers\SubCategoryController')->getSubCategories();

        return view('profile.transaction')->with([
            'title' => 'transaction',
            'accounts' => $accounts,
            'categories' => $categories,
            'subcategories' => $subcategories
        ]);
    }

	public function displayCategory(){

        $categories = app('App\Http\Controllers\CategoryController')->getCategories();
        $subcategories = app('App\Http\Controllers\SubCategoryController')->getSubCategories();

        return view('profile.category')->with([
            'title' => 'category', 
            'categories' => $categories,
            'subcategories' => $subcategories
        ]);
	}

    public function displayAccount(){

        $accounts = app('App\Http\Controllers\AccountController')->getAccounts();

        return view('profile.account')->with([
            'title' => 'account',
            'accounts' => $accounts,
        ]);
    }

}
