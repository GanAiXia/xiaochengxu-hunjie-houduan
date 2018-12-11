<?php

//decode by http://www.yunlu99.com/
global $_GPC, $_W;
$uniacid = $this->_uniacid;
$bid = $_GPC["bid"];
$keyword = $_GPC["keyword"];
if ($_GPC["enews"] == "getinfo") {
	$data = pdo_fetch("select * from " . tablename("kb_sechouse_broker") . " where uniacid='{$uniacid}' and id='{$bid}'");
	$data["thumb"] = tomedia($data["avatar"]);
	$condition = " WHERE `uniacid` = :uniacid AND `uid` = :uid";
	$params = array(":uniacid" => $_W["uniacid"], ":uid" => $data["ecuid"]);
	$sql = "SELECT COUNT(*) FROM " . tablename("kb_sechouse_favorite") . $condition;
	$data["fav"] = pdo_fetchcolumn($sql . " AND (acttype='fav' or acttype='newshopfav')", $params);
	$data["view"] = pdo_fetchcolumn($sql . " AND acttype='view' ", $params);
	$data["feed"] = pdo_fetchcolumn($sql . " AND acttype='feed' ", $params);
	$this->result(0, "经纪人资料", $data);
}