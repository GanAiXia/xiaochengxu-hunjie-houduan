<?php

//decode by http://www.yunlu99.com/
defined("IN_IA") or exit("Access Denied");
require IA_ROOT . "/addons/wdl_weihouse/vender/element.class.php";
class Wdl_weihouseModuleWxapp extends WeModuleWxapp
{
	public $_uniacid = 0;
	public $other = null;
	public $_forms = null;
	public function __construct()
	{
		global $_GPC, $_W;
		$uniacid = $_W["uniacid"];
		$adapter = (include IA_ROOT . "/addons/wdl_weihouse/vender/adapter.php");
		$this->_forms = $adapter["sechouse"];
		$this->other = $adapter["newhouse"];
		$ret = pdo_fetch("select modules from " . tablename("wxapp_versions") . " where uniacid='{$uniacid}'");
		$module = iunserializer($ret["modules"]);
		if ($module["wdl_weihouse"]["uniacid"]) {
			$this->_uniacid = $module["wdl_weihouse"]["uniacid"];
		} else {
			$this->_uniacid = $_W["uniacid"];
		}
	}
	public function _send_notice_account($notice_type = '', $first = '', $keyword1 = "关键词1", $keyword2 = "关键词2", $remark = "备注")
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$gzhinfo = pdo_fetch("select * from " . tablename("kb_config") . " where placeid=41 and uniacid='{$uniacid}' and module='wdl_weihouse'");
		if (!empty($gzhinfo["conf_value"])) {
			$gzh = iunserializer($gzhinfo["conf_value"]);
		} else {
			return false;
		}
		if (empty($notice_type) || $gzh[$notice_type] != 1) {
			return false;
		}
		require IA_ROOT . "/addons/wdl_weihouse/vender/helper.php";
		load()->func("logging");
		$helper = new wdlHelper();
		if (empty($gzh["appid"]) || empty($gzh["appsecret"])) {
			return false;
		}
		$ACCESS_TOKEN = $helper->getAccessToken($gzh["appid"], $gzh["appsecret"]);
		if (!$ACCESS_TOKEN) {
			return false;
		}
		$content["touser"] = $gzh["adminopenid"];
		$content["template_id"] = $gzh["template_id"];
		$content["url"] = '';
		$content["topcolor"] = "#FF0000";
		$content["data"]["first"]["value"] = $first;
		$content["data"]["keyword1"]["value"] = $keyword1;
		$content["data"]["keyword2"]["value"] = $keyword2;
		$content["data"]["remark"]["value"] = $remark;
		$tmpmsg = "{\r\n                    \"touser\":\"" . $content["touser"] . "\",\r\n                    \"template_id\":\"" . $content["template_id"] . "\",                  \r\n                    \"topcolor\":\"#FF0000\",\r\n                    \"data\":{\r\n                            \"first\": {\r\n                                \"value\":\"" . $content["data"]["first"]["value"] . "\",\r\n                                \"color\":\"#bf0000\"\r\n                            },\r\n                            \"keyword1\":{\r\n                                \"value\":\"" . $content["data"]["keyword1"]["value"] . "\",\r\n                                \"color\":\"#173177\"\r\n                            },\r\n                            \"keyword2\":{\r\n                                \"value\":\"" . $content["data"]["keyword2"]["value"] . "\",\r\n                                \"color\":\"#173177\"\r\n                            },\"remark\":{\r\n                                \"value\":\"" . $content["data"]["remark"]["value"] . "\",\r\n                                \"color\":\"#333333\"\r\n                            }        \r\n                            }\r\n                        }";
		$tempurl = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $ACCESS_TOKEN;
		$text = "{\r\n                    \"touser\": \"" . $content["touser"] . "\",\r\n                    \"msgtype\":\"text\",\r\n                    \"text\":\r\n                    {\r\n                     \"content\":\"" . $content["data"]["first"]["value"] . "\\n------------------------------\\n" . $content["data"]["keyword1"]["value"] . "\\n------------------------------\\n" . $content["data"]["keyword2"]["value"] . "\\n------------------------------\\n" . $content["data"]["remark"]["value"] . "\"\r\n                    }\r\n                }";
		$texturl = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $ACCESS_TOKEN;
		load()->func("communication");
		$ret = ihttp_post($texturl, $text);
		if ($ret["content"]["errcode"] == "40001") {
			$ret = ihttp_post($texturl, $text);
		}
		return $ret;
	}
	public function _send_template_notice($openid, $page = '', $form_id, $data = array())
	{
		global $_GPC, $_W;
		load()->func("communication");
		load()->classs("wxapp.account");
		$account = pdo_fetch("select * from " . tablename("account_wxapp") . " where acid= '" . $this->_uniacid . "'");
		$accObj = new WxappAccount($account);
		$ACCESS_TOKEN = $accObj->getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$ACCESS_TOKEN}";
		$tempid = $this->module["config"]["templateid"];
		$body = array("touser" => $openid, "template_id" => $tempid, "form_id" => $form_id, "emphasis_keyword" => '');
		if (!empty($page)) {
			$body["page"] = $page;
		}
		$body["data"] = $data;
		return ihttp_post($url, json_encode($body));
	}
	public function doPageIndex()
	{
		$this->result(0, '', array("test" => $this->_uniacid));
	}
	public function doPageHouseinfo()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uid = $_W["member"]["uid"];
		$openid = $_W["openid"];
		$id = $_GPC["id"];
		$fileds = "*, 0+CAST(loyer AS CHAR) as loyer ";
		$sql = "SELECT {$fileds}  FROM " . tablename("kb_sechouse") . " where uniacid='{$uniacid}' and  id='{$id}'";
		$info = pdo_fetch($sql);
		preg_match("/<iframe(.*?)src=\"(.*?)\"/", $info["description"], $arr);
		$info["vurl"] = $arr[2];
		$urlinfo = parse_url($arr[2]);
		if (!empty($urlinfo["query"])) {
			$query = explode("&", $urlinfo["query"]);
			$a2 = array();
			foreach ($query as $q) {
				$a1 = explode("=", $q);
				$a2[$a1[0]] = $a1[1];
			}
		}
		$info["videoid"] = $a2["vid"];
		$furl_real = '';
		if (!empty($info["videoid"])) {
			$a3 = file_get_contents("http://vv.video.qq.com/getinfo?vids=" . $info["videoid"] . "&platform=101001&charge=0&otype=json");
			$a3 = str_replace("QZOutputJson=", '', rtrim($a3, ";"));
			$a3 = json_decode($a3, true);
			$fvkey = $a3["vl"]["vi"][0]["fvkey"];
			$fn = $a3["vl"]["vi"][0]["fn"];
			$furl = $a3["vl"]["vi"][0]["ul"]["ui"][0]["url"];
			$furl_real = $furl . $fn . "?vkey=" . $fvkey;
		}
		$info["videoinfo"] = $furl_real;
		if (!empty($info["video"]) && strpos($info["video"], "mages")) {
			$info["video"] = tomedia($info["video"]);
		} else {
			$info["video"] = '';
		}
		if (empty($info["thumb"])) {
			$info["thumb"] = "addons/wdl_weihouse/style/images/house_face.jpg";
		}
		if (!empty($info["thumb_url"])) {
			$info["thumbs"] = iunserializer($info["thumb_url"]);
			foreach ($info["thumbs"] as $key => $item) {
				$info["thumbs"][$key] = tomedia($item);
			}
		}
		if (empty($info["thumbs"])) {
			$info["thumbs"][] = tomedia($info["thumb"]);
		}
		$info["update_time"] = date("Y-m-d H:i:s", $info["update_time"]);
		$params = pdo_fetchall("select * from " . tablename("kb_sechouse_param") . " where houseid='{$id}' order by displayorder asc");
		foreach ($params as $k => $p) {
			$info["params"][] = $p;
		}
		$default_avatar = "/addons/wdl_weihouse/style/images/get_avatar.png";
		$info["broker"] = array("avatar" => toimage($default_avatar), "nickname" => $info["publish_name"], "mobile" => $info["linkphone"], "company" => "联系人");
		if ($info["broker_id"]) {
			$broker = pdo_fetch("select id,groupid,ecuid, nickname,openid,mobile,company,avatar,vtags from " . tablename("kb_sechouse_broker") . " where id='" . $info["broker_id"] . "'");
			if (!strpos($broker["avatar"], "/")) {
				$broker["avatar"] = $default_avatar;
			}
			$broker["avatar"] = toimage($broker["avatar"]);
			$info["broker"] = $broker;
		}
		$info = $this->_format_sechouse($info);
		if ($id) {
			pdo_update("kb_sechouse", array("onclick +=" => 1), array("id" => $id));
		}
		$houseid = intval($id);
		$fav = pdo_fetch("select id from " . tablename("kb_sechouse_favorite") . " where ftype=1 and houseid='{$houseid}' and uniacid='{$uniacid}' and uid='{$uid}'");
		if (!empty($fav)) {
			pdo_update("kb_sechouse_favorite", array("hits +=" => 1, "last_time" => TIMESTAMP), array("id" => $fav["id"]));
		} else {
			$savedata = array("uid" => $uid, "openid" => $openid, "uniacid" => $uniacid, "addtime" => TIMESTAMP, "houseid" => $houseid, "ftype" => 1, "acttype" => "view", "last_time" => TIMESTAMP, "title" => $info["title"], "smalltext" => "{$info["village_name"]} | {$info["room"]}房{$info["hall"]}厅{$info["garder"]}卫 | {$info["superficie"]}㎡ | {$info["loyer"]}{$info["prix_unitaire"]}");
			pdo_insert("kb_sechouse_favorite", $savedata);
		}
		$this->_send_notice_account("view_notice", "【{$uid}】浏览房源", $info["title"], "发布人：" . $info["publish_name"], "房源浏览量 " . $info["onclick"]);
		$this->result(0, '', $info);
	}
	public function doPageLimithouse()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uid = $_W["member"]["uid"];
		$openid = $_W["openid"];
		$limit = 20;
		if (isset($_GPC["limit"]) && !empty($_GPC["limit"])) {
			$limit = intval($_GPC["limit"]);
		}
		$where = " uniacid='{$uniacid}'  and isdelete=0 and isonline=1 and zhutype in(0,1,2,3) ";
		$addsql = $addsql2 = '';
		$and = '';
		if (isset($_GPC["fkey"]) && !empty($_GPC["fkey"])) {
			foreach ($_GPC["fkey"] as $fkey => $fval) {
				if (in_array($fkey, array("room", "loyer", "rent_type", "zhutype", "istop", "isjingpin", "ishot", "show_jiaji", "show_you", "show_hight"))) {
					if ($fkey == "loyer") {
						$addsql2 .= " or ( loyer > '" . ($fval - 5) . "' and loyer < '" . ($fval + 5) . "' )";
					} else {
						$addsql .= $and . " {$fkey} = '" . $fval . "' ";
						$and = " and ";
					}
				}
			}
			if (!$addsql) {
				$addsql = " 1=1 ";
			}
		}
		$where .= " and ( ( {$addsql}) {$addsql2})";
		if (isset($_GPC["noid"]) && !empty($_GPC["noid"])) {
			$noid = intval($_GPC["noid"]);
			$where .= " and id !='{$noid}' ";
		}
		$fileds = $this->_query_sechouse_field();
		$sql = "SELECT {$fileds} FROM " . tablename("kb_sechouse") . " where  {$where}  order by refresh_time desc,update_time desc limit {$limit} ";
		$items = pdo_fetchall($sql);
		foreach ($items as $key => $val) {
			$val = $this->_format_sechouse($val);
			$items[$key] = $this->_format_show_item($val);
		}
		$this->result(0, "{$sql}", $items);
	}
	public function _format_show_item($val)
	{
		if ($val["rent_type"] == 0 && $val["zhutype"] == 0) {
			$val["item_address"] = $val["room"] . "房" . $val["hall"] . "厅 / " . $val["superficie"] . "㎡ / " . $val["donner_sur"];
			$val["item_price"] = $val["loyer"] . $val["prix_unitaire"];
			$val["item_per"] = $val["perprice"] ? $val["perprice"] . $val["perprice_unit"] : '';
			$val["quan"] = $val["quan"] ? $val["quan"] : $val["village_name"];
			if ($val["quan"] == "-") {
				$val["quan"] = $val["village_name"];
			}
			$val["item_area"] = $val["area"] . " " . $val["quan"];
			$val["item_time"] = '';
			$val["showtag"] = 1;
		} else {
			if ($val["rent_type"] == 2 && $val["zhutype"] == 0) {
				$val["item_address"] = $val["room"] . "房" . $val["hall"] . "厅 / " . $val["superficie"] . "㎡   ";
				$val["item_price"] = $val["loyer"] . $val["prix_unitaire"];
				$val["item_per"] = $val["menpai"];
				$val["quan"] = $val["quan"] ? $val["quan"] : $val["village_name"];
				if ($val["quan"] == "-") {
					$val["quan"] = $val["village_name"];
				}
				$val["item_area"] = $val["area"] . " " . $val["quan"];
				$val["item_time"] = '';
				$val["showtag"] = 1;
			} else {
				if ($val["zhutype"] == 1) {
					$val["item_address"] = $val["village_name"] . "  " . $val["superficie"] . "㎡   ";
					$val["item_price"] = $val["loyer"] . $val["prix_unitaire"];
					$val["item_per"] = '';
					$val["item_area"] = $val["area"] . "  " . $val["quan"];
					$val["item_time"] = $val["perprice"] ? $val["perprice"] . $val["perprice_unit"] : '';
					if ($val["rent_type"] == 0) {
						$val["item_time"] = "单价 " . $val["item_time"];
					} else {
						$val["item_time"] = "租金 " . $val["item_time"];
					}
					$val["showtag"] = 0;
				} else {
					if ($val["zhutype"] == 2) {
						$val["item_address"] = $val["village_name"] . "  " . $val["superficie"] . "㎡   ";
						$val["item_price"] = $val["loyer"] . $val["prix_unitaire"];
						$val["item_per"] = '';
						$val["item_area"] = $val["area"] . "  " . $val["quan"];
						$val["item_time"] = $val["perprice"] ? $val["perprice"] . $val["perprice_unit"] : '';
						if ($val["rent_type"] == 0) {
							$val["item_time"] = "单价 " . $val["item_time"];
						} else {
							$val["item_time"] = "租金 " . $val["item_time"];
						}
						$val["showtag"] = 0;
					} else {
						if ($val["zhutype"] == 3) {
							$val["item_address"] = $val["village_name"] . "  " . $val["superficie"] . "㎡   ";
							$val["item_price"] = $val["loyer"] . $val["prix_unitaire"];
							$val["item_per"] = "转让费 " . $val["dong"] . "万";
							$val["item_area"] = $val["area"] . "  " . $val["quan"];
							$val["item_time"] = $val["perprice"] ? $val["perprice"] . $val["perprice_unit"] : '';
							$val["item_time"] = "租金" . $val["item_time"];
							$val["showtag"] = 0;
						} else {
							if ($val["zhutype"] == 4) {
								$val["item_address"] = $val["village_name"];
								$val["item_price"] = $val["tags"][1];
								$val["item_per"] = "面积：" . $val["tags"][0];
								$val["item_area"] = $val["area"] . "  " . $val["quan"];
								$val["item_time"] = $val["perprice"] ? $val["perprice"] . $val["perprice_unit"] : '';
								$val["item_time"] = $val["room"] . "房" . $val["hall"] . "厅" . $val["garden"] . "卫  ";
								$val["showtag"] = 0;
							}
						}
					}
				}
			}
		}
		return $val;
	}
	public function _format_sechouse($item)
	{
		$val = $item;
		if (empty($val["thumb"])) {
			$val["thumb"] = "addons/wdl_weihouse/style/images/house_face.jpg";
		}
		if (!strpos($val["thumb"], "mages") && !strpos($val["thumb"], "weihouse")) {
			$val["thumb"] = "addons/wdl_weihouse/style/images/house_face.jpg";
		}
		$val["thumb"] = toimage($val["thumb"]);
		$s = max(1, $val["superficie"]);
		if ($val["loyer"] == 0) {
			$val["loyer"] = "面议";
			$val["prix_unitaire"] = '';
			$val["first_price"] = "-";
			$val["perprice"] = "-";
		} else {
			$val["prix_unitaire"] = rtrim($val["prix_unitaire"], "元");
			$val["perprice"] = ceil($val["loyer"] * 10000 / $s);
			$val["first_price"] = ceil($val["loyer"] * 40 / 100);
		}
		$val["perprice"] = ceil($val["loyer"] * 10000 / $s);
		$val["perprice_unit"] = "元/㎡";
		if ($val["rent_type"] == 2 && $val["zhutype"] > 0) {
			$val["perprice"] = ceil($val["loyer"] / $val["superficie"]);
			$val["perprice_unit"] = "元/㎡/月";
		}
		if ($val["rent_type"] == 2 && $val["zhutype"] == 2) {
			$val["perprice"] = ceil($val["loyer"] / $val["superficie"] / 30);
			$val["perprice_unit"] = "元/㎡·天";
		}
		if ($val["rent_type"] == 0 && $val["zhutype"] == 3) {
			$val["perprice"] = ceil($val["loyer"] / $val["superficie"] / 30);
			$val["perprice_unit"] = "元/㎡·天";
		}
		$perbill = $val["qq"] ? $val["qq"] : 40;
		$val["bill"] = $perbill;
		$val["firstpay"] = $val["loyer"] * $perbill / 100;
		$val["quan"] = $val["quan"] ? $val["quan"] : "-";
		$val["infotype"] = $val["rent_type"] == 2 ? "出租" : "出售";
		$val["refresh_time"] = $val["refresh_time"] ? $val["refresh_time"] : $val["add_time"];
		if ($val["refresh_time"] > $val["add_time"]) {
			$val["add_time"] = $val["refresh_time"];
		}
		$val["add_time"] = date("Y-m-d H:i", $val["add_time"]);
		$val["refresh_time"] = date("Y-m-d H:i", $val["refresh_time"]);
		$tags = $val["disposition"];
		$val["tags_all"] = $val["tags"] = array($val["infotype"], $val["house_type"] ? $val["house_type"] : '', $val["traveaux_finition"] ? $val["traveaux_finition"] : '');
		if (!empty($tags)) {
			$tag = explode(",", $tags);
			$val["tags_all"] = $tag;
			$tmp = array();
			foreach ($tag as $kk => $vv) {
				if ($kk > 3) {
					break;
				}
				if (!empty($vv)) {
					array_push($tmp, $vv);
				}
			}
			$val["tags"] = $tmp;
		}
		if ($val["zhutype"] == "3") {
			$tmp = explode(",", $val["disposition"]);
			$val["hangye"] = $tmp[0];
			$val["jingyin"] = $tmp[1];
		}
		$val["helpinfo"] = "温馨提示：请去电时说明在《" . $this->module["config"]["shopname"] . "》上看到，谢谢！";
		$val["pushtype"] = $val["ptype"] == 1 ? "个人" : "中介";
		if ($this->module["config"]["isallphone"] == 1) {
			$val["linkphone"] = $this->module["config"]["phone"];
			$val["broker"]["mobile"] = $val["linkphone"];
		}
		if (empty($val["employee"])) {
			$val["employee"] = '';
		}
		return $val;
	}
	public function doPagePostsechouse()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uid = $_W["member"]["uid"];
		$openid = $_W["openid"];
		$fileds = array("title", "area", "quan", "village_name", "house_sno", "room", "hall", "garder", "superficie", "loyer", "prix_unitaire", "prix_unitaire", "house_type", "zhutype", "dong", "isdelete", "traveaux_finition", "storey", "total_storey", "donner_sur", "build_age", "description", "publish_name", "linkphone", "vrurl", "video");
		foreach ($fileds as $field) {
			$data[$field] = $_GPC[$field];
		}
		if (!empty($_GPC["thumbs"])) {
			$thumb = explode(",", $_GPC["thumbs"]);
			$data["thumb_url"] = serialize($thumb);
			$data["thumb"] = $thumb[0];
		}
		if (!empty($_GPC["latlon"])) {
			$data["mapinfo"] = $_GPC["latlon"];
		} else {
			$data["mapinfo"] = $this->_address_to_point($this->module["config"]["cityname"] . $data["area"] . $data["village_name"]);
		}
		if (isset($_GPC["disposition"]) && !empty($_GPC["disposition"])) {
			$data["disposition"] = $_GPC["disposition"];
		}
		$data["ptype"] = 0;
		$broker = pdo_fetch("select id,groupid from " . tablename("kb_sechouse_broker") . " where openid='{$openid}' and uniacid='{$uniacid}'");
		if ($broker["id"] > 0) {
			$data["broker_id"] = $broker["id"];
			$data["broker_name"] = $openid;
			if ($broker["groupid"] == 0) {
				$data["ptype"] = 1;
			}
		} else {
			$data["broker_id"] = 0;
			$data["broker_name"] = $openid;
			$data["ptype"] = 1;
		}
		$data["rent_type"] = $_GPC["rent_type"];
		if (empty($_GPC["prix_unitaire"])) {
			$data["prix_unitaire"] = $_GPC["rent_type"] == 2 ? "元/月" : "万";
		} else {
			$data["prix_unitaire"] = $_GPC["prix_unitaire"];
		}
		$data["uniacid"] = intval($uniacid);
		$data["endtime"] = strtotime("+15 day");
		$data["update_time"] = TIMESTAMP;
		$data["refresh_time"] = TIMESTAMP;
		$data["description"] = htmlspecialchars_decode($data["description"]);
		if ($data["prix_unitaire"] == "元/㎡/月") {
			$data["danyuan"] = $data["loyer"];
			$data["menpai"] = $data["prix_unitaire"];
			$data["loyer"] = $data["danyuan"] * $data["superficie"];
			$data["prix_unitaire"] = "元/月";
		} else {
			$data["danyuan"] = '';
			$data["menpai"] = '';
		}
		$data["loyer"] = sprintf("%.2f", $data["loyer"]);
		$data["iscompany"] = 9;
		$id = intval($_GPC["id"]);
		if (empty($id)) {
			$data["add_time"] = TIMESTAMP;
			$data["refresh_time"] = TIMESTAMP;
			$data["isonline"] = 1;
			if ($this->module["config"]["system"]["needcheck"] == 1) {
				$data["isdelete"] = 1;
			} else {
				$data["isdelete"] = 0;
			}
			$ret = pdo_insert("kb_sechouse", $data);
			$id = pdo_insertid();
			if ($_GPC["needpay"] == 1 && $_GPC["need_credit"]) {
				load()->model("mc");
				$credit = $_GPC["need_credit"];
				mc_credit_update($uid, "credit2", -$credit, array($uid, "发布房源使用余额：" . $credit));
				pdo_insert("kb_sechouse_actlog", array("actname" => "publish", "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "money" => $credit, "ecuid" => $uid, "acttype" => 3, "isadd" => 0, "infoid" => $id, "uniacid" => $uniacid, "note" => "发布房源使用余额"));
			}
		} else {
			unset($data["isdelete"]);
			$ret = pdo_update("kb_sechouse", $data, array("id" => $id));
		}
		$this->_send_notice_account("send_notice", "有新的房源发布", $_GPC["title"], "户型" . $_GPC["room"] . "房" . $_GPC["house_type"], "房源ID " . $id);
		$info = pdo_fetch("select broker_id,broker_name from " . tablename("kb_sechouse") . " where id='{$id}' and uniacid='{$uniacid}'");
		$this->count_broker_send_num($info["broker_id"], $info["broker_name"]);
		$this->result(0, "保存成功", $data);
	}
	public function count_broker_send_num($broker_id, $openid = '')
	{
		global $_GPC, $_W;
		if (empty($openid)) {
			$openid = $_W["openid"];
		}
		if ($broker_id > 0) {
			$count1 = pdo_fetchcolumn("select count(*) from " . tablename("kb_sechouse") . " where broker_id='{$broker_id}' and isonline=1 and isdelete=0 and rent_type=0");
			$count2 = pdo_fetchcolumn("select count(*)from " . tablename("kb_sechouse") . " where broker_id='{$broker_id}' and isonline=1 and isdelete=0 and rent_type=2");
			pdo_query("update " . tablename("kb_sechouse_broker") . " set secnum='{$count1}', ernum='{$count2}' where id='{$broker_id}'");
		} else {
			$count1 = pdo_fetchcolumn("select count(*) from " . tablename("kb_sechouse") . " where broker_name='{$openid}' and isonline=1 and isdelete=0 and rent_type=0");
			$count2 = pdo_fetchcolumn("select count(*)from " . tablename("kb_sechouse") . " where broker_name='{$openid}' and isonline=1 and isdelete=0 and rent_type=2");
			pdo_query("update " . tablename("kb_sechouse_broker") . " set secnum='{$count1}', ernum='{$count2}' where openid='{$openid}'");
		}
	}
	public function doPageSearchkey()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uid = $_W["member"]["uid"];
		$openid = $_W["openid"];
		$sql = "SELECT `settings` FROM " . tablename("uni_account_modules") . " WHERE `uniacid` = :uniacid AND `module` = :module";
		$settings = pdo_fetchcolumn($sql, array(":uniacid" => $uniacid, ":module" => "wdl_weihouse"));
		$sec = iunserializer($settings);
		$p["areas"] = form_element::_option($sec["areas"]);
		$quan = pdo_fetchall("select * from " . tablename("kb_sechouse_area") . " where uniacid='{$uniacid}' and type=2 order by orderid desc ,id desc");
		if (!empty($quan)) {
			$tmp = array();
			foreach ($quan as $key => $q) {
				$tmp[$q["area"]][] = $q["name"];
			}
			foreach ($p["areas"] as $k => $pv) {
				$p["quan"][$k] = $tmp[$pv];
			}
		}
		$p["zu_price"] = form_element::_option($sec["search_zhu_price"]);
		if (intval($_GPC["type"] == 2)) {
			$p["price"] = form_element::_option($sec["search_zhu_price"]);
		} else {
			if ($_GPC["type"] == 1) {
				$p["price"] = form_element::_option($sec["search_newhouse_price"]);
			} else {
				if ($_GPC["type"] == 10) {
					$p["price"] = form_element::_option($sec["search_xie_sou_price"]);
				} else {
					if ($_GPC["type"] == 12) {
						$p["price"] = form_element::_option($sec["search_xie_zhu_price"]);
					} else {
						if ($_GPC["type"] == 20) {
							$p["price"] = form_element::_option($sec["search_sp_sou_price"]);
						} else {
							if ($_GPC["type"] == 22) {
								$p["price"] = form_element::_option($sec["search_sp_zhu_price"]);
							} else {
								if ($_GPC["type"] == 30) {
									$p["price"] = form_element::_option($sec["search_syi_sou_price"]);
								} else {
									$p["price"] = form_element::_option($sec["search_sec_price"]);
								}
							}
						}
					}
				}
			}
		}
		$p["housetype"] = form_element::_option($this->_forms["housetype"]);
		$p["sptype"] = form_element::_option($this->_forms["sptype"]);
		$p["chaoxiang"] = form_element::_option($this->_forms["chaoxiang"]);
		$p["zhaungxiu"] = form_element::_option($this->_forms["zhaungxiu"]);
		$p["disposition"] = form_element::_option($this->_forms["disposition"]);
		$p["rent_zhu"] = form_element::_option($this->_forms["rent_zhu"]);
		$p["hangye"] = form_element::_option($this->_forms["hangye"]);
		$p["jingyin"] = $this->_forms["jingyin"];
		$years = date("Y");
		$start = 1987;
		$p["years"] = array();
		$i = 0;
		while ($i < $years - $start) {
			array_push($p["years"], $years - $i);
			$i++;
		}
		$p["sufix"] = form_element::_option($sec["search_sec_sufix"]);
		$p["room"] = array("一房" => 1, "两房" => 2, "三房" => 3, "四房" => 4, "四房以上" => 5);
		$p["mainhouse"] = $this->other["hxtype"];
		$p["order"] = array("默认排序" => 1, "单价由低到高" => 2, "单价由高到低" => 3, "开盘时间顺序" => 4, "开盘时间倒序" => 5);
		$p["housetags"] = $this->other["housetags"];
		$p["projectTypes"] = $this->other["projectTypes"];
		$p["opentime"] = $this->other["opentime"];
		$p["houseyears"] = array("2年内" => 1, "2-5年" => 2, "5-10年" => 3, "10年上" => 4);
		$p["ptype"] = array("个人" => 1, "经纪人" => 2);
		$p["secorder"] = array("发布时间最新" => 1, "更新时间最新" => 2, "价格从高到低" => 3);
		$this->result(0, '', $p);
	}
	public function doPageSearch()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uid = $_W["member"]["uid"];
		$openid = $_W["openid"];
		$search["keyword"] = trim($_GPC["keyword"]);
		$search["area"] = trim($_GPC["area"]);
		$search["minprice"] = trim($_GPC["minprice"]);
		$search["maxprice"] = trim($_GPC["maxprice"]);
		$search["minsufix"] = trim($_GPC["minsufix"]);
		$search["maxsufix"] = trim($_GPC["maxsufix"]);
		$search["room"] = trim($_GPC["room"]);
		$search["isjingping"] = intval($_GPC["isjingping"]);
		$search["istop"] = intval($_GPC["istop"]);
		$rent_type = intval($_GPC["rent_type"]);
		if (isset($_GPC["rent_type"])) {
			$rent_type = intval($_GPC["rent_type"]);
		}
		$search["zhutype"] = trim($_GPC["zhutype"]);
		$condition = " uniacid='{$uniacid}' and isonline=1 and isdelete=0 and rent_type='{$rent_type}'";
		if (isset($_GPC["isbroker"]) && $_GPC["isbroker"] == 1) {
			$brokerid = intval($_GPC["bid"]);
			if ($brokerid > 0) {
				$condition = " uniacid='{$uniacid}' and broker_id='{$brokerid}' and rent_type='{$rent_type}'";
			} else {
				$brokerid = pdo_fetchcolumn("select id from " . tablename("kb_sechouse_broker") . " where openid='{$openid}' and uniacid='{$uniacid}'");
				if ($brokerid > 0) {
					$condition = " uniacid='{$uniacid}' and broker_id='{$brokerid}' and rent_type='{$rent_type}'";
				} else {
					$condition = " uniacid='{$uniacid}' and broker_name='{$openid}' and rent_type='{$rent_type}'";
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
		if (!empty($search["room"])) {
			$condition .= " and room='{$search["room"]}'";
		}
		if ($search["isjingping"] == 1) {
			$condition .= " and isjingpin='{$search["isjingping"]}'";
		}
		if (!empty($search["zhutype"])) {
			$condition .= " and zhutype in ({$search["zhutype"]})";
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
		$sql = "SELECT {$fileds}  FROM " . tablename("kb_sechouse") . " where {$condition} order by update_time desc ,refresh_time desc limit {$startlimit}, {$pagesize}";
		$items = pdo_fetchall($sql);
		if (!empty($items)) {
			foreach ($items as $key => $val) {
				$items[$key] = $this->_format_sechouse($val);
			}
			$this->result(0, "查询成功 {$sql}", $items);
		} else {
			$this->result(0, "{$sql}", $items);
		}
	}
	public function _query_sechouse_field()
	{
		return " id,title, 0+CAST(loyer AS CHAR) as loyer,traveaux_finition,donner_sur,area,quan,prix_unitaire,storey,\r\n            superficie,total_storey,update_time,endtime,house_type,isonline,isdelete,company,broker_id,publish_name,\r\n            isjingpin,ishot,istop,show_jiaji,show_you,show_hight,disposition,disposition,zhutype,dong,ptype,danyuan, menpai,\r\n            thumb,room,hall,garder,village_name,build_age ,rent_type,add_time,onclick,refresh_time";
	}
	public function doPageRuntask()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uid = $_W["member"]["uid"];
		$openid = $_W["openid"];
		$sql = "select * from " . tablename("kb_sechouse_actlog") . " where actname='istop' and uniacid='{$uniacid}' and ( unix_timestamp(addtime)+(num*3600*24))< unix_timestamp(now()) and isadd=1 and mark is null";
		$logs = pdo_fetchall($sql);
		$dot = $infoids = $logids = '';
		if (!empty($logs)) {
			foreach ($logs as $key => $val) {
				$infoids .= $dot . $val["infoid"];
				$logids .= $dot . $val["id"];
				$dot = ",";
			}
		}
		if ($infoids != '') {
			pdo_query("update " . tablename("kb_sechouse") . " set isotp=0 where uniacid='{$uniacid}' and id in({$infoids})");
			pdo_query("update " . tablename("kb_sechouse_actlog") . " set mark='hasend' where uniacid='{$uniacid}' and id in({$logids})");
		}
		pdo_query("update " . tablename("kb_sechouse_broker") . " set groupid=1 where uniacid='{$uniacid}' and end_time < now() ");
		$this->result(0, $infoids . $sql, array());
	}
	public function doPageUpdate()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$openid = $_W["openid"];
		$uid = $_W["member"]["uid"];
		$allow = array("sec", "kb_sechouse", "kb_sechouse_broker", "kb_sechouse_shop");
		$enews = $_GPC["enews"];
		$tablename = $_GPC["tablename"];
		$value = $_GPC["value"];
		$id = $_GPC["id"];
		if ($enews == "down" && $tablename == 1) {
			pdo_update($allow[$tablename], array("isonline" => $value == 1 ? "0" : "1"), array("id" => $id, "uniacid" => $uniacid));
			$info = pdo_fetch("select broker_id,broker_name from " . tablename("kb_sechouse") . " where id='{$id}' and uniacid='{$uniacid}'");
			$this->count_broker_send_num($info["broker_id"], $info["broker_name"]);
			pdo_insert("kb_sechouse_actlog", array("actname" => "isonline", "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "num" => 1, "ecuid" => $uid, "acttype" => 4, "infoid" => $id, "uniacid" => $uniacid, "note" => "房源上下架操作_"));
			$this->result(0, "操作成功房源下架上架" . $allow[$tablename]);
		}
		if ($enews == "setattr" && $tablename == 1) {
			$field = $_GPC["field"];
			$days = 1;
			if (isset($_GPC["topday"]) && !empty($_GPC["topday"])) {
				$days = intval($_GPC["topday"]);
			}
			pdo_update($allow[$tablename], array($field => $value), array("id" => $id, "uniacid" => $uniacid));
			pdo_insert("kb_sechouse_actlog", array("actname" => $field, "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "isadd" => $value, "num" => $days, "ecuid" => $uid, "acttype" => 4, "infoid" => $id, "uniacid" => $uniacid, "note" => "设置房源推荐位"));
			if ($_GPC["ispay"] == 1 && $value == 1) {
				$prive = $this->_user_prive();
				load()->model("mc");
				$credit = $prive["credit"]["istop"];
				$credit = $credit * $days;
				mc_credit_update($uid, "credit2", -$credit, array($uid, "置顶房源使用余额：" . $credit));
				pdo_insert("kb_sechouse_actlog", array("actname" => "downcredit2", "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "money" => $credit, "ecuid" => $uid, "acttype" => 3, "isadd" => 0, "infoid" => $id, "uniacid" => $uniacid, "note" => "置顶房源使用余额"));
			}
			$this->result(0, "设置房源属性成功" . $allow[$tablename]);
		}
		if ($enews == "refresh" && $tablename == 1) {
			$prive = $this->_user_prive();
			$limit = $prive["refresh_num"];
			$sql = "select SUM(num) from " . tablename("kb_sechouse_actlog") . " where infoid='{$id}' and actname='refresh' and uniacid='{$uniacid}'  and (left(`addtime`,10)=left(NOW(),10)) ";
			$ret = pdo_fetchcolumn($sql);
			$info["end_total"] = max($limit - $ret - 1, 0);
			$info["refresh_time"] = date("Y-m-d H:i:s", TIMESTAMP);
			$canrefresh = $limit > $ret;
			if ($_GPC["ispay"] == 1) {
				$prive = $this->_user_prive();
				load()->model("mc");
				$credit = $prive["credit"]["refresh"];
				mc_credit_update($uid, "credit2", -$credit, array($uid, "刷新房源使用余额：" . $credit));
				pdo_insert("kb_sechouse_actlog", array("actname" => "downcredit2", "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "money" => $credit, "ecuid" => $uid, "acttype" => 3, "isadd" => 0, "infoid" => $id, "uniacid" => $uniacid, "note" => "刷新房源使用余额"));
				$canrefresh = true;
				$info["end_total"] = 0;
			}
			if (!$canrefresh) {
				$info["refresh_total"] = $ret;
				$info["end_total"] = 0;
			} else {
				$info["refresh_total"] = $ret + 1;
				pdo_insert("kb_sechouse_actlog", array("actname" => "refresh", "addtime" => $info["refresh_time"], "num" => 1, "ecuid" => $uid, "acttype" => 4, "infoid" => $id, "uniacid" => $uniacid, "note" => "刷新房源"));
				pdo_update("kb_sechouse", array("refresh_time" => TIMESTAMP), array("id" => $id, "uniacid" => $uniacid));
			}
			$this->result(0, "刷新次数达到上限{$sql}", $info);
		}
		if ($enews == "delete" && $tablename == 1) {
			pdo_delete($allow[$tablename], array("id" => $id, "uniacid" => $uniacid));
			$info = pdo_fetch("select broker_id,broker_name from " . tablename("kb_sechouse") . " where id='{$id}' and uniacid='{$uniacid}'");
			$this->count_broker_send_num($info["broker_id"], $info["broker_name"]);
			$this->result(0, "删除房源成功" . $allow[$tablename]);
		}
		if ($enews == "fetch" && $id) {
			$info = pdo_fetch("select * from " . tablename($allow[$tablename]) . " where id='{$id}' and uniacid='{$uniacid}'");
			$info["attachs"] = unserialize($info["thumb_url"]);
			if ($info["attachs"]) {
				foreach ($info["attachs"] as $key => $val) {
					$tmp[$key] = tomedia($val);
				}
				$info["attachs_url"] = $tmp;
			}
			if ($info["zhutype"] == "3") {
				$tmp = explode(",", $info["disposition"]);
				$info["hangye"] = $tmp[0];
				$info["jingyin"] = $tmp[1];
			}
			if ($info["zhutype"] == "4") {
				$tmp = explode(",", $info["disposition"]);
				$info["sufix"] = $tmp[0];
				$info["price"] = $tmp[1];
			}
			if ($info["danyuan"] && $info["menpai"]) {
				$info["loyer"] = $info["danyuan"];
				$info["prix_unitaire"] = $info["menpai"];
			}
			if ($info["video"]) {
				$info["video_src"] = tomedia($info["video"]);
			}
			$this->result(0, "查询一条房源" . $allow[$tablename], $info);
		}
		if ($enews == "managesechouse" && $id) {
			$prive = $this->_user_prive();
			$refresh_num = $prive["refresh_num"];
			$istop_num = $prive["istop_num"];
			$info = pdo_fetch("select * from " . tablename($allow[$tablename]) . " where id='{$id}' and uniacid='{$uniacid}'");
			$ref = $info["refresh_time"] ? $info["refresh_time"] : $info["update_time"];
			$info["refresh_time"] = date("Y-m-d H:i:s", $ref);
			$sql = "select SUM(num) from " . tablename("kb_sechouse_actlog") . " where infoid='{$id}' and actname='refresh' and uniacid='{$uniacid}'  and (left(`addtime`,10)=left(NOW(),10)) ";
			$ret = pdo_fetchcolumn($sql);
			if (!empty($ret)) {
				$info["refresh_total"] = $ret;
				$info["end_total"] = max(0, $refresh_num - $ret);
			} else {
				$info["refresh_total"] = 0;
				$info["end_total"] = $refresh_num;
			}
			$broker_id = pdo_fetchcolumn("select id from " . tablename("kb_sechouse_broker") . " where uniacid='{$uniacid}' and openid='{$openid}'");
			if ($broker_id) {
				$sql = "select count(*) from " . tablename("kb_sechouse") . " where istop=1 and uniacid='{$uniacid}' and  broker_id='{$broker_id}'";
			} else {
				$sql = "select count(*) from " . tablename("kb_sechouse") . " where istop=1 and uniacid='{$uniacid}' and  broker_name='{$openid}'";
			}
			$hasnum = pdo_fetchcolumn($sql);
			$info["istop_total"] = $hasnum;
			$info["istop_end_total"] = max(0, $istop_num - $hasnum);
			$info["prive"] = $prive;
			$info["member"] = $_W["member"];
			$this->result(0, "查询一条房源 {$openid} " . $sql, $info);
		}
		if ($enews == "buygroup") {
			if (isset($_GPC["needcredit"])) {
			}
			$this->result(0, "升级成功" . $credit, $this->_user_prive());
		}
		if ($enews == "transjifen") {
			$trans = intval($_GPC["credit"]);
			$fee = intval($_GPC["money"]);
			if ($fee > intval($_W["member"]["credit1"])) {
				return $this->result(1, "账号积分不足");
			}
			load()->model("mc");
			pdo_insert("kb_sechouse_actlog", array("actname" => "transjifen", "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "money" => $fee, "ecuid" => $uid, "acttype" => 1, "isadd" => 0, "infoid" => 0, "uniacid" => $uniacid, "note" => "积分兑换成"));
			pdo_insert("kb_sechouse_actlog", array("actname" => "downcredit1", "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "money" => $fee, "ecuid" => $uid, "acttype" => 8, "isadd" => 0, "infoid" => 0, "uniacid" => $uniacid, "note" => "积分兑换成余额消费积分"));
			pdo_insert("kb_sechouse_actlog", array("actname" => "addcredit2", "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "money" => $trans, "ecuid" => $uid, "acttype" => 2, "isadd" => 1, "infoid" => 0, "uniacid" => $uniacid, "note" => "积分兑换成余额"));
			mc_credit_update($uid, "credit1", -$fee, array($uid, $fee . "积分兑换余额：" . $trans));
			mc_credit_update($uid, "credit2", +$trans, array($uid, $fee . "积分兑换余额：" . $trans));
			return $this->result(0, '', array($fee, $trans));
		}
	}
	public function doPageUserprive()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$openid = $_W["openid"];
		$uid = $_W["member"]["uid"];
		$info["prive"] = $this->_user_prive();
		$info["member"] = $_W["member"];
		if ($info["prive"]["group_name"] == "per") {
			$info["house_total"] = pdo_fetchcolumn("select count(*) from " . tablename("kb_sechouse") . " where broker_name='{$openid}' and uniacid='{$uniacid}'");
		} else {
			$broker_id = $info["prive"]["broker"]["id"];
			$info["house_total"] = pdo_fetchcolumn("select count(*) from " . tablename("kb_sechouse") . " where broker_id='{$broker_id}' and uniacid='{$uniacid}'");
		}
		$info["need_pay_credit"] = $info["prive"]["credit"]["send"];
		$info["credit_send_ok"] = 1;
		if ($info["prive"]["send_num"] > $info["house_total"]) {
			$info["needpay"] = 0;
		} else {
			$info["needpay"] = 1;
			if (round($info["member"]["credit2"]) > round($info["prive"]["credit"]["send"])) {
				$info["credit_send_ok"] = 1;
			} else {
				$info["credit_send_ok"] = 0;
			}
		}
		$this->result(0, "查询一条房源 {$openid} " . $sql, $info);
	}
	public function _user_prive()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$openid = $_W["openid"];
		$uid = $_W["member"]["uid"];
		$setting = $this->_getSetting();
		$broker = pdo_fetch("select id, groupid,ecuid,nickname,mobile,company,end_time,secnum,ernum from " . tablename("kb_sechouse_broker") . " where openid='{$openid}' and uniacid='{$uniacid}'");
		if (!empty($broker)) {
			if ($broker["groupid"] == 2) {
				$group_name = "broker2";
				$prive = $setting["broker2"];
			} else {
				if ($broker["groupid"] == 1) {
					$group_name = "broker";
					$prive = $setting["broker"];
				} else {
					$group_name = "per";
					$prive = $setting["per"];
				}
			}
		} else {
			$group_name = "per";
			$prive = $setting["per"];
			$broker = array("id" => 0, "groupid" => 0, "ecuid" => $uid, "nickname" => '', "mobile" => '', "company" => '', "secnum" => 0, "ernum" => 0);
		}
		$prive["send_num"] = intval($prive["send_num"]);
		$prive["refresh_num"] = intval($prive["refresh_num"]);
		$prive["istop_num"] = intval($prive["istop_num"]);
		$prive["group_name"] = $group_name;
		$prive["credit"] = $setting["credit"];
		$prive["broker"] = $broker;
		$prive["member"] = $_W["member"];
		$prive["leve"]["broker2"] = $setting["broker2"];
		$prive["leve"]["broker"] = $setting["broker"];
		$prive["leve"]["per"] = $setting["per"];
		return $prive;
	}
	public function doPagePay()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$openid = $_W["openid"];
		$uid = $_W["member"]["uid"];
		$fee = intval($_GPC["money"]);
		$actname = trim($_GPC["actname"]);
		pdo_insert("kb_sechouse_actlog", array("actname" => $actname, "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "money" => $fee, "ecuid" => $uid, "acttype" => 9, "infoid" => 0, "uniacid" => $uniacid, "note" => "发起微信支付订单"));
		$orderid = pdo_insertid();
		$order = array("tid" => $orderid, "user" => $openid, "fee" => floatval($fee), "title" => "微信支付");
		$pay_params = $this->pay($order);
		if (is_error($pay_params)) {
			return $this->result(1, "支付失败，请重试");
		}
		return $this->result(0, '', $pay_params);
	}
	public function payResult($log)
	{
		global $_GPC, $_W;
		$uniacid = $log["uniacid"];
		$openid = $_W["openid"];
		$uid = $log["tag"]["uid"];
		if ($log["tid"]) {
			$sn = $log["tid"];
			$order = pdo_fetch("select * from " . tablename("kb_sechouse_actlog") . " where uniacid='{$uniacid}' and ecuid='{$uid}' and id='{$sn}'");
			if ($log["from"] == "notify") {
				if ($order["actname"] == "makeorder") {
					$this->_pay_notice_credit2($log, $order);
				} else {
					if ($order["actname"] == "buygroup_pay") {
						$this->_pay_notice_buygroup($log, $order);
					}
				}
				pdo_update("kb_sechouse_actlog", array("mark" => "success"), array("id" => $order["id"]));
				return $this->result(0, "支付成功", $log);
			}
		}
		return $this->result(0, "支付失败", $log);
	}
	public function _pay_notice_buygroup($log, $order)
	{
		global $_GPC, $_W;
		$uniacid = $log["uniacid"];
		$uid = $log["tag"]["uid"];
		if ($order["mark"] != "success") {
			load()->model("mc");
			$credit = $order["money"];
			$setting = $this->_getSetting();
			$jifen = intval($setting["credit"]["buygroup_send_credit1"]);
			if ($jifen > 0) {
				mc_credit_update($uid, "credit1", $jifen, array($uid, "认证VIP经纪人送积分：" . $jifen));
				pdo_insert("kb_sechouse_actlog", array("actname" => "addcredit1", "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "money" => $jifen, "ecuid" => $uid, "acttype" => 7, "isadd" => 1, "infoid" => 0, "uniacid" => $uniacid, "note" => "认证VIP经纪人送积分"));
			}
			pdo_insert("kb_sechouse_actlog", array("actname" => "buygroup", "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "money" => $credit, "ecuid" => $uid, "acttype" => 1, "isadd" => 0, "infoid" => 0, "uniacid" => $uniacid, "note" => "升级经纪人为VIP认证"));
			$log_id = pdo_insertid();
			$endtime = date("Y-m-d H:i:s", strtotime("+360 day"));
			pdo_query("update " . tablename("kb_sechouse_broker") . " set groupid=2\r\n                ,buygroup_log_id = '{$log_id}', end_time='{$endtime}'\r\n                where uniacid='{$uniacid}'  and ecuid='{$uid}'");
		}
		$this->_send_notice_account("buygroup_notice", "【{$uid}】在线认证经纪人", "升级经纪人为VIP认证", "金额：{$credit}元", "奖励积分：{$jifen}");
		return true;
	}
	public function _calcute_bill($money)
	{
		$bd = $this->module["config"]["credit"];
		$credit = 0;
		$money = intval($money);
		if ($bd["bill_method"] == 2) {
			if ($money >= intval($bd["jieti3"])) {
				$credit = $bd["jieti3_bill"];
			} else {
				if ($money >= intval($bd["jieti2"])) {
					$credit = $bd["jieti2_bill"];
				} else {
					if ($money >= intval($bd["jieti1"])) {
						$credit = $bd["jieti1_bill"];
					} else {
						$credit = $money;
					}
				}
			}
		} else {
			$bill = intval($bd["bill"]);
			if (!$bill) {
				$bill = 10;
			}
			$credit = $money * $bd["bill"];
		}
		return $credit;
	}
	public function _pay_notice_credit2($log, $order)
	{
		global $_GPC, $_W;
		$uniacid = $log["uniacid"];
		$uid = $log["tag"]["uid"];
		$setting = $this->_getSetting();
		$bill = 10;
		if (isset($setting["credit"]["bill"])) {
			$bill = intval($setting["credit"]["bill"]);
		}
		$fee = $order["money"];
		$credit = $this->_calcute_bill($fee);
		load()->model("mc");
		mc_credit_update($uid, "credit1", $credit, array($uid, "余额充值奖励积分：" . $credit));
		pdo_insert("kb_sechouse_actlog", array("actname" => "addcredit1", "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "money" => $credit, "ecuid" => $uid, "acttype" => 7, "infoid" => 0, "uniacid" => $uniacid, "note" => "余额充值成功，奖励积分"));
		mc_credit_update($uid, "credit2", $fee, array($uid, "余额在线充值：" . $fee));
		pdo_insert("kb_sechouse_actlog", array("actname" => "addcredit2", "addtime" => date("Y-m-d H:i:s", TIMESTAMP), "money" => $fee, "ecuid" => $uid, "acttype" => 2, "infoid" => 0, "uniacid" => $uniacid, "note" => "余额充值成功"));
		$this->_send_notice_account("buy_notice", "【{$uid}】在线充值", "余额充值成功", "金额：{$fee}元", "奖励积分：{$credit}");
		return true;
	}
	public function doPageFeed()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$openid = $_W["openid"];
		$uid = $_W["member"]["uid"];
		$acttype = $_GPC["ftype"];
		$params = array(":uniacid" => $uniacid);
		$id = $_GPC["delid"];
		if (!empty($id)) {
			pdo_delete("kb_sechouse_favorite", array("id" => $id, "uniacid" => $uniacid));
		}
		$sqladd = " and uid='{$uid}' ";
		if (isset($_GPC["isbroker"]) && $_GPC["isbroker"] == 1) {
			$sqladd = " and ftype=3 and  bid in (select id from " . tablename("kb_sechouse_broker") . " where ecuid='{$uid}') ";
		}
		$items = pdo_fetchall("select id, houseid,title,smalltext,ftype,(hits+1) as hits,FROM_UNIXTIME(addtime,'%Y-%m-%d %H:%i:%s') as addtime " . ",FROM_UNIXTIME(last_time,'%Y-%m-%d %H:%i:%s') as lasttime ,acttype, url" . " from " . tablename("kb_sechouse_favorite") . " where   uniacid=:uniacid and  acttype ='{$acttype}' {$sqladd} order by id desc limit 50 ", $params);
		foreach ($items as $key => $val) {
			if ($val["acttype"] == "view") {
				if ($val["houseid"] > 0) {
					if ($val["ftype"] == 1) {
						$url = "/wdl_weihouse/pages/house/index?id=" . $val["houseid"];
					} else {
						if ($val["ftype"] == 11) {
							$url = "/wdl_weihouse/pages/newshop/index?id=" . $val["houseid"];
						}
					}
				}
				$detail = $val["lasttime"] . " 浏览 (" . $val["hits"] . ")";
			} else {
				if ($val["acttype"] == "newshopfav") {
					if ($val["houseid"] > 0) {
						$url = "/wdl_weihouse/pages/newshop/index?id=" . $val["houseid"];
					}
					$detail = $val["addtime"] . " 收藏 新房";
				} else {
					if ($val["acttype"] == "fav") {
						if ($val["houseid"] > 0) {
							$url = "/wdl_weihouse/pages/newshop/index?id=" . $val["houseid"];
						}
						$detail = $val["addtime"] . " 收藏 ";
					} else {
						if ($val["acttype"] == "feed") {
							if ($val["houseid"] > 0) {
								if ($val["ftype"] == 3) {
									$url = "/wdl_weihouse/pages/house/index?id=" . $val["houseid"];
								} else {
									if ($val["ftype"] == 8) {
										$url = "/wdl_weihouse/pages/newshop/index?id=" . $val["houseid"];
									}
								}
							}
							$detail = $val["addtime"] . " 预约 ";
						} else {
							unset($items[$key]);
						}
					}
				}
			}
			if (empty($url)) {
				$url = "/wdl_weihouse/pages/index/welcome";
			}
			$items[$key]["url"] = $url;
			$items[$key]["detail"] = $detail;
		}
		$this->result(0, "查询成功", $items);
	}
	function doPageAddfavorite()
	{
		global $_W, $_GPC;
		$uniacid = $this->_uniacid;
		$openid = $_W["openid"];
		$uid = $_W["member"]["uid"];
		$ftype = intval($_GPC["ftype"]);
		$houseid = intval($_GPC["id"]);
		$fav = pdo_fetch("select id from " . tablename("kb_sechouse_favorite") . " where houseid='{$houseid}' and  ftype ='{$ftype}' and  uniacid='{$uniacid}' and uid='{$uid}'");
		if (!empty($fav)) {
			$this->_send_notice_account("favorite_notice", "重复收藏房源：", $_GPC["title"], $_GPC["smalltext"], "用户ID" . $uid);
			$this->result(0, "已经收藏过");
			exit;
		}
		$savedata = array("uid" => $uid, "openid" => $openid, "uniacid" => $uniacid, "addtime" => TIMESTAMP, "houseid" => $houseid, "ftype" => $ftype, "url" => $_GPC["url"], "acttype" => $_GPC["acttype"], "title" => $_GPC["title"], "smalltext" => $_GPC["smalltext"]);
		pdo_insert("kb_sechouse_favorite", $savedata);
		$id = pdo_insertid();
		pdo_update("kb_sechouse", array("fav_num +=" => 1), array("id" => $houseid));
		$this->_send_notice_account("favorite_notice", "收藏房源：", $_GPC["title"], $_GPC["smalltext"], "用户ID" . $uid);
		$this->result(0, "收藏成功");
	}
	function doPageAddfeed()
	{
		global $_W, $_GPC;
		$uniacid = $this->_uniacid;
		$openid = $_W["openid"];
		$houseid = intval($_GPC["id"]);
		$uid = $_W["member"]["uid"];
		$ftype = 3;
		if (!empty($_GPC["ftype"])) {
			$ftype = intval($_GPC["ftype"]);
		}
		$savedata = array("uid" => $uid, "openid" => $openid, "uniacid" => $uniacid, "ftype" => $ftype, "addtime" => TIMESTAMP, "houseid" => intval($_GPC["id"]), "title" => $_GPC["title"], "url" => $_GPC["url"], "acttype" => $_GPC["acttype"], "smalltext" => $_GPC["smalltext"]);
		if ($ftype == 3) {
			$bid = pdo_fetchcolumn("select broker_id from " . tablename("kb_sechouse") . " where id='{$_GPC["id"]}'");
			if ($bid > 0) {
				$savedata["bid"] = $bid;
			}
		}
		pdo_insert("kb_sechouse_favorite", $savedata);
		$id = pdo_insertid();
		$data = array("keyword2" => array("value" => "你的预约提交成功"), "keyword1" => array("value" => "房源：" . $_GPC["title"] . "|" . $savedata["smalltext"]));
		$ret = $this->_send_template_notice($openid, "wdl_weihouse/pages/index/user", $_GPC["form_id"], $data);
		$this->_send_notice_account("feed_notice", "【{$uid}】" . "有新客户预约", $_GPC["title"], $savedata["smalltext"]);
		$house_id = intval($_GPC["id"]);
		$house = pdo_fetch("select broker_id from " . tablename("kb_sechouse") . " where id='{$house_id}'");
		$broker = pdo_fetch("select  openid from " . tablename("kb_sechouse_broker") . "  where  id='" . $house["broker_id"] . "'");
		if ($broker["openid"]) {
			$data = array("keyword2" => array("value" => "有新客户预约"), "keyword1" => array("value" => "房源：" . $_GPC["title"] . "|" . $savedata["smalltext"]));
		}
		$this->result(0, "预约成功");
	}
	public function _getSetting()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$sql = "SELECT `settings` FROM " . tablename("uni_account_modules") . " WHERE `uniacid` = :uniacid AND `module` = :module";
		$settings = pdo_fetchcolumn($sql, array(":uniacid" => $uniacid, ":module" => "wdl_weihouse"));
		$ret = iunserializer($settings);
		return $ret;
	}
	function doPageSettingData()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$ret = $this->_getSetting();
		$array = array();
		if (isset($_GPC["fkey"]) && !empty($_GPC["fkey"])) {
			foreach ($_GPC["fkey"] as $fkey => $fval) {
				if ($fval == "logo" || $fval == "banner") {
					$array[$fval] = tomedia($ret[$fval]);
				} else {
					$array[$fval] = $ret[$fval];
				}
			}
		}
		$ret2 = pdo_fetch("select * from " . tablename("kb_config") . " where uniacid='{$uniacid}' and placeid=11 and module='wdl_weihouse'");
		$confmenu = iunserializer($ret2["conf_value"]);
		$array["totalinfo"] = pdo_fetchcolumn("select count(*) from " . tablename("kb_sechouse") . " where uniacid='{$uniacid}'");
		$array["totalinfo"] = intval($confmenu["index_totalinfo_num"]) + intval($array["totalinfo"]);
		$array["visited"] = pdo_fetchcolumn("select sum(onclick) from " . tablename("kb_sechouse") . " where uniacid='{$uniacid}' ");
		$array["visited"] = intval($array["visited"]) + intval($confmenu["index_totalinfo_visit"]);
		$array["settingdata"] = $ret;
		$this->result(0, "模块设置参数settings", $array);
	}
	public function doPageSearchNewhouse()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$search["keyword"] = trim($_GPC["keyword"]);
		$search["area"] = trim($_GPC["area"]);
		$search["minprice"] = trim($_GPC["minprice"]);
		$search["maxprice"] = trim($_GPC["maxprice"]);
		$search["minsufix"] = trim($_GPC["minsufix"]);
		$search["maxsufix"] = trim($_GPC["maxsufix"]);
		$search["room"] = trim($_GPC["room"]);
		$search["isjingping"] = intval($_GPC["isjingping"]);
		$search["onsale"] = trim($_GPC["onsale"]);
		$sort["sort_order"] = intval($_GPC["sort_order"]);
		$search["house_nature"] = trim($_GPC["house_nature"]);
		$search["house_type"] = trim($_GPC["house_type"]);
		$search["opentime"] = trim($_GPC["opentime"]);
		$condition = " uniacid='{$uniacid}' ";
		if (!empty($search["keyword"])) {
			$condition .= " and (`house_title` like '%{$search["keyword"]}%' or `house_old` like '%{$search["keyword"]}%' or `house_region` like '%{$search["keyword"]}%')";
		}
		if (!empty($search["area"])) {
			$condition .= " and house_region='{$search["area"]}'";
		}
		if (!empty($search["minprice"])) {
			$condition .= " and ( average_price >='{$search["minprice"]}')";
		}
		if (!empty($search["maxprice"])) {
			$condition .= " and (average_price<='{$search["maxprice"]}') ";
		}
		if (!empty($search["room"])) {
			$condition .= " and house_mark like '%{$search["room"]}%' ";
		}
		if (!empty($search["onsale"])) {
			$condition .= " and house_sale = '{$search["onsale"]}' ";
		}
		if (!empty($search["house_nature"])) {
			$condition .= " and house_nature like '%{$search["house_nature"]}%' ";
		}
		if (!empty($search["house_type"])) {
			$condition .= " and house_prowedt like '%{$search["house_type"]}%' ";
		}
		if ($search["isjingping"] == 1) {
			$condition .= " and house_tj='{$search["isjingping"]}'";
		}
		if ($search["opentime"] > 0) {
			$condition .= " and house_paymethod='{$search["opentime"]}'";
		}
		$sort_method = array("0" => " id ASC ", "1" => "id DESC", 2 => " average_price DESC ", 3 => " average_price ASC ", 4 => " house_paymethod ASC", 5 => " house_paymethod DESC");
		if (!empty($sort["sort_order"])) {
			$order = $sort_method[$sort["sort_order"]];
		} else {
			$order = " id ASC ";
		}
		$page = max(1, $_GPC["page"]);
		$pagesize = 10;
		$startlimit = ($page - 1) * $pagesize;
		$fileds = $this->_house_list_field();
		$sql = "SELECT {$fileds}  FROM " . tablename("kb_house_info") . " where {$condition} order by {$order} limit {$startlimit}, {$pagesize}";
		$items = pdo_fetchall($sql);
		if (!empty($items)) {
			foreach ($items as $key => $val) {
				$items[$key] = $this->_format_newhouse_item($val);
			}
			$this->result(0, "查询成功  sql=[{$sql}]", $items);
		} else {
			$this->result(0, "没有数据 sql=[{$sql}]", $items);
		}
	}
	public function doPageNewhouseByfield()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uid = $_W["member"]["uid"];
		$openid = $_W["openid"];
		$limit = 20;
		if (isset($_GPC["limit"]) && !empty($_GPC["limit"])) {
			$limit = $_GPC["limit"];
		}
		if (isset($_GPC["limit"]) && !empty($_GPC["limit"])) {
			$limit = intval($_GPC["limit"]);
		}
		$where = " uniacid='{$uniacid}'  and is_on=0 ";
		if (isset($_GPC["fkey"]) && !empty($_GPC["fkey"])) {
			foreach ($_GPC["fkey"] as $fkey => $fval) {
				$where .= " and {$fkey} = '" . $fval . "' ";
			}
		}
		$fileds = $this->_house_list_field();
		$sql = "SELECT {$fileds} FROM " . tablename("kb_house_info") . " where  {$where}  order by fcatid desc,id desc limit {$limit} ";
		$items = pdo_fetchall($sql);
		foreach ($items as $key => $val) {
			$items[$key] = $this->_format_newhouse_item($val);
		}
		$this->result(0, "success", $items);
	}
	public function _house_list_field()
	{
		return "id, newshouse_id as hid,house_title,house_face,average_price,min_price,max_price,tprice,unit1,unit2,\r\n           house_sale,house_logo,house_prowedt,house_nature,house_type,house_region,house_paymethod,house_mark, \r\n           price_info,uniacid,house_selltelephone,house_address,house_main";
	}
	public function _format_map_point($lon, $lat)
	{
		$bd_lon = $lon;
		$bd_lat = $lat;
		$X_PI = M_PI * 3000.0 / 180.0;
		$x = $bd_lon - 0.0065;
		$y = $bd_lat - 0.006;
		$z = sqrt($x * $x + $y * $y) - 2.0E-5 * sin($y * $X_PI);
		$theta = atan2($y, $x) - 3.0E-6 * cos($x * $X_PI);
		$val[0] = $z * cos($theta);
		$val[1] = $z * sin($theta);
		return $val;
	}
	public function _format_newhouse_item($val)
	{
		if (empty($val)) {
			return array();
		}
		$prowedt = explode(",", $val["house_prowedt"]);
		$val["house_prowedt_all"] = $prowedt;
		$val["house_prowedt"] = $prowedt;
		if (count($prowedt) > 3) {
			$val["house_prowedt"] = array($prowedt[0], $prowedt[1], $prowedt[3]);
		}
		$nature = array("住宅" => "1", "别墅" => "2", "商住" => "3", "商铺" => "4", "低商" => "5");
		$val["house_nature"] = explode(",", $val["house_nature"]);
		$val["house_nature_key"] = $nature[$val["house_nature"][0]];
		$val["house_logo"] = tomedia($val["house_logo"]);
		$val["house_face"] = tomedia($val["house_face"]);
		if ($val["house_sale"]) {
			$val["house_sale_tag"] = $this->other["onsale"][$val["house_sale"]];
		} else {
			$val["house_sale_tag"] = "待定";
		}
		$val["house_startselldate"] = $val["house_startselldate"] ? $val["house_startselldate"] : "暂无开盘信息";
		if ($val["tprice"]) {
			$val["tprice"] = $this->other["pricetype"][$val["tprice"]];
		} else {
			$val["tprice"] = "普通住宅";
		}
		if ($val["unit2"] == "平米") {
			$val["unit2"] = "㎡";
		}
		if ($val["average_price"]) {
			$tag = "均价";
			$price = $val["average_price"] . " " . $val["unit1"] . "/" . $val["unit2"];
		} else {
			if ($val["min_price"]) {
				$tag = "起价";
				$price = $val["min_price"] . " " . $val["unit1"] . "/" . $val["unit2"];
			} else {
				if ($val["max_price"]) {
					$tag = "最高";
					$price = " " . $val["max_price"] . " " . $val["unit1"] . "/" . $val["unit2"];
				} else {
					$tag = "价格";
					$price = "待定";
				}
			}
		}
		$val["price"] = $price;
		$val["price_label"] = $tag;
		return $val;
	}
	public function _format_saleinfo($val)
	{
		$val["addtime"] = date("Y年m月d日", strtotime($val["addtime"]));
		if ($val["tags"]) {
			$val["tags_label"] = $this->other["saletype"][$val["tags"]];
		} else {
			$val["tags"] = 1;
			$val["tags_label"] = "销控信息";
		}
		return $val;
	}
	public function doPageNewshop()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$openid = $_W["openid"];
		$newhouse_id = $_GPC["id"];
		$uid = $_W["member"]["uid"];
		$info = pdo_fetch("select * from " . tablename("kb_house_info") . " where newshouse_id='{$newhouse_id}' and uniacid='{$uniacid}'");
		if (!empty($info)) {
			$shop = pdo_fetch("select * from " . tablename("kb_house") . " where id='{$newhouse_id}'");
		}
		$data["house"] = $this->_format_newhouse_item($info);
		$points = $this->_format_map_point($shop["map_x"], $shop["map_y"]);
		$data["shop"] = $shop;
		$data["shop"]["map_x"] = $points[0];
		$data["shop"]["map_y"] = $points[1];
		$res = pdo_fetchall("select * from " . tablename("kb_house_saleinfo") . " where newhouse_id='{$newhouse_id}' order by addtime desc limit 2");
		foreach ($res as $key => $val) {
			$res[$key] = $this->_format_saleinfo($val);
		}
		$data["salelist"] = $res;
		$data["house"]["saleinfo_total"] = pdo_fetchcolumn("select count(*) from " . tablename("kb_house_saleinfo") . " where  newhouse_id='{$newhouse_id}'");
		pdo_update("kb_house", array("view +=" => "1"), array("id" => $newhouse_id));
		$houseid = intval($newhouse_id);
		$fav = pdo_fetch("select id from " . tablename("kb_sechouse_favorite") . " where ftype=11 and houseid='{$houseid}' and uniacid='{$uniacid}' and uid='{$uid}'");
		if (!empty($fav)) {
			pdo_update("kb_sechouse_favorite", array("hits +=" => 1, "last_time" => TIMESTAMP), array("id" => $fav["id"]));
		} else {
			$savedata = array("uid" => $uid, "openid" => $openid, "uniacid" => $uniacid, "addtime" => TIMESTAMP, "houseid" => $houseid, "ftype" => 11, "acttype" => "view", "last_time" => TIMESTAMP, "title" => $data["house"]["house_title"], "smalltext" => "{$info["house_type"]} | 新房 ");
			pdo_insert("kb_sechouse_favorite", $savedata);
		}
		$data["housepic"] = array();
		$ret = pdo_fetchall("select id,img_url,name from " . tablename("kb_house_attach") . " where uniacid='{$uniacid}' and newhouse_id='{$houseid}' and (sid=4 or sid=10) order by id desc limit 10");
		if (!empty($ret)) {
			foreach ($ret as $key => $val) {
				$val["img_url"] = tomedia($val["img_url"]);
				$ret[$key] = $val;
			}
			$data["housepic"] = $ret;
		}
		$data["ybf"] = array();
		$ret = pdo_fetchall("select id,img_url,name from " . tablename("kb_house_attach") . " where uniacid='{$uniacid}' and newhouse_id='{$houseid}' and (sid=7) order by id desc limit 10");
		if (!empty($ret)) {
			foreach ($ret as $key => $val) {
				$val["name"] = $val["name"] ? $val["name"] : "户型图";
				$val["img_url"] = tomedia($val["img_url"]);
				$ret[$key] = $val;
			}
			$data["ybf"] = $ret;
		}
		$this->result(0, "success", $data);
	}
	public function doPageRealnewhouse()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$newhouse_id = $_GPC["newhouse_id"];
		$limit = 4;
		if (isset($_GPC["limit"]) && !empty($_GPC["limit"])) {
			$limit = intval($_GPC["limit"]);
		}
		$fileds = $this->_house_list_field();
		$real = pdo_fetchall("select {$fileds} from " . tablename("kb_house_info") . " where newshouse_id<>'{$newhouse_id}' and uniacid='{$uniacid}' limit 4");
		foreach ($real as $key => $val) {
			$real[$key] = $this->_format_newhouse_item($val);
		}
		$this->result(0, "success", $real);
	}
	public function doPageNewSale()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$newhouse_id = $_GPC["newhouse_id"];
		$keyword = $_GPC["keyword"];
		$tags = 0;
		if ($_GPC["tags"]) {
			$tags = $_GPC["tags"];
		}
		$limit = 10;
		if (isset($_GPC["limit"]) && !empty($_GPC["limit"])) {
			$limit = intval($_GPC["limit"]);
		}
		$sql_add = " s.uniacid='{$uniacid}' and s.tid=0 ";
		if ($newhouse_id > 0) {
			$sql_add .= " and s.newhouse_id='{$newhouse_id}' ";
		}
		if ($tags) {
			$sql_add .= " and s.tags='{$tags}' ";
		}
		if ($keyword) {
			$sql_add .= " and ( s.title like '%{$keyword}%' or h.house_title like '%{$keyword}%')";
		}
		$page = max(1, $_GPC["page"]);
		$pagesize = $limit;
		$startlimit = ($page - 1) * $pagesize;
		$ret = pdo_fetchall("select s.*,h.house_title,h.house_face,h.house_logo from " . tablename("kb_house_saleinfo") . " s left join " . tablename("kb_house_info") . " h on s.newhouse_id= h.newshouse_id  where  {$sql_add} order by s.addtime desc limit {$startlimit},{$pagesize} ");
		if (!empty($ret)) {
			foreach ($ret as $key => $val) {
				$ret[$key]["category"] = $this->other["saletype"][$val["tags"]];
				$ret[$key]["showtime"] = date("Y年m月d日", strtotime($val["addtime"]));
				$ret[$key]["thumb"] = tomedia($val["house_logo"]);
				$ret[$key]["thumb2"] = tomedia($val["house_face"]);
			}
		}
		$data["salelist"] = $ret;
		$category = $this->other["saletype"];
		foreach ($category as $k => $cat) {
			$tmp[$k]["name"] = $cat;
			$tmp[$k]["tags"] = $k;
			$tmp[$k]["selected"] = $k == $tags ? 1 : 0;
			$tmp[$k]["total"] = 0;
		}
		$tmp[0] = array("name" => "全部", "tags" => 0, "total" => 0, "selected" => $tags == 0 ? 1 : 0);
		$data["house_name"] = pdo_fetchcolumn("select house_title from " . tablename("kb_house_info") . " where newshouse_id='{$newhouse_id}'");
		$data["category"] = $tmp;
		$data["total"] = 0;
		$this->result(0, "success", $data);
	}
	public function doPageSecshop()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$shopid = $_GPC["shopid"];
		$keyword = '';
		if ($_GPC["keyword"]) {
			$keyword = $_GPC["keyword"];
		}
		$limit = 10;
		if (isset($_GPC["limit"]) && !empty($_GPC["limit"])) {
			$limit = intval($_GPC["limit"]);
		}
		$sql_add = " disabled=0 and uniacid='{$uniacid}' ";
		if ($keyword) {
			$sql_add .= " and ( shopname like '%{$keyword}%'  or address like '%{$keyword}%')";
		}
		$page = max(1, $_GPC["page"]);
		$pagesize = $limit;
		$startlimit = ($page - 1) * $pagesize;
		$ret = pdo_fetchall("select * from " . tablename("kb_sechouse_shop") . "  where {$sql_add} order by secnum desc,id desc limit {$startlimit},{$pagesize} ");
		if (!empty($ret)) {
			foreach ($ret as $key => $val) {
				$ret[$key]["thumb"] = tomedia($val["thumb"]);
			}
		}
		$data["result"] = $ret;
		$data["total"] = pdo_fetchcolumn("select count(*) from " . tablename("kb_sechouse_shop") . " where  {$sql_add}  ");
		if ($data["total"] < $page * $limit) {
			$data["islastpage"] = 1;
		} else {
			$data["islastpage"] = 0;
		}
		$this->result(0, "success", $data);
	}
	public function doPageSecshopdetail()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$shopid = $_GPC["shopid"];
		$sql_add = " id='{$shopid}' uniacid='{$uniacid}' ";
		$info = pdo_fetch("select * from " . tablename("kb_sechouse_shop") . " where id='{$shopid}' and uniacid='{$uniacid}'");
		if (!empty($info)) {
			$info["thumb"] = tomedia($info["thumb"]);
		}
		$this->result(0, "success", array("result" => $info));
	}
	public function doPageMapparse()
	{
		global $_GPC, $_W;
		$address = $_GPC["addr"];
		$cf = $this->_getSetting();
		$address = $cf["cityname"] . $address;
		$url = "http://api.map.baidu.com/geocoder/v2/?output=json&ak=4ec0d36f65cf2bc011d7b21fd45a244c&address=" . $address;
		$json = file_get_contents($url);
		$ret = json_decode($json, true);
		if ($ret["result"]["location"]) {
			$points = $this->_format_map_point($ret["result"]["location"]["lng"], $ret["result"]["location"]["lat"]);
		}
		$this->result(0, "success", array("result" => $points));
	}
	public function _address_to_point($address)
	{
		global $_GPC, $_W;
		$url = "http://api.map.baidu.com/geocoder/v2/?output=json&ak=4ec0d36f65cf2bc011d7b21fd45a244c&address=" . $address;
		$json = file_get_contents($url);
		$ret = json_decode($json, true);
		if ($ret["result"]["location"]) {
			$points = $ret["result"]["location"]["lng"] . "," . $ret["result"]["location"]["lat"];
		}
		return $points;
	}
	public function doPageBrokerlist()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$shopid = $_GPC["shopid"];
		$keyword = '';
		if ($_GPC["keyword"]) {
			$keyword = $_GPC["keyword"];
		}
		$limit = 10;
		if (isset($_GPC["limit"]) && !empty($_GPC["limit"])) {
			$limit = intval($_GPC["limit"]);
		}
		$sql_add = " disabled=0 and  uniacid='{$uniacid}' and groupid>0 ";
		if ($shopid > 0) {
			$sql_add .= " and shopid='{$shopid}' ";
		}
		if ($keyword) {
			$sql_add .= " and ( nickname like '%{$keyword}%'  or company like '%{$keyword}%')";
		}
		$page = max(1, $_GPC["page"]);
		$pagesize = $limit;
		$startlimit = ($page - 1) * $pagesize;
		$ret = pdo_fetchall("select * from " . tablename("kb_sechouse_broker") . "  where {$sql_add} order by secnum desc,id desc limit {$startlimit},{$pagesize} ");
		if (!empty($ret)) {
			foreach ($ret as $key => $val) {
				if (!strpos($val["avatar"], "/")) {
					$val["avatar"] = "/addons/wdl_weihouse/style/images/get_avatar.png";
				}
				$ret[$key]["secnum"] = $val["secnum"] ? $val["secnum"] : 0;
				$ret[$key]["ernum"] = $val["ernum"] ? $val["ernum"] : 0;
				$ret[$key]["avatar"] = tomedia($val["avatar"]);
			}
		}
		$data["result"] = $ret;
		$data["total"] = pdo_fetchcolumn("select count(*) from " . tablename("kb_sechouse_broker") . " where  {$sql_add}  ");
		if ($data["total"] < $page * $limit) {
			$data["islastpage"] = 1;
		} else {
			$data["islastpage"] = 0;
		}
		$this->result(0, "success", $data);
	}
	public function doPageSalelist()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$newhouse_id = $_GPC["newhouse_id"];
		$tags = 0;
		if ($_GPC["tags"]) {
			$tags = $_GPC["tags"];
		}
		$limit = 4;
		if (isset($_GPC["limit"]) && !empty($_GPC["limit"])) {
			$limit = intval($_GPC["limit"]);
		}
		$sql_add = " uniacid='{$uniacid}' ";
		if ($newhouse_id > 0) {
			$sql_add .= " and newhouse_id='{$newhouse_id}' ";
		}
		if ($tags) {
			$sql_add .= " and tags='{$tags}' ";
		}
		$page = max(1, $_GPC["page"]);
		$pagesize = $limit;
		$startlimit = ($page - 1) * $pagesize;
		$ret = pdo_fetchall("select * from " . tablename("kb_house_saleinfo") . "  where {$sql_add} order by addtime desc limit {$startlimit},{$pagesize} ");
		if (!empty($ret)) {
			foreach ($ret as $key => $val) {
				$ret[$key]["category"] = $this->other["saletype"][$val["tags"]];
				$ret[$key]["showtime"] = date("Y年m月d日", strtotime($val["addtime"]));
			}
		}
		$data["salelist"] = $ret;
		$category = $this->other["saletype"];
		$data["total"] = pdo_fetchcolumn("select count(*) from " . tablename("kb_house_saleinfo") . " where newhouse_id='{$newhouse_id}' and uniacid='{$uniacid}' ");
		if ($data["total"] < $page * $limit) {
			$data["islastpage"] = 1;
		} else {
			$data["islastpage"] = 0;
		}
		foreach ($category as $k => $cat) {
			$tmp[$k]["name"] = $cat;
			$tmp[$k]["tags"] = $k;
			$tmp[$k]["selected"] = $k == $tags ? 1 : 0;
			$tmp[$k]["total"] = pdo_fetchcolumn("select count(*) from " . tablename("kb_house_saleinfo") . " where newhouse_id='{$newhouse_id}' and uniacid='{$uniacid}' and tags='{$k}'");
		}
		$tmp[0] = array("name" => "全部动态", "tags" => 0, "total" => $data["total"], "selected" => $tags == 0 ? 1 : 0);
		$data["house_name"] = pdo_fetchcolumn("select house_title from " . tablename("kb_house_info") . " where newshouse_id='{$newhouse_id}'");
		$data["category"] = $tmp;
		$this->result(0, "success", $data);
	}
	public function doPageHouseroom()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$newhouse_id = $_GPC["newhouse_id"];
		$roomid = 0;
		if ($_GPC["roomid"]) {
			$roomid = intval($_GPC["roomid"]);
		}
		$album_id = 0;
		if (!empty($_GPC["album_id"])) {
			$album_id = intval($_GPC["album_id"]);
		}
		$limit = 4;
		if (isset($_GPC["limit"]) && !empty($_GPC["limit"])) {
			$limit = intval($_GPC["limit"]);
		}
		$page = max(1, $_GPC["page"]);
		$pagesize = $limit;
		$startlimit = ($page - 1) * $pagesize;
		$limit_sql = " limit {$startlimit},{$pagesize}";
		$sql_add = " newhouse_id='{$newhouse_id}' and uniacid='{$uniacid}' ";
		if ($roomid > 0) {
			$sql_add .= " and id='{$roomid}' ";
			$limit_sql = '';
		}
		if ($album_id > 0) {
			$sql_add .= " and album_id='{$album_id}' ";
		}
		$sql = "select * from " . tablename("kb_house_room") . " where {$sql_add} order by id desc {$limit_sql} ";
		$ret = pdo_fetchall($sql);
		if (!empty($ret)) {
			foreach ($ret as $key => $val) {
				$ret[$key]["thumb"] = tomedia($val["thumb"]);
				$ret[$key]["tags"] = explode("#", $val["btags"]);
				if (count($ret[$key]["tags"]) > 3) {
					$ret[$key]["tags_simple"][0] = $ret[$key]["tags"][0];
					$ret[$key]["tags_simple"][1] = $ret[$key]["tags"][1];
					$ret[$key]["tags_simple"][2] = $ret[$key]["tags"][2];
				} else {
					$ret[$key]["tags_simple"] = $ret[$key]["tags"];
				}
			}
		}
		$data["roomlist"] = $ret;
		if ($roomid > 0) {
			$data["roominfo"] = $ret[0];
			$data["yangban"] = $data["thumbs"] = array();
			$attach = pdo_fetchall("select img_url  from " . tablename("kb_house_attach") . " where sid=3 and uniacid=:uniacid and room=:id", array(":uniacid" => $uniacid, ":id" => $roomid));
			foreach ($attach as $key => $v) {
				$data["thumbs"][] = toimage($v["img_url"]);
			}
			$attach = pdo_fetchall("select img_url  from " . tablename("kb_house_attach") . " where sid=7 and uniacid=:uniacid and room=:id", array(":uniacid" => $uniacid, ":id" => $roomid));
			foreach ($attach as $key => $v) {
				$data["yangban"][] = toimage($v["img_url"]);
			}
		}
		$data["total"] = pdo_fetchcolumn("select count(*) from " . tablename("kb_house_room") . " where {$sql_add} ");
		if ($data["total"] < $page * $limit) {
			$data["islastpage"] = 1;
		} else {
			$data["islastpage"] = 0;
		}
		$category[] = array("name" => "全部户型", "tags" => 0, "total" => $data["total"], "selected" => $album_id == 0 ? 1 : 0);
		$res = pdo_fetchall("select * from " . tablename("kb_house_album") . " where  newshouse_id='{$newhouse_id}' and uniacid='{$uniacid}' ");
		foreach ($res as $key => $r) {
			$k = $r["id"];
			$tmp["name"] = $r["album_cat_name"];
			$tmp["tags"] = $r["id"];
			$tmp["selected"] = $k == $album_id ? 1 : 0;
			$tmp["total"] = pdo_fetchcolumn("select count(*) from " . tablename("kb_house_room") . " where newhouse_id='{$newhouse_id}' and uniacid='{$uniacid}' and album_id='{$k}'");
			$category[] = $tmp;
		}
		$ret = pdo_fetch("select house_title,house_selltelephone from " . tablename("kb_house_info") . " where newshouse_id='{$newhouse_id}'");
		$data["house_name"] = $ret["house_title"];
		$data["house_selltelephone"] = $ret["house_selltelephone"];
		$data["category"] = $category;
		$this->result(0, "success[sql={$sql}]", $data);
	}
	public function doPageActlog()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uid = $_W["member"]["uid"];
		$openid = $_W["openid"];
		$limit = 20;
		if (isset($_GPC["limit"]) && !empty($_GPC["limit"])) {
			$limit = intval($_GPC["limit"]);
		}
		$page = max(1, $_GPC["page"]);
		$pagesize = $limit;
		$startlimit = ($page - 1) * $pagesize;
		$limit_sql = " limit {$startlimit},{$pagesize}";
		if (!empty($_GPC["sql"])) {
			$sql = " and " . $_GPC["sql"];
		}
		$sql_add = " ecuid='{$uid}' and uniacid='{$uniacid}'  {$sql} ";
		$data["total"] = pdo_fetchcolumn("select count(*) from " . tablename("kb_sechouse_actlog") . " where {$sql_add} ");
		$data["result"] = pdo_fetchall("select * from " . tablename("kb_sechouse_actlog") . " where    {$sql_add} order by id desc {$limit_sql}");
		foreach ($data["result"] as $key => $val) {
			$val["money"] = $val["money"] * 100 / 100;
			$val["addtime"] = date("m/d H:i", strtotime($val["addtime"]));
			$data["result"][$key] = $val;
		}
		$data["prive"] = $this->_user_prive();
		$this->result(0, "success[sql={$sql}]", $data);
	}
	public function doPageBrokeragent()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uid = $_W["member"]["uid"];
		$openid = $_W["openid"];
		if ($_GPC["enews"] == "getinfo") {
			$data["result"] = pdo_fetch("select * from " . tablename("kb_sechouse_broker") . " where uniacid='{$uniacid}' and openid='{$openid}'");
			$data["result"]["thumb"] = tomedia($data["result"]["avatar"]);
			$condition = " WHERE `uniacid` = :uniacid AND `uid` = :uid";
			$params = array(":uniacid" => $_W["uniacid"], ":uid" => $uid);
			$sql = "SELECT COUNT(*) FROM " . tablename("kb_sechouse_favorite") . $condition;
			$data["result"]["fav"] = pdo_fetchcolumn($sql . " AND (acttype='fav' or acttype='newshopfav')", $params);
			$data["result"]["view"] = pdo_fetchcolumn($sql . " AND acttype='view' ", $params);
			$data["result"]["feed"] = pdo_fetchcolumn($sql . " AND acttype='feed' ", $params);
			$data["result"]["member"] = $_W["member"];
			$data["result"]["prive"] = $this->_user_prive();
			$this->result(0, "经纪人资料", $data);
		}
		$field = array("nickname", "company", "mobile", "vtags", "avatar", "desc", "groupid");
		$save = array();
		foreach ($field as $key => $v) {
			$save[$v] = $_GPC[$v];
		}
		if (!empty($save)) {
			$id = intval($_GPC["id"]);
			$save["openid"] = $openid;
			$save["uniacid"] = $uniacid;
			$broker = pdo_fetch("select * from " . tablename("kb_sechouse_broker") . " where uniacid='{$uniacid}' and openid='{$openid}' order by id desc");
			if (!empty($broker)) {
				$id = $broker["id"];
			}
			$fans = mc_fetch($_W["member"]["uid"], array("avatar", "nickname"));
			$save["ecuid"] = $uid;
			if (!empty($_GPC["avatar"])) {
				$save["avatar"] = $_GPC["avatar"];
			} else {
				$save["avatar"] = $fans["avatar"];
			}
			if ($id) {
				pdo_update("kb_sechouse_broker", $save, array("id" => $id));
			} else {
				pdo_insert("kb_sechouse_broker", $save);
				if ($save["groupid"] > 0) {
					$this->_send_notice_account("broker_notice", "【{$uid}】开通经纪人", "姓名：" . $save["nickname"], "手机号：" . $save["mobile"] . "公司：" . $save["company"]);
				} else {
					$this->_send_notice_account("register_notice", "【{$uid}】个人绑定手机号", "姓名：" . $save["nickname"], "手机号：" . $save["mobile"]);
				}
			}
			$this->result(0, $id, $save);
		}
	}
	public function doPageTransfile()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uptypes = array("image/jpg", "image/jpeg", "image/png", "image/pjpeg", "image/gif", "image/bmp", "image/x-png");
		$max_file_size = 20000000;
		$save_folder = "images/" . $uniacid . "/" . date("Ym") . "/";
		$destination_folder = "../attachment/" . $save_folder;
		if (!is_uploaded_file($_FILES["upfile"]["tmp_name"])) {
			echo "图片不存在!";
			exit;
		}
		$file = $_FILES["upfile"];
		if ($max_file_size < $file["size"]) {
			echo "文件太大!";
			exit;
		}
		if (!in_array($file["type"], $uptypes)) {
			echo "文件类型不符!" . $file["type"];
			exit;
		}
		if (!file_exists($destination_folder)) {
			mkdir($destination_folder);
		}
		$filename = $file["tmp_name"];
		$pinfo = pathinfo($file["name"]);
		$ftype = $pinfo["extension"];
		$destination = $destination_folder . str_shuffle(time() . rand(111111, 999999)) . "." . $ftype;
		if (file_exists($destination) && $overwrite != true) {
			echo "同名文件已经存在了";
			exit;
		}
		if (!move_uploaded_file($filename, $destination)) {
			echo "移动文件出错";
			exit;
		}
		$pinfo = pathinfo($destination);
		$fname = $pinfo["basename"];
		echo $save_folder . $fname . "#" . tomedia($save_folder . $fname);
		@(require_once IA_ROOT . "/framework/function/file.func.php");
		@($filename = $fname);
		@file_remote_upload($filename);
	}
	public function doPageTransVideo()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uptypes = array("image/jpg", "image/jpeg", "image/png", "image/pjpeg", "image/gif", "image/bmp", "image/x-png");
		$max_file_size = 20000000;
		$save_folder = "images/" . $uniacid . "/" . date("Ym") . "/";
		$destination_folder = "../attachment/" . $save_folder;
		if (!is_uploaded_file($_FILES["upfile"]["tmp_name"])) {
			echo "图片不存在!";
			exit;
		}
		$file = $_FILES["upfile"];
		if (!file_exists($destination_folder)) {
			mkdir($destination_folder);
		}
		$filename = $file["tmp_name"];
		$pinfo = pathinfo($file["name"]);
		$ftype = $pinfo["extension"];
		$destination = $destination_folder . str_shuffle(time() . rand(111111, 999999)) . "." . $ftype;
		if (file_exists($destination) && $overwrite != true) {
			echo "同名文件已经存在了";
			exit;
		}
		if (!move_uploaded_file($filename, $destination)) {
			echo "移动文件出错";
			exit;
		}
		$pinfo = pathinfo($destination);
		$fname = $pinfo["basename"];
		echo $save_folder . $fname . "#" . tomedia($save_folder . $fname);
		@(require_once IA_ROOT . "/framework/function/file.func.php");
		@($filename = $fname);
		@file_remote_upload($filename);
	}
	public function doPageToken()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$uid = $_W["member"]["uid"];
		$openid = $_W["openid"];
		$api_url = "http://w7.siyueweb.cn/app/index.php?i=7&c=entry&do=weihousetoken&m=wdl_token";
		$ret = file_get_contents($api_url);
		$msg = trim($ret);
		$this->result(0, $msg);
	}
	public function doPageBrokercheck()
	{
		global $_GPC, $_W;
		$uniacid = $this->_uniacid;
		$openid = $_W["openid"];
		$uid = $_W["member"]["uid"];
		$login_uid = $_GPC["login_uid"];
		$ret = pdo_fetch("select * from " . tablename("kb_sechouse_broker") . " where uniacid='{$uniacid}' and openid='{$openid}'");
		$this->result(0, "经纪人资料", $ret);
	}
}