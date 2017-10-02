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

	public function getOverviewInfos(){

		$accounts = $this->getAccounts();

        $accountsInfos = array();

        // Pour tous les comptes
        foreach($accounts as $slug => $account){

            $accountsInfos[$slug]['name'] = $account;
            $accountsInfos[$slug]['color'] = 'rgb(' . rand(0, 255) . "," . rand(0, 255) . "," . rand(0, 255) . ')';
            
            // Requete sur les totaux mensuelles
            $sumsTransaction = DB::table('transaction as t')
                ->join('account', 't.account_id', '=', 'account.id')
                ->select(DB::raw("DATE_FORMAT(t.`date`,'%Y-%m') AS month,
                    (select sum(transaction.payed_amount) from `transaction` inner join `account` on `account`.`id` = `transaction`.`account_id` 
                    where  DATE_FORMAT(`date`,'%Y-%m') <= month and `account`.`slug` = '".$slug."') AS amount"))
                ->where('account.slug', '=', $slug)
                ->where('t.user_id', '=', Auth::user()->id)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

           // Requete sur les totaux des transfert entrant
            $sumsTransferIn = DB::table('transfer as t')
                ->join('account as to', 't.to_account_id', '=', 'to.id')
                ->select(DB::raw("DATE_FORMAT(t.`date`,'%Y-%m') AS month,
                    (select sum(transfer.amount) from `transfer`
                    where  DATE_FORMAT(`date`,'%Y-%m') <= month) AS amount"))
                ->where('to.user_id', '=', Auth::user()->id)
                ->where('to.slug', '=', $slug)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Requete sur les totaux des transfert sortant
            $sumsTransferOut = DB::table('transfer as t')
                ->join('account as from', 't.from_account_id', '=', 'from.id')
                ->select(DB::raw("DATE_FORMAT(t.`date`,'%Y-%m') AS month,
                    (select sum(transfer.amount) from `transfer`
                     where  DATE_FORMAT(`date`,'%Y-%m') <= month) AS amount"))
                ->where('from.user_id', '=', Auth::user()->id)
                ->where('from.slug', '=', $slug)
                ->groupBy('month')
                ->orderBy('month')
                ->get();
           

            // Formatte et stock les valeurs récupérées
            $oneMonth = 60 * 60 * 24 * 30;
            $oneYear = 60 * 60 * 24 * 365;
            $currentMonth = time() - $oneYear;
            $i = 0;
            do{
                
                $done = false;
                $accountsInfos[$slug]['monthly'][$i] = 0;
                foreach($sumsTransaction as $sumTransaction){
                    if($sumTransaction->month == date('Y-m', $currentMonth)){
                        $accountsInfos[$slug]['monthly'][$i] = $sumTransaction->amount;
                        $done = true;
                        break;
                    }
                }

                foreach($sumsTransferIn as $sumTransferIn){
                    if($sumTransferIn->month == date('Y-m', $currentMonth)){
                        $accountsInfos[$slug]['monthly'][$i] += $sumTransferIn->amount;
                        $done = true;
                        break;
                    }
                }

                foreach($sumsTransferOut as $sumTransferOut){
                    if($sumTransferOut->month == date('Y-m', $currentMonth)){
                        $accountsInfos[$slug]['monthly'][$i] += $sumTransferOut->amount;
                        $done = true;
                        break;
                    }
                }

                if(!$done){
                    if($i > 0){
                        $accountsInfos[$slug]['monthly'][$i] = $accountsInfos[$slug]['monthly'][$i - 1];  
                    }else{
                        $accountsInfos[$slug]['monthly'][$i] = null;        
                    }
                }

                $currentMonth += $oneMonth;
                $i++;
            }while(date('Y-m', $currentMonth) != date('Y-m'));

        }

        $categoryInfos = array(
            'category' => array(),
            'subcategory' => array()
        );

        // Category sum
        $results = DB::table('transaction')
            ->join('account', 'transaction.account_id', '=', 'account.id')
            ->join('category', 'transaction.category_id', '=', 'category.id')
            ->select(DB::raw('SUM(transaction.payed_amount) as sum, category.name as name, category.slug as slug'))
            ->where('transaction.user_id', '=', Auth::user()->id)
            ->groupBy('transaction.category_id')
            ->orderBy('category.slug')
            ->get();


        foreach($results as $result){
            if($result->sum < 0){
                $categoryInfos['category'][$result->slug] = [
                    'sum' => abs($result->sum),
                    'name' => $result->name,
                    'color' => 'rgb(' . rand(0, 255) . "," . rand(0, 255) . "," . rand(0, 255) . ')'
                ];
            }
        }

        // Subcategory sum
        $results = DB::table('transaction')
            ->join('account', 'transaction.account_id', '=', 'account.id')
            ->join('category', 'transaction.category_id', '=', 'category.id')
            ->leftJoin('subcategory', 'transaction.subcategory_id', '=', 'subcategory.id')
            ->select(DB::raw('SUM(transaction.payed_amount) as sum, subcategory.name as name, subcategory.slug as slug, category.slug as categorySlug, category.name as categoryName'))
            ->where('transaction.user_id', '=', Auth::user()->id)
            ->groupBy(DB::raw('transaction.category_id, transaction.subcategory_id'))
            ->orderBy('category.slug')
            ->get();


        foreach($results as $result){
            if($result->sum < 0){
                if(is_null($result->slug)){
                    if(array_key_exists($result->categorySlug, $categoryInfos['subcategory'])){
                        $categoryInfos['subcategory'][$result->categorySlug] += [
                            'sum' => abs($result->sum),
                            'name' => $result->categoryName,
                            'color' => 'rgb(' . rand(0, 255) . "," . rand(0, 255) . "," . rand(0, 255) . ')'
                        ];
                    }else{
                        $categoryInfos['subcategory'][$result->categorySlug] = [
                            'sum' => abs($result->sum),
                            'name' => $result->categoryName,
                            'color' => 'rgb(' . rand(0, 255) . "," . rand(0, 255) . "," . rand(0, 255) . ')'
                        ];
                    }
                }else{
                    if(array_key_exists($result->categorySlug, $categoryInfos['subcategory'])){
                        $categoryInfos['subcategory'][$result->slug] += [
                            'sum' => abs($result->sum),
                            'name' => $result->name,
                            'color' => 'rgb(' . rand(0, 255) . "," . rand(0, 255) . "," . rand(0, 255) . ')'
                        ];
                    }else{
                        $categoryInfos['subcategory'][$result->slug] = [
                            'sum' => abs($result->sum),
                            'name' => $result->name,
                            'color' => 'rgb(' . rand(0, 255) . "," . rand(0, 255) . "," . rand(0, 255) . ')'
                        ];
                    }
                }
            }
        }

        return array(
        	'accountsInfos' => $accountsInfos,
            'categoryInfos' => $categoryInfos
        );

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
