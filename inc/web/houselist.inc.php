<?php

//decode by http://www.yunlu99.com/
defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W;
load()->func("tpl");
$operation = !empty($_GPC["op"]) ? $_GPC["op"] : "display";
$selects = $this->_forms;
$uniacid = $_W["uniacid"];
$openid = $_W["openid"];
$opersql = '';
if ($_W["user"]["type"] == 3) {
	$opersql = " AND `uid` =:uid ";
}
if ($operation == "display") {
	if (isset($_GPC["ids"]) && !empty($_GPC["ids"])) {
		$sk = intval($_GPC["htype"]);
		$sqladd_tmp = array("  rent_type=0 , zhutype=0 ", "  rent_type=2 , zhutype=0 ", "  rent_type=0 , zhutype=1 ", "  rent_type=2 , zhutype=1 ", "  rent_type=0 , zhutype=2 ", "  rent_type=2 , zhutype=2 ", "  rent_type=0 , zhutype=3 ");
		if (!empty($sqladd_tmp[$sk])) {
			$ids = implode(",", $_GPC["ids"]);
			pdo_query("update " . tablename("kb_sechouse") . " set " . $sqladd_tmp[$sk] . " where uniacid='{$uniacid}' and id in ({$ids}) ");
		}
	}
	$pindex = max(1, intval($_GPC["page"]));
	$psize = 15;
	$condition = " WHERE `uniacid` = :uniacid  ";
	$params = array(":uniacid" => $_W["uniacid"]);
	if ($opersql) {
		$condition .= $opersql;
		$params[":uid"] = $_W["uid"];
	}
	if (!empty($_GPC["keyword"])) {
		$condition .= " AND (`title` LIKE :title OR `village_name` LIKE :title OR `area` LIKE :title OR `house_sno` LIKE :title OR `publish_name`  LIKE :title )";
		$params[":title"] = "%" . trim($_GPC["keyword"]) . "%";
	}
	if (isset($_GPC["inonline"])) {
		$condition .= " AND `isonline` = :isonline";
		$params[":isonline"] = intval($_GPC["isonline"]);
	}
	if (isset($_GPC["htype"]) && $_GPC["htype"] != '') {
		$sqladd_tmp = array(" AND rent_type=0 AND zhutype=0 ", " AND rent_type=2 AND zhutype=0 ", " AND rent_type=0 AND zhutype=1 ", " AND rent_type=2 AND zhutype=1 ", " AND rent_type=0 AND zhutype=2 ", " AND rent_type=2 AND zhutype=2 ", " AND rent_type=0 AND zhutype=3 ");
		$sk = intval($_GPC["htype"]);
		if (!empty($sqladd_tmp[$sk])) {
			$condition .= $sqladd_tmp[$sk];
		}
	}
	$sql = "SELECT COUNT(*) FROM " . tablename("kb_sechouse") . $condition;
	$total = pdo_fetchcolumn($sql, $params);
	if (!empty($total)) {
		$sql = "SELECT * FROM " . tablename("kb_sechouse") . $condition . " ORDER BY `id` DESC, `endtime` DESC,\r\n                            `id` DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize;
		$list = pdo_fetchall($sql, $params);
		$pager = pagination($total, $pindex, $psize);
	}
	$htype_option = array("二手房", "租房", "写字楼出售", "写字楼出租", "商铺出售", "商铺出租", "生意转让");
	include $this->template("house_list");
} else {
	if ($operation == "setproperty") {
		$id = intval($_GPC["id"]);
		$type = $_GPC["type"];
		$data = intval($_GPC["data"]);
		if (in_array($type, array("istop", "isjingpin", "ishot", "show_jiaji", "show_you", "show_hight"))) {
			$data = $data == 1 ? "0" : "1";
			pdo_update("kb_sechouse", array($type => $data), array("id" => $id, "uniacid" => $_W["uniacid"]));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		if (in_array($type, array("isonline"))) {
			$data = $data == 1 ? "0" : "1";
			pdo_update("kb_sechouse", array($type => $data), array("id" => $id, "uniacid" => $_W["uniacid"]));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		if (in_array($type, array("isdelete"))) {
			$data = $data == 1 ? "0" : "1";
			pdo_update("kb_sechouse", array($type => $data), array("id" => $id, "uniacid" => $_W["uniacid"]));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		die(json_encode(array("result" => 0)));
	}
}