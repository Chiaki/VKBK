<?php
/*
	biganfa/vk-auth wrapper
	version: 1.0
*/

namespace VKA;
require_once ROOT ."/vendor/autoload.php";
use VkAuth\VkAuthAgent;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use VkAuth\exception\VkAuthException;

class dev {
	
	/* VK login [cfg.php] */
	private $login;
	
	/* VK password [cfg.php] */
	private $pass;
	
	/* VK API version [vesion.php] */
	private $api;
	
	/* User-Agent string */
	private $user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36';
	
	/* vk-agent here */
	private $agent;
	
	/* cookies here */
	private $jar;
	
	/* guzzle here */
	private $client;
	
	/*
		Set login, password and api version
	*/
	public function __construct($u, $p, $a){
		$this->login = $u;
		$this->pass = $p;
		$this->api = $a;
	}
	
	/*
		Authorize in VK with login & password
	*/
	public function vka_init(){
		$this->agent = new \VkAuth\VkAuthAgent($this->login, $this->pass, null, function ($message) {
			//echo $message . PHP_EOL;
		});
		$this->jar = $this->agent->getAuthorizedCookieJar();
		$this->client = new \GuzzleHttp\Client([
			'base_uri' => 'http://vk.com',
			'timeout' => 10,
		]);
	}
	
	/*
		Send request to /dev page and get data-hash value
		In: $method (string) - VK API method name
		Out: $hash (string) - value of data-hash
	*/
	private function vka_getHash($method){
		if(empty($method)){ return false; }
		/** @var \GuzzleHttp\Psr7\Response $response */
		/** Call method page to get data-hash */
		$response = $this->client->get(
			'/dev/'.$method,
			[
				'allow_redirects' => true,
				'cookies' => $this->jar // auth cookie inside
			]
		);
		$vkResponseBody = strval($response->getBody());
		// Search a hash
		preg_match_all("/data\-hash\=\"([^\"]+)\"/",$vkResponseBody,$h);
		
		return (isset($h[1][0]) && !empty($h[1][0]) ? $h[1][0] : false);
	}
	
	/*
		Check do we allow this method
		In: $method (string) - VK API method name
		Out: bool
	*/
	private function vka_methodAllowed($method){
		$allowed = array(
			'messages.getConversations' => true,
			'messages.getHistory' => true
		);
		
		return (isset($allowed[$method]) ? true : false );
	}
	
	/*
		Default configs for allowed methods
		In: $method (string) - VK API method name
		Out: array || false
	*/
	private function vka_getMethodConfig($method){
		$methods = array(
			'messages.getConversations' => [
				'act' => 'a_run_method',
				'al' => 1,
				'hash' => '',
				'method' => 'messages.getConversations',
				'param_offset' => 0,
				'param_count' => 20,
				'param_filter' => 'all',
				'param_extended' =>0,
				'param_start_message_id' => '',
				'param_fields' => '',
				'param_group_id' => '',
				'param_v' => $this->api
			],
			'messages.getHistory' => [
				'act' => 'a_run_method',
				'al' => 1,
				'hash' => '',
				'method' => 'messages.getHistory',
				'param_offset' => 0,
				'param_count' => 20,
				'param_user_id' => '',
				'param_peer_id' => '',
				'param_start_message_id' => '',
				'param_rev' => 0,
				'param_extended' => 0,
				'param_fields' => '',
				'param_group_id' => '',
				'param_v' => $this->api
			]
		);
		
		return (isset($methods[$method]) ? $methods[$method] : false );
	}
	
	/*
		Method call, in return get data in json format or false
		In: $method (string) - VK API method name
			$params (array) - overrides default method params
		Out: array || false
	*/
	public function vka_method($method, $params = array()){
		// Check: method allowed
		if(!$this->vka_methodAllowed($method)){ return false; }
		
		// Get: data-hash
		$hash = $this->vka_getHash($method);
		
		// Get: method config
		$fields = $this->vka_getMethodConfig($method);
		
		// If we have hash and config, do a request!
		if($hash !== false && $fields !== false){
			
			// Set: hash
			$fields['hash'] = $hash;
			
			// For every field of method config, let's check do we have a
			// param istead of default value
			foreach($fields as $fk => $fv){
				if(isset($params[$fk])){ $fields[$fk] = $params[$fk]; }
			}
			//var_dump($fields);
			$response2 = $this->client->request('POST', 'https://vk.com/dev', [
				'allow_redirects' => true,
				'cookies' => $this->jar, // auth cookie inside
				'headers' => [
					'User-Agent'   => $this->user_agent,
					'accept'       => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
					'content-type' => 'application/x-www-form-urlencoded',
					'origin'       => 'https://vk.com',
					'referer'      => 'https://vk.com/'
				],
				'form_params' => $fields
			]);
			
			// Convert cp1251 to UTF-8
			$vkResponseBody2 = iconv('cp1251','UTF-8',$response2->getBody());
			
			// Remove <!-- from start
			if(substr($vkResponseBody2,0,4) == '<!--'){
				$q = substr($vkResponseBody2,4);
			}
			// Decode JSON payload
			$q = json_decode($q, true);
			
			if(isset($q['payload'][1][0])){
				// Decode JSON responce
				$q = json_decode($q['payload'][1][0], true);
				if(is_array($q)){ return $q; }
			} else {
				return false;
			}			
		} else {
			return false;
		}
	}
}

?>