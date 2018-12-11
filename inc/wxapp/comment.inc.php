<?php

//decode by http://www.yunlu99.com/
global $_GPC, $_W;
$uniacid = $this->_uniacid;
$newhouse_id = $_GPC["infoid"];
$keyword = $_GPC["keyword"];
$tid = 39;
if ($_GPC["tags"]) {
	$tid = $_GPC["tags"];
}
$limit = 4;
if (isset($_GPC["limit"]) && !empty($_GPC["limit"])) {
	$limit = intval($_GPC["limit"]);
}
$sql_add = " uniacid='{$uniacid}' and ftype='{$tid}'  and houseid='{$newhouse_id}'";
$page = max(1, $_GPC["page"]);
$pagesize = $limit;
$startlimit = ($page - 1) * $pagesize;
$category = array();
if (isset($_GPC["total"]) && !empty($_GPC["total"])) {
	$total = $_GPC["total"];
} else {
	$total = pdo_fetchcolumn("select count(*)  from " . tablename("kb_sechouse_favorite") . "   where {$sql_add} ");
}
$ret = pdo_fetchall("select uid,id,addtime,smalltext,ftype  from " . tablename("kb_sechouse_favorite") . "  where {$sql_add} order by id desc limit {$startlimit},{$pagesize} ");
if (!empty($ret)) {
	foreach ($ret as $key => $val) {
		$smalltext = explode("|", $val["smalltext"]);
		$ret[$key]["content"] = str_replace("留言：", '', $smalltext[0]);
		$ret[$key]["score"] = $smalltext[1];
		$ret[$key]["showtime"] = date("Y年m月d日", $val["addtime"]);
		if (!empty($val["uid"])) {
			$fans = mc_fetch($val["uid"], array("avatar", "nickname"));
			$ret[$key]["nickname"] = $fans["nickname"];
			$ret[$key]["avater"] = $fans["avatar"];
		}
	}
}
$data["salelist"] = $ret;
$data["total"] = $total;
$data["commentsql"] = "select uid,id,addtime,smalltext,ftype  from " . tablename("kb_sechouse_favorite") . "  where {$sql_add} order by id desc limit {$startlimit},{$pagesize} ";
$this->result(0, "success", $data);