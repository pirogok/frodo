<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Abraham\TwitterOAuth\TwitterOAuth;
use DB;

class Twitter extends Model
{

	private $consumerKey 	= '';
	private $consumerSecret = '';
	private $oauthToken 	= '';
	private $oauthSecret	= '';

	public $connection 		= NULL;

	public $credentials 	= NULL;

	public function __construct()
	{
		$this->consumerKey  	= env('TWITTER_CONSUMER_KEY', '');
		$this->consumerSecret  	= env('TWITTER_CONSUMER_SECRET', '');
		$this->accessToken  	= env('TWITTER_ACCESS_TOKEN', '');
		$this->accessSecret  	= env('TWITTER_ACCESS_TOKEN_SECRET', '');

		$this->connection = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->accessToken, $this->accessSecret);
	}

	public function credentials()
	{
		$this->credentials = $this->connection->get('account/verify_credentials');
		return $this->credentials;
	}

	public function getAccountInfo($account_id, $screen_name)
	{
		return $this->connection->get('users/show', ['user_id' => $account_id, 'screen_name' => $screen_name]);
	}

	public function getTwitterPosts($account_id, $screen_name, $limit)
	{
		return $this->connection->get('statuses/user_timeline', ['user_id' => $account_id, 'screen_name' => $screen_name, 'count' => $limit]);
	}
}
