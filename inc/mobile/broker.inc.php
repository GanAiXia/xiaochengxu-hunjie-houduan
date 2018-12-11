<?php

//decode by http://www.yunlu99.com/
defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W;
checkauth();
$uniacid = $_W["uniacid"];
$uid = $_W["member"]["uid"];
$openid = $_W["openid"];
load()->func("tpl");
load()->func("logging");
$operation = !empty($_GPC["op"]) ? $_GPC["op"] : "display";
$selects = $this->_forms;
$sec = $this->module_setting();
if ($operation != "display") {
	if ($operation == "kefuBind") {
		$username = $openid;
		$params = array(":openid" => $openid, ":uniacid" => $uniacid);
		$item = pdo_fetch("select * from " . tablename("kb_sechouse_broker") . " where openid=:openid and uniacid=:uniacid", $params);
		if (!empty($item)) {
			pdo_update("kb_sechouse_broker", array("disabled" => 0), array("openid" => $openid, "uniacid" => $uniacid));
		} else {
			$fans = mc_fetch($_W["member"]["uid"], array("avatar", "nickname"));
			pdo_insert("kb_sechouse_broker", array("openid" => $openid, "nickname" => $fans["nickname"], "avatar" => $fans["avatar"], "uniacid" => $uniacid, "disabled" => 0));
		}
		echo "绑定经纪人成功";
		exit;
	}
}