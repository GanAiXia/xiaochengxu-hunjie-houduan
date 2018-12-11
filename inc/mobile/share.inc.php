<?php

//decode by http://www.yunlu99.com/
defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W;
$uniacid = $_W["uniacid"];
load()->func("tpl");
load()->func("logging");
$operation = !empty($_GPC["op"]) ? $_GPC["op"] : "display";
$selects = $this->_forms;
$sec = $this->module_setting();
$infoid = $_GPC["infoid"];
$category = $_GPC["category"];
$uid = $_GPC["uid"];
$money = 0;
if ($operation == "display") {
	if ($category == "newshop") {
		$info = pdo_fetch("select * from " . tablename("kb_house_info") . " where uniacid='{$uniacid}' and id='{$infoid}'");
		$thumb = tomedia($info["house_logo"]);
		$title = $info["house_title"];
		$price = " " . $info["average_price"] . "  元/平米";
		$housetype = "主力户型：" . $info["house_mark"];
		$mianji = "楼盘优惠：" . $info["house_poster"];
	} else {
		if ($category == "sec") {
			$info = pdo_fetch("select * from " . tablename("kb_sechouse") . " where uniacid='{$uniacid}' and id='{$infoid}'");
			$thumb = tomedia($info["thumb"]);
			$title = $info["title"];
			if ($info["rent_type"] == 2) {
				$price = "租金:" . $info["loyer"] . " 元/月";
			} else {
				$price = "售价:" . $info["loyer"] . " 万元";
			}
			$housetype = "户型：" . $info["room"] . "房" . $info["hall"] . "厅" . $info["garder"] . "卫";
			$mianji = "面积：" . $info["superficie"] . "㎡";
		} else {
			if ($category == "paper") {
				$user = pdo_fetch("select * from " . tablename("kb_share_user") . " where uniacid='{$uniacid}' and uid='{$uid}' order by id desc");
				$ret = pdo_fetch("select * from " . tablename("kb_config") . " where uniacid='{$uniacid}' and placeid=31 and module='wdl_weihouse'");
				if (!empty($ret["conf_value"])) {
					$share = iunserializer($ret["conf_value"]);
					$thumb = tomedia($share["paper"]);
				}
				$title = "我是推广达人： " . $user["name"];
				$price = "购房有优惠";
				$housetype = "请扫描关注，或电话联系我！";
				$mianji = "手机号：" . $user["mobile"];
			}
		}
	}
	$scence = $category . $uniacid . $infoid . $uid;
	$hasmake = pdo_fetch("select * from " . tablename("kb_share_scence") . " where uid='{$uid}' and scence='{$scence}'");
	if (!$hasmake) {
		pdo_insert("kb_share_scence", array("uid" => $uid, "scence" => $scence, "category" => $category, "infoid" => $infoid, "money" => $money, "uniacid" => $uniacid));
	}
	$qrsrc = $_W["siteroot"] . "app/" . $this->createMobileUrl("share", array("op" => "bqscence", "weid" => $uniacid, "category" => $category, "infoid" => $infoid, "uid" => $uid, "scence" => $scence));
} else {
	if ($operation == "bqscence") {
		load()->func("communication");
		load()->classs("wxapp.account");
		$account = pdo_fetch("select * from " . tablename("account_wxapp") . " where acid= '" . $uniacid . "'");
		$accObj = new WxappAccount($account);
		$ACCESS_TOKEN = $accObj->getAccessToken();
		$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$ACCESS_TOKEN}";
		$scence = $_GPC["scence"];
		$body = array("scene" => $scence, "page" => "wdl_weihouse/pages/index/welcome");
		$ret = ihttp_post($url, json_encode($body));
		echo $ret["content"];
		exit;
	} else {
		if ($operation == "localpic") {
			load()->func("communication");
			$url = urldecode($_GPC["url"]);
			$ret = ihttp_get($url);
			if ($ret["code"] == 200) {
				echo $ret["content"];
			}
			exit;
		}
	}
}
include $this->template("share_paper");