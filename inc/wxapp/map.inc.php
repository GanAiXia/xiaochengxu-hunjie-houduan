<?php

//decode by http://www.yunlu99.com/
global $_GPC, $_W;
$uniacid = $this->_uniacid;
$tags = intval($_GPC["tags"]);
if ($tags == 0) {
	$addon = " and rent_type=0 and zhutype=0 ";
} else {
	if ($tags == 1) {
		$addon = " and rent_type=2 and zhutype=0 ";
	} else {
		if ($tags == 2) {
			$addon = "  and zhutype=1 ";
		} else {
			if ($tags == 3) {
				$addon = " and zhutype=2 ";
			} else {
				if ($tags == 4) {
					$addon = " and zhutype=3 ";
				}
			}
		}
	}
}
$ret = pdo_fetchall("select id, title,  mapinfo from " . tablename("kb_sechouse") . " where `mapinfo` IS NOT NULL and uniacid='{$uniacid}' {$addon} ");
$items = array();
foreach ($ret as $key => $val) {
	if ($val["mapinfo"]) {
		$point = explode(",", $val["mapinfo"]);
		$bd_lon = $point[0];
		$bd_lat = $point[1];
		$X_PI = M_PI * 3000.0 / 180.0;
		$x = $bd_lon - 0.0065;
		$y = $bd_lat - 0.006;
		$z = sqrt($x * $x + $y * $y) - 2.0E-5 * sin($y * $X_PI);
		$theta = atan2($y, $x) - 3.0E-6 * cos($x * $X_PI);
		$lon = $z * cos($theta);
		$lat = $z * sin($theta);
		$items[] = array("id" => $val["id"], "title" => $val["title"], "mapinfo" => $val["mapinfo"], "lat" => $lat, "lon" => $lon);
	}
}
$this->result(0, "查询成功 {$sql}", $items);