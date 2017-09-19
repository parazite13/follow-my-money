<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\AddSubCategoryRequest;
use Illuminate\Support\Facades\Redirect;
use App\Http\Utility;

class SubCategoryController extends Controller
{


	public function __construct(){
    	$this->middleware('auth');
    }
	
	public function addSubCategory(AddSubCategoryRequest $request){

		$categoryId = DB::table('category')
			->where('slug', '=', $request->get('category'))
			->first()
			->id;

		$subcategory = DB::table('subcategory')
			->where('slug', '=', Utility::slugify($request->get('subcategory')))
			->first();

		if(is_null($subcategory)){
			DB::table('subcategory')->insert([
				'category_id' => $categoryId,
				'name' => $request->get('subcategory'),
				'slug' => Utility::slugify($request->get('subcategory'))
			]);
			return Redirect::route('my-profile/category')->with('alert-success', 'The sub-category has been added');
		}else{
			return Redirect::route('my-profile/category')->with('alert-danger', 'The sub-category already exists');
		}
	}

	public function removeSubCategory(Request $request){

		DB::table('subcategory')
			->join('category', 'subcategory.category_id', '=', 'category.id')
			->where('subcategory.slug', Utility::slugify($request->get('subcategory')))
			->where('category.slug', Utility::slugify($request->get('category')))
			->delete();
		return Redirect::route('my-profile/category')->with('alert-success', 'The sub-category has been removed');
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
}
