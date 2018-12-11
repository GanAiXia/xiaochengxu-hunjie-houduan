<?php

//decode by http://www.yunlu99.com/
defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W;
load()->func("tpl");
$operation = !empty($_GPC["op"]) ? $_GPC["op"] : "display";
$selects = $this->_forms;
$sec = $this->module_setting();
$uniacid = $_W["uniacid"];
$openid = $_W["openid"];
$opersql = '';
if ($_W["user"]["type"] == 3) {
	$opersql = " AND `role_uid` =:uid ";
}
if ($operation == "display") {
	if (isset($_GPC["id"]) && !empty($_GPC["id"])) {
		pdo_delete("kb_sechouse_favorite", array("id" => $_GPC["id"], "uniacid" => $uniacid));
	}
	$pindex = max(1, intval($_GPC["page"]));
	$psize = 15;
	$condition = " WHERE a.`uniacid` = :uniacid ";
	$params = array(":uniacid" => $_W["uniacid"]);
	if (!empty($_GPC["keyword"])) {
		$condition .= " AND a.`title` LIKE :title";
		$params[":title"] = "%" . trim($_GPC["keyword"]) . "%";
	}
	if (isset($_GPC["ftype"])) {
		$condition .= " AND a.`acttype` = :ftype";
		$params[":ftype"] = trim($_GPC["ftype"]);
		$ftype = trim($_GPC["ftype"]);
	} else {
		$condition .= " AND a.`ftype` = :ftype";
		$params[":ftype"] = "view";
		$ftype = "view";
	}
	if ($opersql) {
		$condition .= $opersql;
		$params[":uid"] = $_W["uid"];
	}
	$sql = "SELECT COUNT(*) FROM " . tablename("kb_sechouse_favorite") . " as a " . $condition;
	$total = pdo_fetchcolumn($sql, $params);
	if (!empty($total)) {
		$sql = "SELECT a.*,b.nickname,b.avatar FROM " . tablename("kb_sechouse_favorite") . " as a left join " . tablename("mc_members") . " as b on a.uid=b.uid " . $condition . " ORDER BY a.`id` DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize;
		$list = pdo_fetchall($sql, $params);
		$pager = pagination($total, $pindex, $psize);
	}
}
include $this->template("house_statics");