<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddCategoryRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Http\Utility;

class CategoryController extends Controller
{

	public function __construct(){
    	$this->middleware('auth');
    }
	
	public function addCategory(AddCategoryRequest $request){

		$category = DB::table('category')
			->where('name', '=', Utility::slugify($request->get('category')))
			->first();

		if(is_null($category)){
			DB::table('category')->insert([
				'name' => $request->get('category'),
				'slug' => Utility::slugify($request->get('category'))
			]);
			return Redirect::route('my-profile/category')->with('alert-success', 'The category has been added');
		}else{
			return Redirect::route('my-profile/category')->with('alert-danger', 'The category already exists');
		}
	}

	public function removeCategory(Request $request){

		DB::table('category')->where(
			'slug', $request->get('category')
		)->delete();
		return Redirect::route('my-profile/category')->with('alert-success', 'The category has been removed');
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
}
