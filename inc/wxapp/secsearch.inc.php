<?php

//decode by http://www.yunlu99.com/
global $_GPC, $_W;
$uniacid = $this->_uniacid;
$uid = $_W["member"]["uid"];
$openid = $_W["openid"];
$search["keyword"] = trim($_GPC["keyword"]);
$search["area"] = trim($_GPC["area"]);
$search["minprice"] = intval($_GPC["minprice"]);
$search["maxprice"] = intval($_GPC["maxprice"]);
$search["minsufix"] = intval($_GPC["minsufix"]);
$search["maxsufix"] = intval($_GPC["maxsufix"]);
$search["room"] = intval($_GPC["room"]);
$search["isjingping"] = intval($_GPC["isjingping"]);
$search["istop"] = intval($_GPC["istop"]);
$search["order"] = intval($_GPC["order"]);
$search["morefind"] = $_GPC["morefind"];
$zhutype = intval($_GPC["zhutype"]);
$rent_type = intval($_GPC["rent_type"]);
if (isset($_GPC["rent_type"])) {
	$rent_type = intval($_GPC["rent_type"]);
}
$condition = " uniacid='{$uniacid}' and isonline=1 and isdelete=0 and rent_type='{$rent_type}' and zhutype='{$zhutype}' ";
if (isset($_GPC["isbroker"]) && $_GPC["isbroker"] == 1) {
	$brokerid = intval($_GPC["bid"]);
	if ($brokerid > 0) {
		$condition = " uniacid='{$uniacid}' and broker_id='{$brokerid}' and rent_type='{$rent_type}' and zhutype in(0,1,2,3)";
	} else {
		$brokerid = pdo_fetchcolumn("select id from " . tablename("kb_sechouse_broker") . " where openid='{$openid}' and uniacid='{$uniacid}'");
		if ($brokerid > 0) {
			$condition = " uniacid='{$uniacid}' and broker_id='{$brokerid}' and rent_type='{$rent_type}' and isonline=1 and isdelete=0  and zhutype in(0,1,2,3) ";
		} else {
			$condition = " uniacid='{$uniacid}' and broker_name='{$openid}' and rent_type='{$rent_type}' and isonline=1 and isdelete=0  and zhutype in(0,1,2,3) ";
		}
	}
}
$page = max(1, $_GPC["page"]);
$pagesize = 10;
$startlimit = ($page - 1) * $pagesize;
if (!empty($search["keyword"])) {
	$condition .= " and (`title` like '%{$search["keyword"]}%' or `area` like '%{$search["keyword"]}%' or `village_name` like '%{$search["keyword"]}%')";
}
if (!empty($search["area"])) {
	$condition .= " and area='{$search["area"]}'";
}
if (!empty($search["minprice"])) {
	$condition .= " and loyer>='{$search["minprice"]}'";
}
if (!empty($search["maxprice"])) {
	$condition .= " and loyer<='{$search["maxprice"]}'";
}
if (!empty($search["minsufix"])) {
	$condition .= " and superficie>='{$search["minsufix"]}'";
}
if (!empty($search["maxsufix"])) {
	$condition .= " and superficie<='{$search["maxsufix"]}'";
}
if ($search["isjingping"] == 1) {
	$condition .= " and isjingpin='{$search["isjingping"]}'";
}
if (isset($_GPC["morefind"]) && !empty($_GPC["morefind"])) {
	$morefind = explode(",", trim(trim($_GPC["morefind"], "]"), "["));
	foreach ($morefind as $k => $v) {
		$morefind[$k] = trim($v, "&quot;");
	}
	if ($morefind[0]) {
		$condition .= " and room='{$morefind[0]}'";
	}
	if ($morefind[1]) {
		$condition .= " and house_type='{$morefind[1]}'";
	}
	if ($morefind[2]) {
		$condition .= " and traveaux_finition='{$morefind[2]}'";
	}
	if ($morefind[3]) {
		$years = date("Y");
		$tmp = array("1" => " and ( build_age  <" . $years . " and build_age>" . ($years - 2) . " )", "2" => " and (build_age   <" . ($years - 2) . " and build_age>" . ($years - 5) . ")", "3" => " and ( build_age   <" . ($years - 5) . " and build_age>" . ($years - 10) . ")", "4" => " and ( build_age   <" . ($years - 10) . " and build_age>1980 )");
		$condition .= $tmp[$morefind[3]];
	}
	if ($morefind[4]) {
		if ($morefind[4] == 1) {
			$condition .= " and ptype='1' ";
		} else {
			$condition .= " and ptype='0' ";
		}
	}
}
if ($search["order"] > 0) {
	$tmp = array(1 => " add_time desc ", 2 => " update_time desc ", 3 => " loyer desc");
	$order = $tmp[$search["order"]];
}
if (empty($order)) {
	$order = " update_time desc ,refresh_time desc ";
}
if ($search["istop"] == 1) {
	$condition .= " and istop='1' ";
	$pagesize = 1000;
} else {
	if (!isset($_GPC["isbroker"])) {
		$condition .= " and istop='0' ";
	}
}
$fileds = $this->_query_sechouse_field();
$sql = "SELECT {$fileds}  FROM " . tablename("kb_sechouse") . " where {$condition} order by {$order} limit {$startlimit}, {$pagesize}";
$items = pdo_fetchall($sql);
if (!empty($items)) {
	foreach ($items as $key => $item) {
		$val = $this->_format_sechouse($item);
		$val["groupid"] = 0;
		if ($val["iscompany"] == 9) {
			$val["pushtype"] = "个人";
		} else {
			$val["pushtype"] = "中介";
		}
		if ($val["broker_id"] > 0) {
			$val["groupid"] = pdo_fetchcolumn("select groupid from " . tablename("kb_sechouse_broker") . " where id='" . $val["broker_id"] . "'");
			$val["pushtype"] = "经纪人";
			if ($val["groupid"] == 0) {
				$val["pushtype"] = "个人";
			}
		}
		if ($val["zhutype"] == 4) {
			$tmp = pdo_fetch("select nickname,avatar from " . tablename("kb_sechouse_broker") . " where id='" . $val["broker_id"] . "'");
			if ($tmp["avatar"] == "undefined" || empty($tmp["avatar"])) {
				$tmp["avatar"] = "addons/wdl_weihouse/style/images/house_face.jpg";
			}
			$tmp["avatar"] = toimage($tmp["avatar"]);
			$val["sendbroker"] = $tmp;
		}
		$items[$key] = $this->_format_show_item($val);
	}
	if (isset($_GPC["gettotal"]) && $_GPC["gettotal"] == 1) {
		$data["total"] = pdo_fetchcolumn(" select count（*）  from " . tablename("kb_sechouse") . " where {$condition} ");
		$data["items"] = $items;
		$this->result(0, "查询成功 {$sql}", $data);
	} else {
		$this->result(0, "查询成功 {$sql}", $items);
	}
} else {
	$this->result(0, "{$sql}", $items);
}