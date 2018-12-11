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
$adplace = (include IA_ROOT . "/addons/wdl_weihouse/vender/adplace.php");
$default_menu = (include IA_ROOT . "/addons/wdl_weihouse/vender/menu.php");
$modulename = "wdl_weihouse";
function update_place_data($placeid, $confkey, $confvalue = array())
{
	global $_GPC, $_W;
	$modulename = "wdl_weihouse";
	$uniacid = $_W["uniacid"];
	if (empty($confvalue)) {
		$conf_value = $_GPC[$confkey];
	} else {
		$conf_value = $confvalue;
	}
	$has = pdo_fetch("select * from " . tablename("kb_config") . " where placeid='{$placeid}' and confkey='{$confkey}' and uniacid='{$uniacid}' and module='{$modulename}'");
	$data = array("placeid" => $placeid, "uniacid" => $uniacid, "uid" => $_W["uid"], "module" => $modulename, "confkey" => $confkey, "conf_value" => iserializer($conf_value));
	if (empty($has)) {
		pdo_insert("kb_config", $data);
	} else {
		pdo_update("kb_config", $data, array("id" => $has["id"]));
	}
	return true;
}
function fetch_place_data($placeid, $confkey = '')
{
	global $_GPC, $_W;
	$modulename = "wdl_weihouse";
	$uniacid = $_W["uniacid"];
	$modulename = "wdl_weihouse";
	$sqladd = '';
	if ($confkey != '') {
		$sqladd = " and confkey='{$confkey}' ";
	}
	$ret = pdo_fetch("select * from " . tablename("kb_config") . " where placeid='{$placeid}' {$sqladd} and uniacid='{$uniacid}' and module='{$modulename}' order  by id desc");
	return iunserializer($ret["conf_value"]);
}
if ($operation == "postadd") {
	update_place_data(1, "navmenu");
	update_place_data(11, "confmenu");
	update_place_data(2, "btmenu");
	update_place_data(4, "banner");
	update_place_data(21, "slide_news");
	update_place_data(22, "slide_house");
	update_place_data(6, "notice");
	update_place_data(5, "indexprice");
	update_place_data(31, "share");
	update_place_data(41, "gzhinfo");
	if (!empty($_GPC["thumb"])) {
		$tmp = array("title" => $_GPC["thumb_name"], "thumb" => $_GPC["thumb"]);
		$data = array("placeid" => 3, "uniacid" => $uniacid, "uid" => $_W["uid"], "module" => $modulename, "confkey" => "media", "conf_value" => iserializer($tmp));
		pdo_insert("kb_config", $data);
	}
	message("编辑成功");
	include $this->template("config_display");
}
if ($operation == "display") {
	$page = intval($_GPC["page"]);
	$confid = intval($_GPC["confid"]);
	if ($confid > 0) {
		pdo_delete("kb_config", array("id" => $confid, "uniacid" => $uniacid));
	}
	$navmenu = fetch_place_data(1);
	$confmenu = fetch_place_data(11);
	if ($confmenu["index"] == 1) {
		$default_menu = $navmenu;
	}
	$btmenu = fetch_place_data(2);
	$banner = fetch_place_data(4);
	if (empty($banner)) {
		$banner["image"] = array();
	}
	$notice = fetch_place_data(6);
	$slide_news = fetch_place_data(21);
	$slide_house = fetch_place_data(22);
	$indexprice = fetch_place_data(5);
	$share = fetch_place_data(31);
	$gzhinfo = fetch_place_data(41);
	$ret = pdo_fetchall("select * from " . tablename("kb_config") . " where placeid=3 and uniacid='{$uniacid}' and module='{$modulename}'");
	if (!empty($ret)) {
		foreach ($ret as $k => $val) {
			$tmp = iunserializer($val["conf_value"]);
			$tmp["id"] = $val["id"];
			$medias[] = $tmp;
		}
	}
	include $this->template("config_display");
} else {
	if ($operation == "adplace") {
		foreach ($adplace as $key => $place) {
			$adplace[$key]["place"] = fetch_place_data(201, $key);
		}
		include $this->template("config_ad_place");
	} else {
		if ($operation == "setplace") {
			$position = $_GPC["id"];
			$place = $adplace[$position];
			if (checksubmit("submit")) {
				update_place_data(201, $position, $_GPC["data"]);
				message("编辑成功", $this->createWebUrl("enset", array("op" => "adplace")));
			}
			$set = fetch_place_data(201, $position);
			if (!empty($set)) {
				$place["place"] = $set;
			}
			include $this->template("config_ad_place");
		} else {
			if ($operation == "setaddata") {
				$position = $_GPC["id"];
				$place = $adplace[$position];
				$form = $adplace[$position]["formfield"];
				if (checksubmit("submit")) {
					foreach ($form as $key => $v) {
						if ($v["type"] == "thumb") {
							$_GPC["data"][$key] = tomedia($_GPC["data"][$key]);
						}
					}
					$tmp = $_GPC["data"];
					$data = array("placeid" => 202, "uniacid" => $uniacid, "uid" => $_W["uid"], "module" => $modulename, "confkey" => $position, "conf_value" => iserializer($tmp));
					pdo_insert("kb_config", $data);
					message("编辑成功", $this->createWebUrl("enset", array("op" => "adplace")));
				}
				$medias = array();
				$ret = pdo_fetchall("select * from " . tablename("kb_config") . " where placeid=202 and confkey='{$position}' and uniacid='{$uniacid}' and module='{$modulename}'");
				if (!empty($ret)) {
					foreach ($ret as $k => $val) {
						$tmp = iunserializer($val["conf_value"]);
						foreach ($form as $k2 => $v) {
							if ($v["type"] == "thumb") {
								$tmp[$k2] = tomedia($tmp[$k2]);
							}
						}
						$tmp["id"] = $val["id"];
						$medias[] = $tmp;
					}
				}
				include $this->template("config_ad_place");
			} else {
				if ($operation == "deladdata") {
					$id = intval($_GPC["id"]);
					pdo_delete("kb_config", array("id" => $id, "uniacid" => $uniacid));
					message("删除成功");
				} else {
					if ($operation == "editaddata") {
						$iid = $_GPC["iid"];
						$aditem = pdo_fetch("select * from " . tablename("kb_config") . " where id='{$iid}' and  uniacid='{$uniacid}'");
						if (!empty($_GPC["id"])) {
							$position = $_GPC["id"];
						} else {
							$position = $aditem["confkey"];
						}
						$place = $adplace[$position];
						$form = $adplace[$position]["formfield"];
						if ($iid) {
							$place["addata"] = iunserializer($aditem["conf_value"]);
						}
						if (checksubmit("submit")) {
							foreach ($form as $key => $v) {
								if ($v["type"] == "thumb") {
									$_GPC["data"][$key] = tomedia($_GPC["data"][$key]);
								}
							}
							$tmp = $_GPC["data"];
							$data = array("placeid" => 202, "uniacid" => $uniacid, "uid" => $_W["uid"], "module" => $modulename, "confkey" => $position, "conf_value" => iserializer($tmp));
							pdo_update("kb_config", $data, array("id" => $iid));
							message("编辑成功", $this->createWebUrl("enset", array("op" => "adplace")));
						}
						include $this->template("config_ad_place");
					}
				}
			}
		}
	}
}