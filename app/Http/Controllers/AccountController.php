<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\AddAccountRequest;
use Illuminate\Support\Facades\Redirect;
use App\Http\Utility;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{

	public function __construct(){
    	$this->middleware('auth');
    }
	
    public function addAccount(AddAccountRequest $request){

    	$account = DB::table('account')
			->where('name', '=', Utility::slugify($request->get('account')))
			->first();

		if(is_null($account)){
			DB::table('account')->insert([
				'name' => $request->get('account'),
				'slug' => Utility::slugify($request->get('account')),
				'user_id' => Auth::user()->id
			]);
			return Redirect::route('my-profile/account')->with('alert-success', 'The account has been added');
		}else{
			return Redirect::route('my-profile/account')->with('alert-danger', 'The account already exists');
		}
    }

    public function removeAccount(Request $request){

    	DB::table('account')->where(
			'slug', $request->get('account')
		)->delete();
		return Redirect::route('my-profile/account')->with('alert-success', 'The account has been removed');
    }

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
}
