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
$adapter = (include IA_ROOT . "/addons/wdl_weihouse/vender/adapter.php");
$other = $adapter["newhouse"];
if (isset($_GPC["newhouseid"]) && !empty($_GPC["newhouseid"])) {
	$dropmenu[] = array("title" => "预览楼盘", "op" => "viewbase", "hid" => $_GPC["newhouseid"]);
	$dropmenu[] = array("title" => "基本资料编辑", "op" => "baseinfo", "hid" => $_GPC["newhouseid"]);
	$dropmenu[] = array("title" => "价格记录", "op" => "price", "hid" => $_GPC["newhouseid"]);
	$dropmenu[] = array("title" => "销售动态", "op" => "saleinfo", "hid" => $_GPC["newhouseid"]);
	$dropmenu[] = array("title" => "楼盘图片列表", "op" => "housepic", "hid" => $_GPC["newhouseid"]);
	$dropmenu[] = array("title" => "楼盘户型", "op" => "roomlist", "hid" => $_GPC["newhouseid"]);
}
$opersql = '';
if ($_W["user"]["type"] == 3) {
	$opersql = " AND `user_id` =:uid ";
}
if ($operation == "editbaseinfo") {
	if (checksubmit("submit")) {
		$postdata = $_GPC["data"];
		if (!empty($postdata)) {
			$postdata["base"]["uniacid"] = $postdata["newhouse"]["uniacid"] = $uniacid;
			if (empty($postdata["base"]["house_title"])) {
				message("请输入楼盘名称", "referer", "error");
			}
			if (!empty($postdata["house_address"])) {
				$postdata["newhouse"]["shop_address"] = $postdata["house_address"];
				$postdata["base"]["house_address"] = $postdata["house_address"];
			} else {
				$postdata["newhouse"]["shop_address"] = '';
				$postdata["base"]["house_address"] = '';
			}
			if (!empty($postdata["mapinfo"])) {
				$mapinfo = explode(",", $postdata["mapinfo"]);
				$postdata["newhouse"]["map_x"] = $mapinfo[0];
				$postdata["newhouse"]["map_y"] = $mapinfo[1];
			}
			if (!empty($postdata["base"]["house_nature"])) {
				$postdata["base"]["house_nature"] = implode(",", $postdata["base"]["house_nature"]);
			}
			if (!empty($postdata["base"]["house_type"])) {
				$postdata["base"]["house_type"] = implode(",", $postdata["base"]["house_type"]);
			}
			if (!empty($postdata["base"]["house_prowedt"])) {
				$postdata["base"]["house_prowedt"] = implode(",", $postdata["base"]["house_prowedt"]);
			}
			if (!empty($postdata["base"]["house_mark"])) {
				$postdata["base"]["house_mark"] = implode(",", $postdata["base"]["house_mark"]);
			}
			$postdata["newhouse"]["shop_name"] = $postdata["base"]["house_title"];
			if (isset($postdata["shopid"]) && !empty($postdata["shopid"])) {
				$shopid = intval($postdata["shopid"]);
				$houseid = intval($postdata["houseid"]);
				$newhouse_id = intval($postdata["newhouse_id"]);
				pdo_update("kb_house", $postdata["newhouse"], array("id" => $shopid));
				pdo_update("kb_house_info", $postdata["base"], array("id" => $houseid));
				message("资料修改成功", $this->createWebUrl("newhouse"));
			} else {
				pdo_insert("kb_house", $postdata["newhouse"]);
				$shopid = pdo_insertid();
				$postdata["base"]["newshouse_id"] = $shopid;
				$postdata["base"]["user_id"] = $_W["uid"];
				pdo_insert("kb_house_info", $postdata["base"]);
			}
			message("添加楼盘成功", $this->createWebUrl("newhouse"));
		} else {
			message("添加失败", "referer", "error");
		}
	}
} else {
	if ($operation == "addbaseinfo") {
		include $this->template("newhouse_baseinfo");
	} else {
		if ($operation == "baseinfo") {
			$newhouseid = intval($_GPC["newhouseid"]);
			$page = intval($_GPC["page"]);
			$params = array(":uniacid" => $uniacid, ":newhouseid" => $newhouseid);
			$shopinfo = pdo_fetch("select * from " . tablename("kb_house") . " where uniacid=:uniacid and id=:newhouseid", $params);
			if (empty($shopinfo)) {
				message("参数错误", $this->createWebUrl("newhouse"), "error");
			}
			$forumscon3 = pdo_fetch("select * from " . tablename("kb_house_info") . " where uniacid=:uniacid and newshouse_id=:newhouseid", $params);
			include $this->template("newhouse_baseinfo");
		} else {
			if ($operation == "viewbase") {
				$newhouseid = intval($_GPC["newhouseid"]);
				$params = array(":uniacid" => $uniacid, ":newhouseid" => $newhouseid);
				$shopinfo = pdo_fetch("select * from " . tablename("kb_house") . " where uniacid=:uniacid and id=:newhouseid", $params);
				if (empty($shopinfo)) {
					message("参数错误", $this->createWebUrl("newhouse"), "error");
				}
				$forumscon3 = pdo_fetch("select * from " . tablename("kb_house_info") . " where uniacid=:uniacid and newshouse_id=:newhouseid", $params);
				include $this->template("newhouse_index");
			} else {
				if ($operation == "baidumap") {
					$point = $_GPC["point"];
					$point = $this->module["config"]["city_point"];
					$default_point = $point ? $point : "110.305277,25.278618";
					include $this->template("baidumap");
				} else {
					if ($operation == "display") {
						$pindex = max(1, intval($_GPC["page"]));
						$psize = 15;
						$condition = " WHERE `uniacid` = :uniacid ";
						$params = array(":uniacid" => $_W["uniacid"]);
						if (!empty($_GPC["keyword"])) {
							$condition .= " AND (`house_title` LIKE :title OR `house_region` LIKE :title OR `house_developer` LIKE :title OR `house_nature` LIKE :title)";
							$params[":title"] = "%" . trim($_GPC["keyword"]) . "%";
						}
						if (isset($_GPC["is_on"]) && $_GPC["is_on"] != -1) {
							$condition .= " AND `is_on` = :is_on";
							$params[":is_on"] = intval($_GPC["is_on"]);
						}
						if (isset($_GPC["house_sale"]) && $_GPC["house_sale"] != -1) {
							$condition .= " AND `is_on` = :house_sale";
							$params[":house_sale"] = intval($_GPC["house_sale"]);
						}
						if ($opersql) {
							$condition .= $opersql;
							$params[":uid"] = $_W["uid"];
						}
						if (isset($_GPC["newhouseid"]) && $_GPC["newhouseid"]) {
							$newhouse_id = intval($_GPC["newhouseid"]);
							pdo_delete("kb_house_info", array("newshouse_id" => $newhouse_id, "uniacid" => $uniacid));
							pdo_delete("kb_house", array("id" => $newhouse_id, "uniacid" => $uniacid));
							pdo_delete("kb_house_album", array("newshouse_id" => $newhouse_id, "uniacid" => $uniacid));
						}
						$sql = "SELECT COUNT(*) FROM " . tablename("kb_house_info") . $condition;
						$total = pdo_fetchcolumn($sql, $params);
						if (!empty($total)) {
							$sql = "SELECT * FROM " . tablename("kb_house_info") . $condition . " ORDER BY   `fcatid`  DESC ,id desc LIMIT " . ($pindex - 1) * $psize . "," . $psize;
							$list = pdo_fetchall($sql, $params);
							$pager = pagination($total, $pindex, $psize);
						}
						include $this->template("newhouse_display");
					} else {
						if ($operation == "modifyname") {
							$id = $_GPC["houseid"];
							$name = $_GPC["name"];
							pdo_update("kb_house_attach", array("name" => $name), array("id" => $id, "uniacid" => $uniacid));
							echo "success";
							exit;
						} else {
							if ($operation == "housepic") {
								$newhouseid = intval($_GPC["newhouseid"]);
								$params = array(":uniacid" => $uniacid, ":newhouseid" => $newhouseid);
								$shopinfo = pdo_fetch("select * from " . tablename("kb_house") . " where uniacid=:uniacid and id=:newhouseid", $params);
								if (empty($shopinfo)) {
									message("参数错误", $this->createWebUrl("newhouse"), "error");
								}
								$forumscon3 = pdo_fetch("select * from " . tablename("kb_house_info") . " where uniacid=:uniacid and newshouse_id=:newhouseid", $params);
								$sid = intval($_GPC["sid"]);
								if (empty($sid)) {
									$sid = 4;
								}
								$hid = $newhouseid;
								$user_id = $newhouseid;
								$sql = "select  *,(select img_url from " . tablename("kb_house_attach") . "m where m.user_album_id=a.id order by m.id desc limit 1 ) as  img_url  from  " . tablename("kb_house_album") . " a  where a.newshouse_id='{$user_id}' and a.mode='{$sid}' ";
								$img_type = pdo_fetchall($sql);
								$pindex = max(1, intval($_GPC["page"]));
								$psize = 15;
								$condition = " where `sid`=:sid and newhouse_id=:newhouseid and uniacid=:uniacid ";
								$params[":sid"] = $sid;
								$albumid = intval($_GPC["albumid"]);
								if (!empty($albumid)) {
									$condition .= " and user_album_id=:albumid ";
									$params[":albumid"] = $albumid;
								}
								$sql = "SELECT COUNT(*) FROM " . tablename("kb_house_attach") . $condition;
								$total = pdo_fetchcolumn($sql, $params);
								if (!empty($total)) {
									$sql = "SELECT * FROM " . tablename("kb_house_attach") . $condition . " ORDER BY   `id` DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize;
									$result["data"] = pdo_fetchall($sql, $params);
									$pager = pagination($total, $pindex, $psize);
								}
								include $this->template("newhouse_pic");
							} else {
								if ($operation == "addpic") {
									$sid = intval($_GPC["sid"]);
									$hid = $newhouseid = intval($_GPC["newhouseid"]);
									$typename = $other["imgoption"][$sid];
									$params = array(":uniacid" => $uniacid, ":newhouseid" => $newhouseid);
									$forumscon3 = pdo_fetch("select * from " . tablename("kb_house_info") . " where uniacid=:uniacid and newshouse_id=:newhouseid", $params);
									if (checksubmit("submit")) {
										$album_cat_name = '';
										$albumid = intval($_GPC["albumid"]);
										if ($albumid > 0) {
											$album = pdo_fetch("select * from " . tablename("kb_house_album") . " where uniacid='{$uniacid}' and id='{$albumid}'");
											$album_cat_name = $album["album_cat_name"];
										}
										if (!empty($_GPC["album_cat_name"])) {
											$album_cat_name = $_GPC["album_cat_name"];
											pdo_insert("kb_house_album", array("newshouse_id" => $hid, "album_cat_name" => $album_cat_name, "mode" => $sid, "parent_id" => $albumid, "uniacid" => $uniacid));
											$albumid = pdo_insertid();
										}
										if (!$albumid) {
											message("请选择图片分类", referer(), "fail");
										}
										if (!empty($_GPC["thumbs"])) {
											foreach ($_GPC["thumbs"] as $key => $thumb) {
												pdo_insert("kb_house_attach", array("name" => '', "newhouse_id" => $newhouseid, "img_url" => $thumb, "img_url_s" => $thumb, "upl_time" => date("Y-m-d H:i:s"), "user_album_id" => $albumid, "sid" => $sid, "uniacid" => $uniacid));
											}
										}
										message("提交图片成功", referer(), "success");
									}
									$params[":sid"] = $sid;
									$category = pdo_fetchall("select * from " . tablename("kb_house_album") . " where  uniacid=:uniacid and newshouse_id=:newhouseid and mode=:sid ", $params);
									include $this->template("newhouse_addpic");
								} else {
									if ($operation == "deletepic") {
										$hid = intval($_GPC["newhouseid"]);
										$imgid = intval($_GPC["imgid"]);
										pdo_delete("kb_house_attach", array("id" => $imgid, "newhouse_id" => $hid, "uniacid" => $uniacid));
										message("删除成功", referer(), "success");
									} else {
										if ($operation == "delpictype") {
											$hid = intval($_GPC["newhouseid"]);
											$tid = intval($_GPC["tid"]);
											pdo_delete("kb_house_album", array("id" => $tid, "newshouse_id" => $hid, "uniacid" => $uniacid));
											message("删除成功", referer(), "success");
										} else {
											if ($operation == "addroom") {
												$id = $_GPC["id"];
												$hid = $newhouseid = intval($_GPC["newhouseid"]);
												$params = array(":uniacid" => $uniacid, ":newhouse_id" => $newhouseid);
												$forumscon3 = pdo_fetch("select * from " . tablename("kb_house_info") . " where uniacid=:uniacid and newshouse_id=:newhouse_id", $params);
												if (checksubmit("submit")) {
													$save = $_GPC["data"];
													$album_cat_name = '';
													$albumid = intval($_GPC["albumid"]);
													if ($albumid > 0) {
														$album = pdo_fetch("select * from " . tablename("kb_house_album") . " where uniacid='{$uniacid}' and id='{$albumid}'");
														$album_cat_name = $album["album_cat_name"];
													}
													if (!empty($_GPC["album_cat_name"])) {
														$album_cat_name = $_GPC["album_cat_name"];
														pdo_insert("kb_house_album", array("newshouse_id" => $hid, "album_cat_name" => $album_cat_name, "mode" => 3, "parent_id" => $albumid, "uniacid" => $uniacid));
														$albumid = pdo_insertid();
													}
													if (!$albumid) {
														message("请选择图片分类", referer(), "fail");
													}
													if (!empty($_GPC["thumb"])) {
														$save["thumb"] = $_GPC["thumb"];
													}
													$save["newhouse_id"] = $hid;
													$save["uniacid"] = $uniacid;
													$save["album_id"] = $albumid;
													$save["album_name"] = $album_cat_name;
													if ($id > 0) {
														pdo_update("kb_house_room", $save, array("id" => $id));
														$room_id = $id;
														pdo_query("delete from " . tablename("kb_house_attach") . " where room='{$room_id}' and (sid=3 or sid=7) and uniacid='{$uniacid}'");
													} else {
														pdo_insert("kb_house_room", $save);
														$room_id = pdo_insertid();
													}
													if (!empty($_GPC["thumbs"])) {
														foreach ($_GPC["thumbs"] as $key => $thumb) {
															pdo_insert("kb_house_attach", array("name" => $save["title"], "newhouse_id" => $newhouseid, "img_url" => $thumb, "img_url_s" => $thumb, "upl_time" => date("Y-m-d H:i:s"), "user_album_id" => $albumid, "sid" => 3, "room" => $room_id, "uniacid" => $uniacid));
														}
													}
													if (!empty($_GPC["yangbantu"])) {
														foreach ($_GPC["yangbantu"] as $key => $thumb) {
															pdo_insert("kb_house_attach", array("name" => $save["title"], "newhouse_id" => $newhouseid, "img_url" => $thumb, "img_url_s" => $thumb, "upl_time" => date("Y-m-d H:i:s"), "user_album_id" => 0, "sid" => 7, "room" => $room_id, "uniacid" => $uniacid));
														}
													}
													message("操作成功", referer(), "success");
												}
												$params[":sid"] = 3;
												$category = pdo_fetchall("select * from " . tablename("kb_house_album") . " where  uniacid=:uniacid and newshouse_id=:newhouse_id and mode=:sid ", $params);
												$info = pdo_fetch("select * from " . tablename("kb_house_room") . " where  uniacid=:uniacid and id=:id", array(":uniacid" => $uniacid, ":id" => $id));
												$attach = pdo_fetchall("select img_url  from " . tablename("kb_house_attach") . " where sid=3 and uniacid=:uniacid and room=:id", array(":uniacid" => $uniacid, ":id" => $id));
												foreach ($attach as $key => $v) {
													$thumbs[] = $v["img_url"];
												}
												$attach = pdo_fetchall("select img_url  from " . tablename("kb_house_attach") . " where sid=7 and uniacid=:uniacid and room=:id", array(":uniacid" => $uniacid, ":id" => $id));
												foreach ($attach as $key => $v) {
													$yangban[] = $v["img_url"];
												}
												include $this->template("newhouse_room_add");
											} else {
												if ($operation == "roomlist") {
													$id = $_GPC["id"];
													$hid = $newhouseid = intval($_GPC["newhouseid"]);
													$params = array(":uniacid" => $uniacid, ":newhouse_id" => $newhouseid);
													$forumscon3 = pdo_fetch("select * from " . tablename("kb_house_info") . " where uniacid=:uniacid and newshouse_id=:newhouse_id", $params);
													if ($id) {
														if ($_GPC["enews"] == "delete") {
															pdo_query("delete from " . tablename("kb_house_room") . " where id='{$id}' and uniacid='{$uniacid}'");
															message("删除成功", referer(), "success");
														}
													}
													$pindex = max(1, intval($_GPC["page"]));
													$psize = 15;
													$condition = " where  newhouse_id=:newhouse_id and uniacid=:uniacid ";
													$sql = "SELECT COUNT(*) FROM " . tablename("kb_house_room") . $condition;
													$total = pdo_fetchcolumn($sql, $params);
													if (!empty($total)) {
														$sql = "SELECT * FROM " . tablename("kb_house_room") . $condition . " ORDER BY   `id` DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize;
														$result["data"] = pdo_fetchall($sql, $params);
														$pager = pagination($total, $pindex, $psize);
													}
													include $this->template("newhouse_room_display");
												} else {
													if ($operation == "addsaleinfo") {
														$id = $_GPC["id"];
														$hid = $newhouseid = intval($_GPC["newhouseid"]);
														$params = array(":uniacid" => $uniacid, ":newhouse_id" => $newhouseid);
														$forumscon3 = pdo_fetch("select * from " . tablename("kb_house_info") . " where uniacid=:uniacid and newshouse_id=:newhouse_id", $params);
														if ($id) {
															$info = pdo_fetch("select * from " . tablename("kb_house_saleinfo") . " where uniacid='{$uniacid}' and id='{$id}'");
														} else {
															$info["addtime"] = date("Y-m-d");
														}
														if (checksubmit("submit")) {
															if (empty($_GPC["data"]["tags"])) {
																message("请选择分类", referer(), "error");
															}
															if (empty($_GPC["data"]["title"])) {
																message("请输入标题", referer(), "error");
															}
															if (!empty($_GPC["data"])) {
																$savedata = $_GPC["data"];
																$savedata["newhouse_id"] = $newhouseid;
																$savedata["uniacid"] = $uniacid;
																if ($id) {
																	pdo_update("kb_house_saleinfo", $savedata, array("id" => $id));
																} else {
																	pdo_insert("kb_house_saleinfo", $savedata);
																}
															}
															message("保存销售动态成功", referer(), "success");
														}
														include $this->template("newhouse_saleinfo_add");
													} else {
														if ($operation == "saleinfo") {
															$id = $_GPC["id"];
															$hid = $newhouseid = intval($_GPC["newhouseid"]);
															$params = array(":uniacid" => $uniacid, ":newhouse_id" => $newhouseid);
															$forumscon3 = pdo_fetch("select * from " . tablename("kb_house_info") . " where uniacid=:uniacid and newshouse_id=:newhouse_id", $params);
															if ($id) {
																if ($_GPC["enews"] == "delete") {
																	pdo_query("delete from " . tablename("kb_house_saleinfo") . " where id='{$id}' and uniacid='{$uniacid}'");
																	message("删除成功", referer(), "success");
																}
															}
															$pindex = max(1, intval($_GPC["page"]));
															$psize = 15;
															$condition = " where  newhouse_id=:newhouse_id and uniacid=:uniacid ";
															$sql = "SELECT COUNT(*) FROM " . tablename("kb_house_saleinfo") . $condition;
															$total = pdo_fetchcolumn($sql, $params);
															if (!empty($total)) {
																$sql = "SELECT * FROM " . tablename("kb_house_saleinfo") . $condition . " ORDER BY   `id` DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize;
																$result["data"] = pdo_fetchall($sql, $params);
																$pager = pagination($total, $pindex, $psize);
															}
															include $this->template("newhouse_saleinfo_display");
														} else {
															if ($operation == "price") {
																$id = $_GPC["id"];
																$hid = $newhouseid = intval($_GPC["newhouseid"]);
																$params = array(":uniacid" => $uniacid, ":newhouse_id" => $newhouseid);
																$forumscon3 = pdo_fetch("select * from " . tablename("kb_house_info") . " where uniacid=:uniacid and newshouse_id=:newhouse_id", $params);
																if ($id) {
																	if ($_GPC["enews"] == "delete") {
																		pdo_query("delete from " . tablename("kb_house_price") . " where id='{$id}' and uniacid='{$uniacid}'");
																		message("删除成功", referer(), "success");
																	}
																	$info = pdo_fetch("select * from " . tablename("kb_house_price") . " where uniacid='{$uniacid}' and id='{$id}'");
																} else {
																	$info["price_datetime"] = date("Y-m-d");
																}
																if (checksubmit("submit")) {
																	if (empty($_GPC["data"]["tprice"])) {
																		message("请输入价格类型", referer(), "error");
																	}
																	if (empty($_GPC["data"]["min_price"]) && empty($_GPC["data"]["average_price"]) && empty($_GPC["data"]["max_price"])) {
																		message("请输入价格", referer(), "error");
																	}
																	if (!empty($_GPC["data"])) {
																		$savedata = $_GPC["data"];
																		$savedata["newhouse_id"] = $newhouseid;
																		$savedata["uniacid"] = $uniacid;
																		if ($id) {
																			pdo_update("kb_house_price", $savedata, array("id" => $id));
																		} else {
																			pdo_insert("kb_house_price", $savedata);
																		}
																		$price = pdo_fetch("select * from " . tablename("kb_house_price") . " where newhouse_id='{$hid}' and uniacid='{$uniacid}' order by price_datetime desc,id desc");
																		pdo_update("kb_house_info", array("average_price" => $price["average_price"], "min_price" => $price["min_price"], "max_price" => $price["max_price"], "price_info" => $price["price_info"], "tprice" => $price["tprice"], "price_info" => $price["price_info"], "unit1" => $price["unit1"], "unit2" => $price["unit2"]), array("newshouse_id" => $hid));
																	}
																	message("保存价格动态成功", referer(), "success");
																}
																$pindex = max(1, intval($_GPC["page"]));
																$psize = 15;
																$condition = " where  newhouse_id=:newhouse_id and uniacid=:uniacid ";
																$sql = "SELECT COUNT(*) FROM " . tablename("kb_house_price") . $condition;
																$total = pdo_fetchcolumn($sql, $params);
																if (!empty($total)) {
																	$sql = "SELECT * FROM " . tablename("kb_house_price") . $condition . " ORDER BY   `id` DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize;
																	$result["data"] = pdo_fetchall($sql, $params);
																	$pager = pagination($total, $pindex, $psize);
																}
																include $this->template("newhouse_price");
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}