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
$table_name = "kb_sechouse_shop";
$modelname = "wdl_weihouse";
$opersql = '';
if ($_W["user"]["type"] == 3) {
	$opersql = " AND `uid` =:uid ";
}
if ($operation == "display") {
	if (!empty($_GPC["id"])) {
		$params = array(":uniacid" => $_W["uniacid"], ":id" => intval($_GPC["id"]));
		pdo_query("delete  from  " . tablename($table_name) . "  where uniacid=:uniacid and id=:id", $params);
		message("删除门店成功");
	}
	$pindex = max(1, intval($_GPC["page"]));
	$psize = 15;
	$condition = " WHERE `uniacid` = :uniacid ";
	$params = array(":uniacid" => $uniacid);
	if (!empty($_GPC["keyword"])) {
		$condition .= " AND `shopname` LIKE :shopname";
		$params[":shopname"] = "%" . trim($_GPC["keyword"]) . "%";
	}
	if ($opersql) {
		$condition .= $opersql;
		$params[":uid"] = $_W["uid"];
	}
	$sql = "SELECT COUNT(*) FROM " . tablename($table_name) . $condition;
	$total = pdo_fetchcolumn($sql, $params);
	if (!empty($total)) {
		$sql = "SELECT * FROM " . tablename($table_name) . $condition . " ORDER BY  id desc LIMIT " . ($pindex - 1) * $psize . "," . $psize;
		$list = pdo_fetchall($sql, $params);
		$pager = pagination($total, $pindex, $psize);
	}
} else {
	if ($operation == "add") {
		$admin = get_user_permission($uniacid, $modelname);
	} else {
		if ($operation == "setproperty") {
			$id = intval($_GPC["id"]);
			$type = $_GPC["type"];
			$data = intval($_GPC["data"]);
			if (in_array($type, array("istop", "isyou", "ischeng"))) {
				$data = $data == 1 ? "0" : "1";
				pdo_update("kb_sechouse_shop", array($type => $data), array("id" => $id, "uniacid" => $_W["uniacid"]));
				die(json_encode(array("result" => 1, "data" => $data)));
			}
			die(json_encode(array("result" => 0)));
		} else {
			if ($operation == "edit") {
				if (!empty($_GPC["id"])) {
					$params = array(":uniacid" => $uniacid, ":id" => intval($_GPC["id"]));
					$item = pdo_fetch("select * from " . tablename($table_name) . " where uniacid=:uniacid and id=:id", $params);
				}
				if (empty($item)) {
					message("参数有误");
				}
				$admin = get_user_permission($uniacid, $modelname);
			} else {
				if ($operation == "addpost") {
					if (checksubmit("submit")) {
						$savedata = $_GPC["data"];
						$savedata["thumb"] = $_GPC["thumb"];
						$savedata["uniacid"] = $uniacid;
						if ($savedata["uid"] > 0) {
							$savedata["role_name"] = pdo_fetchcolumn("select username from " . tablename("users") . " where uid='" . $savedata["uid"] . "'");
						}
						if (intval($_GPC["id"]) > 0) {
							pdo_update($table_name, $savedata, array("uniacid" => $uniacid, "id" => intval($_GPC["id"])));
						} else {
							pdo_insert($table_name, $savedata);
						}
						message("操作成功", $this->createWebUrl("secshop"), "success");
					}
				}
			}
		}
	}
}
include $this->template("secshop");
function get_user_permission($uniacid, $modelname)
{
	global $_W, $GPC;
	$opersql = '';
	if ($_W["user"]["type"] == 3) {
		$opersql = " AND u.`uid` ='" . $_W["uid"] . "' ";
	}
	$role = pdo_fetchall("select u.username ,u.uid from " . tablename("users_permission") . " as p  left join " . tablename("users") . " as u on p.uid=u.uid where p.uniacid='{$uniacid}' and p.type='{$modelname}' {$opersql}");
	$admin = array();
	if (!empty($role)) {
		foreach ($role as $key => $r) {
			$admin[$r["uid"]] = $r["username"];
		}
	}
	return $admin;
}