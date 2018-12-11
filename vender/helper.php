<?php

//decode by http://www.yunlu99.com/
class wdlHelper
{
	public function getAccessToken($key, $secret)
	{
		$ret = pdo_fetch("select acid from " . tablename("account_wechats") . " where `key`='{$key}' and secret='{$secret}' order by acid desc");
		if ($ret["acid"]) {
			$cachekey = "we7:accesstoken:{$ret["acid"]}";
		} else {
			$cachekey = "we7:accesstoken_wdl:{$key}";
		}
		$cache = cache_load($cachekey);
		if (!empty($cache) && !empty($cache["token"]) && $cache["expire"] > TIMESTAMP) {
			$access_token = $cache;
			return $access_token["token"];
		}
		if (empty($key) || empty($secret)) {
			return false;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$key}&secret={$secret}";
		$content = ihttp_get($url);
		if (is_error($content)) {
			return false;
		}
		if (empty($content["content"])) {
			return false;
		}
		$token = @json_decode($content["content"], true);
		if (empty($token) || !is_array($token) || empty($token["access_token"]) || empty($token["expires_in"])) {
			$errorinfo = substr($content["meta"], strpos($content["meta"], "{"));
			$errorinfo = @json_decode($errorinfo, true);
			return false;
		}
		$record = array();
		$record["token"] = $token["access_token"];
		$record["expire"] = TIMESTAMP + $token["expires_in"] - 200;
		cache_write($cachekey, $record);
		return $record["token"];
	}
}