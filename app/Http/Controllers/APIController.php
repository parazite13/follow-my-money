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

	public function getOverviewInfos($start=null, $end=null){

        if($end === null){
            $end = date('Y-m');
        }

        if($start === null){
            $explode = explode('-', $end);
            $start = ((intval($explode[0]) - 1) . '-' . $explode[1]);
        }

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
                    where DATE_FORMAT(`date`,'%Y-%m') <= month and `account`.`slug` = '".$slug."') AS amount"))
                ->where('account.slug', '=', $slug)
                ->where('t.user_id', '=', Auth::user()->id)
                ->where(DB::raw("DATE_FORMAT(t.`date`,'%Y-%m')"), '>=', $start)
                ->where(DB::raw("DATE_FORMAT(t.`date`,'%Y-%m')"), '<=', $end)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Requete sur les transferts entrants
            $sumsTransferIn = DB::table('transfer as t')
                ->join('account as to', 't.to_account_id', '=', 'to.id')
                ->select(DB::raw("DATE_FORMAT(t.`date`,'%Y-%m') AS month,
                    (select sum(transfer.amount) from `transfer` inner join `account` on `transfer`.`to_account_id` = `account`.`id` where DATE_FORMAT(`date`,'%Y-%m') <= month and `account`.`user_id` = 1 and `account`.`slug` = '".$slug."') AS amount"))
                ->where('to.user_id', '=', Auth::user()->id)
                ->where('to.slug', '=', $slug)
                ->where(DB::raw("DATE_FORMAT(t.`date`,'%Y-%m')"), '>=', $start)
                ->where(DB::raw("DATE_FORMAT(t.`date`,'%Y-%m')"), '<=', $end)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Requete sur les transferts sortants
            $sumsTransferOut = DB::table('transfer as t')
                ->join('account as from', 't.from_account_id', '=', 'from.id')
                ->select(DB::raw("DATE_FORMAT(t.`date`,'%Y-%m') AS month,
                    (select sum(transfer.amount) from `transfer` inner join `account` on `transfer`.`from_account_id` = `account`.`id` where DATE_FORMAT(`date`,'%Y-%m') <= month and `account`.`user_id` = 1 and `account`.`slug` = '".$slug."') AS amount"))
                ->where('from.user_id', '=', Auth::user()->id)
                ->where('from.slug', '=', $slug)
                ->where(DB::raw("DATE_FORMAT(t.`date`,'%Y-%m')"), '>=', $start)
                ->where(DB::raw("DATE_FORMAT(t.`date`,'%Y-%m')"), '<=', $end)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Formatte les données précedemment récupérées
            foreach($sumsTransaction as $sumTransaction){
                $accountsInfos[$slug]['monthly'][$sumTransaction->month] = $sumTransaction->amount;
            }

            $temp = array();
            foreach($sumsTransferIn as $sumTransferIn){
                $temp[$sumTransferIn->month] = $sumTransferIn->amount;
            }
            $sumsTransferIn = $temp;

            $temp = array();
            foreach($sumsTransferOut as $sumTransferOut){
                $temp[$sumTransferOut->month] = $sumTransferOut->amount;
            }
            $sumsTransferOut = $temp;


            $currentDate = $start;
            $previousDate = $start;

            // Pour tous les mois de la période
            while(strtotime($currentDate) <= strtotime($end)){

                // Si le mois n'a pas déja été rempli
                if(isset($accountsInfos[$slug]['monthly']) 
                    && !array_key_exists($currentDate, $accountsInfos[$slug]['monthly'])){

                    // Si la valeur précédente a été définie on la récupère pour ce mois-ci
                    if(isset($accountsInfos[$slug]['monthly'][$previousDate])
                        && is_numeric($accountsInfos[$slug]['monthly'][$previousDate])){
                        $accountsInfos[$slug]['monthly'][$currentDate] = $accountsInfos[$slug]['monthly'][$previousDate];

                    // Sinon on ne renseigne pas de valeur
                    }else{
                        $accountsInfos[$slug]['monthly'][$currentDate] = 0;
                    }
                }

                // De meme pour les transferts entrants
                if(!array_key_exists($currentDate, $sumsTransferIn)){

                    // Si la valeur précédente a été définie on la récupère pour ce mois-ci
                    if(isset($sumsTransferIn[$previousDate]) && is_numeric($sumsTransferIn[$previousDate])){
                        $sumsTransferIn[$currentDate] = $sumsTransferIn[$previousDate];

                    // Sinon on ne renseigne pas de valeur
                    }else{
                        $sumsTransferIn[$currentDate] = 0;
                    }
                }

                // De meme pour les transferts sortants
                if(!array_key_exists($currentDate, $sumsTransferOut)){

                    // Si la valeur précédente a été définie on la récupère pour ce mois-ci
                    if(isset($sumsTransferOut[$previousDate]) && is_numeric($sumsTransferOut[$previousDate])){
                        $sumsTransferOut[$currentDate] = $sumsTransferOut[$previousDate];

                    // Sinon on ne renseigne pas de valeur
                    }else{
                        $sumsTransferOut[$currentDate] = 0;
                    }
                }

                $previousDate = $currentDate;
                $currentDate = date("Y-m", strtotime("+1 month", strtotime($currentDate)));
            }

            // Ajoute ou retire les montants issu des transfers
            foreach($sumsTransferIn as $month => $amount){
                $accountsInfos[$slug]['monthly'][$month] += $amount;
            }

            foreach($sumsTransferOut as $month => $amount){
                $accountsInfos[$slug]['monthly'][$month] -= $amount;
            }

            // On tri le tableau par date
            if(isset($accountsInfos[$slug]['monthly'])){
                uksort($accountsInfos[$slug]['monthly'], function($a, $b){
                    if(strtotime($a) < strtotime($b)) return -1;
                    else if(strtotime($a) > strtotime($b)) return 1;
                    else return 0;
                });
            }

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
            ->where(DB::raw("DATE_FORMAT(`transaction`.`date`,'%Y-%m')"), '>=', $start)
            ->where(DB::raw("DATE_FORMAT(`transaction`.`date`,'%Y-%m')"), '<=', $end)
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
            ->where(DB::raw("DATE_FORMAT(`transaction`.`date`,'%Y-%m')"), '>=', $start)
            ->where(DB::raw("DATE_FORMAT(`transaction`.`date`,'%Y-%m')"), '<=', $end)
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
                ->orderBy('transaction.date', 'desc')
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

		if($request->get('account') == "espece"){
			$accountId = 0;
		}else{
			$accountId = DB::table('account')
				->where('slug', '=', $request->get('account'))
				->first()
				->id;
		}


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
