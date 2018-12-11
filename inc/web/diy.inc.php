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