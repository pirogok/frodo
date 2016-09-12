<?php
/**
 * class Twitter extends Model
 *
 * Model for Twitter
 * 
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Abraham\TwitterOAuth\TwitterOAuth;
use DB;

class Twitter extends Model
{
    /**
	 * доступ к твиттеру
	 * 
     * private $consumerKey
	 * private $consumerSecret
	 * private $oauthToken
	 * private $oauthSecret
	 * 
     */
	private $consumerKey 	= '';
	private $consumerSecret = '';
	private $oauthToken 	= '';
	private $oauthSecret	= '';

    /**
	 * public $connection
	 * 
	 * ресурс твиттера
	 * 
     */
	public $connection 		= NULL;

    /**
	 * public $connection
	 * 
	 * верительные грамоты
	 * 
     */
	public $credentials 	= NULL;

    /**
     * public function __construct()
	 *
	 * connect to twitter
     *
     * @return void
     */
	public function __construct()
	{
		$this->consumerKey  	= env('TWITTER_CONSUMER_KEY', '');
		$this->consumerSecret  	= env('TWITTER_CONSUMER_SECRET', '');
		$this->accessToken  	= env('TWITTER_ACCESS_TOKEN', '');
		$this->accessSecret  	= env('TWITTER_ACCESS_TOKEN_SECRET', '');

		$this->connection = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->accessToken, $this->accessSecret);
	}

    /**
     * public function credentials()
	 *
	 * credentials
     *
     * @return void
     */
	public function credentials()
	{
		$this->credentials = $this->connection->get('account/verify_credentials');
		return $this->credentials;
	}

    /**
     * public function getAccountInfo($account_id, $screen_name)
	 *
	 * Get account
     *
     * @return array
     */
	public function getAccountInfo($account_id, $screen_name)
	{
		return $this->connection->get('users/show', ['user_id' => $account_id, 'screen_name' => $screen_name]);
	}

    /**
     * public function getTwitterPosts($account_id, $screen_name, $limit)
	 *
	 * Get twitt
     *
	 * @param  scalar $account_id
	 * @param  string $screen_name
	 * @param  int $limit
     * @return array
     */
	public function getTwitterPosts($account_id, $screen_name, $limit)
	{
		return $this->connection->get('statuses/user_timeline', ['user_id' => $account_id, 'screen_name' => $screen_name, 'count' => $limit]);
	}
}
