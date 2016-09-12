<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Twitter;
use DB;

class Account extends Model
{	

	private $twitter = NULL;

	private function instanceTwitter() {
		return $this->twitter = new Twitter;
	}

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

	public function listAccounts()
	{
		// Уточнить старт, лимит, количество (метаданные) в задании не требуется?!
		$result = DB::select('SELECT * FROM accounts'); 

		if ($result) {
			return $result;
		} else {
			return ['status' => 'error', 'error' => 'Ups!!! No data.'];
		}
	}

	public function getAccount($account_id)
	{
		$arr = DB::select('SELECT * FROM accounts WHERE account_id = ?', [$account_id]); 

		if ($arr[0]) {
			return $arr[0];
		} else {
			return ['status' => 'error', 'error' => 'Ups!!! No accounts.'];
		}
	}

	public function listPosts($account_id, $limit)
	{
		$arr = DB::select('SELECT * FROM posts WHERE account_id = ? LIMIT ?', [$account_id, $limit]);

		if ($arr) {
			return $arr;
		} else {
			return ['status' => 'error', 'error' => 'Ups!!! No posts.'];
		}
	}	

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

	public function deleteAccount($account_id)
	{
		$deleted = DB::table('accounts')->where('account_id', '=', $account_id)->delete();

		if ($deleted) {
			return ['status' => 'success'];
		} else {
			return ['status' => 'error', 'error' => 'Ups!!! Not deleted.'];
		}
	}
	
	public function getHotAccount()
	{
		// алгоритм позволяе не исполбзовать трансакции, если понадобится - раскоментировать
		//DB::transaction(function () {
			
		// можно выбрать не используя поля статус, как то так
		// SELECT * FROM `accounts` WHERE `updated_at` < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL `refresh_interval` HOUR
		// будет на 1 запрос меньше, но в перспективе если будет много данных по статусу можно будет выбирать частями
		// чтоб не перерасходовать память
			
		// Изменяем статус аккаунтов время у которых от последнего апдейта меньше интервала
		DB::update('UPDATE `accounts` SET `status` = \'hot\' WHERE `updated_at` < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL `refresh_interval` HOUR)'); 
		
		// выбирае по статусу hot
		$accounts = DB::select('SELECT * FROM `accounts` WHERE status = \'hot\'');
		
		// Изменяем время последнего апдейта и статус
		DB::update('UPDATE `accounts` SET `status` = \'new\', `updated_at` = CURRENT_TIMESTAMP WHERE status = \'hot\'');
		//});
		
		return $accounts;
	}	
	
	public function insertTwitts($arr, $account_id)
	{
		DB::transaction(function () use ($arr, $account_id) {
			foreach ($arr as $v) {
				// Какой то косяк мускула. Возвращает true даже если срабатывает INSERT IGNORE
				// поэтому лишний запрос
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
