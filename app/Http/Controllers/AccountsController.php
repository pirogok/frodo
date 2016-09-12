<?php
/**
 * class AccountsController extends Controller
 *
 * изменения в системном файле стр. 90
 * vendor\laravel\lumen-framework\src\Routing\ProvidesConvenienceMethods.php
 * 
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Twitter;
use Illuminate\Http\Response;

class AccountsController extends Controller
{
    /**
     * public function listAccounts()
	 *
	 * List account.
     *
     * @return \Illuminate\Http\Response
     */
    public function listAccounts()
    {
		$account = new Account;

		return (new Response($account->listAccounts(), 200))->header('Content-Type', 'json');
    }

    /**
     * public function createAccount(Request $request)
	 *
	 * Create account.
	 * screen_name обязательный параметр, по account_id получить данные с твиттера не получилось
	 * добавил screen_name 
	 * подробно здесь
	 * https://dev.twitter.com/rest/reference/get/users/show
     *
	 * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function createAccount(Request $request)
    {
		$this->validate($request, [
			'refresh_interval' => 'required|string',
			'account_id' => 'required|string',
			'screen_name' => 'required|string'
		]);

		$account = new Account;
		return (new Response($account->createAccount($request->all()), 200))->header('Content-Type', 'json');
    }

    /**
     * public function editAccount( Request $request, $account_id)
     *
	 * Edit account
	 *
     * @param  Request $request
	 * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editAccount( Request $request, $account_id)
    {
		$this->validate($request, [
			'refresh_interval' => 'required|string',
		]);

		$account = new Account;
		return (new Response($account->editAccount($account_id, $request->all()), 200))->header('Content-Type', 'json');
    }

    /**
     * public function deleteAccount($account_id)
	 *
	 * Delete account
     *
     * @param  int  $account_id
     * @return \Illuminate\Http\Response
     */
    public function deleteAccount($account_id)
    {

		$account = new Account;

		$arr = $account->deleteAccount($account_id);
		return (new Response($arr, 200))->header('Content-Type', 'json');
    }

    /**
     * public function listPosts($account_id, $limit = 100)
	 *
	 * List posts
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $limit
     * @return \Illuminate\Http\Response
     */
    public function listPosts($account_id, $limit = 100)
    {
		$account = new Account;
		$arr = $account->listPosts($account_id, $limit);

		return (new Response($arr, 200))->header('Content-Type', 'json');
	}
	
    /**
     * public function getTwitterPosts($limit = 100)
	 *
	 * Get twitter posts
     *
     * @param  int  $limit
     * @return void
     */
    public function getTwitterPosts($limit = 100)
    {
		$account = new Account;
		$accounts = $account->getHotAccount();

		if ($accounts) {
			$twitter = new Twitter;
			
			foreach ($accounts as $v) {
				$twitts = $twitter->getTwitterPosts($v->account_id, $v->screen_name, $limit);
							
				$account->insertTwitts($twitts, $v->account_id);
			}
		}
	}
}
