<?php
/**
 * class Account extends Model
 *
 * Model for Account
 * 
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Twitter;
use DB;

class Account extends Model
{	
    /**
     * private $twitter
	 *
	 * instance twitter
     */
	private $twitter = NULL;

    /**
     * private function instanceTwitter() 
	 *
	 * $this->twitter = new Twitter;
     *
     * @return void
     */
	private function instanceTwitter() 
	{
		return $this->twitter = new Twitter;
	}

    /**
     * public function createAccount($data)
	 *
	 * Create account
     *
	 * @param  obj $data
     * @return array
     */
	public function createAccount($data)
	{
		$this->instanceTwitter();

		$info = $this->twitter->getAccountInfo($data['account_id'], $data['screen_name']);

		if (!isset($info->id)) {
			return ['status' => 'error', 'error' => 'Ups!!! This account not found.'];
		}

		$result = DB::insert('INSERT IGNORE INTO accounts (refresh_interval, account_id, screen_name, title, updated_at) VALUES (?, ?, ?, ?, ?)',
			[$data['refresh_interval'], $data['account_id'], $info->screen_name, $info->description, '2000-01-01 01:01:01']);
		
		if ($result) {
			return ['status' => 'success', 'title' => $info->description];
		} else {
			return ['status' => 'error', 'error' => 'Ups!!! Not introduced in the base.'];
		}
	}

    /**
     * public function listAccounts()
	 *
	 * List accounts
	 * Уточнить старт, лимит, количество (метаданные) для пагинации
	 * Надо научить скрипт выбирать частями, например 100 записей начиная с 200
	 * В задании не требуется
     *
	 * @param  obj $data
     * @return array
     */
	public function listAccounts()
	{
		$result = DB::select('SELECT * FROM accounts'); 

		if ($result) {
			return $result;
		} else {
			return ['status' => 'error', 'error' => 'Ups!!! No data.'];
		}
	}

    /**
     * public function getAccount($account_id)
	 *
	 * get one accounts
     *
	 * @param  scalar $account_id
     * @return array
     */
	public function getAccount($account_id)
	{
		$arr = DB::select('SELECT * FROM accounts WHERE account_id = ?', [$account_id]); 

		if ($arr[0]) {
			return $arr[0];
		} else {
			return ['status' => 'error', 'error' => 'Ups!!! No accounts.'];
		}
	}

    /**
     * public function listPosts($account_id, $limit)
	 *
	 * get one accounts
     *
	 * @param  scalar $account_id
	 * @param  int $limit
     * @return array
     */
	public function listPosts($account_id, $limit)
	{
		$arr = DB::select('SELECT * FROM posts WHERE account_id = ? LIMIT ?', [$account_id, $limit]);

		if ($arr) {
			return $arr;
		} else {
			return ['status' => 'error', 'error' => 'Ups!!! No posts.'];
		}
	}	

    /**
     * public function editAccount($account_id, $data)
	 *
	 * edit account
     *
	 * @param  scalar $account_id
	 * @param  post $data
     * @return array
     */
	public function editAccount($account_id, $data)
	{
		$affected = DB::update('UPDATE accounts SET refresh_interval = ? WHERE account_id = ?', [$data['refresh_interval'], $account_id]); 

		if ($affected) {
			$account = $this->getAccount($account_id);
			return ['status' => 'success', 'title' => $account->title];
		} else {
			return ['status' => 'error', 'error' => 'Ups!!! Not affected.'];
		}
	}

    /**
     * public function deleteAccount($account_id)
	 *
	 * delete account
     *
	 * @param  scalar $account_id
     * @return array
     */
	public function deleteAccount($account_id)
	{
		$deleted = DB::table('accounts')->where('account_id', '=', $account_id)->delete();

		if ($deleted) {
			return ['status' => 'success'];
		} else {
			return ['status' => 'error', 'error' => 'Ups!!! Not deleted.'];
		}
	}
	
    /**
     * public function getHotAccount()
	 *
	 * Get hot account for twitter
     * алгоритм позволяе не исполбзовать трансакции, если понадобится - раскоментировать
	 * можно выбрать не используя поля статус, как то так
	 * SELECT * FROM `accounts` WHERE `updated_at` < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL `refresh_interval` HOUR
	 * будет на 1 запрос меньше, но в перспективе если будет много данных по статусу можно будет выбирать частями
	 * чтоб не перерасходовать память
	 * Изменяем статус аккаунтов время у которых от последнего апдейта меньше интервала
	 * выбирае по статусу hot
	 * Изменяем время последнего апдейта и статус
	 * 
     * @return array
     */
	public function getHotAccount()
	{
		//DB::transaction(function () {
		DB::update('UPDATE `accounts` SET `status` = \'hot\' WHERE `updated_at` < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL `refresh_interval` HOUR)'); 
		
		$accounts = DB::select('SELECT * FROM `accounts` WHERE status = \'hot\'');
		
		DB::update('UPDATE `accounts` SET `status` = \'new\', `updated_at` = CURRENT_TIMESTAMP WHERE status = \'hot\'');
		//});
		
		return $accounts;
	}	

	/**
     * public function insertTwitts($arr, $account_id)
	 *
	 * Какой то косяк мускула. Возвращает true даже если срабатывает INSERT IGNORE
     * поэтому лишний запрос
	 * 
     * @return void
     */
	public function insertTwitts($arr, $account_id)
	{
		DB::transaction(function () use ($arr, $account_id) {
			foreach ($arr as $v) 
				$count = DB::table('posts')->where('post_id', '=', $v->id)->count();

				if (!$count) {
					$sql = "INSERT INTO `posts` (`account_id`, `post_id`, `title`, `datetime`, `description`, `num_favorites`, `num_replies`, `num_retweets`) VALUES (?,?,?,?,?,?,0,?) ";
					$r = DB::insert($sql, [$account_id, $v->id, $v->text, $v->created_at, $v->text, $v->favorite_count, $v->retweet_count]);

					DB::update('UPDATE accounts SET posts_number = (posts_number + 1) WHERE account_id = ?', [$account_id]);
				}
			}
		});
	}
}
