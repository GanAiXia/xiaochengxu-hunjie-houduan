<?php
/*
 * 这个是小程序后台动态配置的控制器
 */
defined('IN_IA') or exit('Access Denied');
global $_GPC, $_W;
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';	
$selects = $this->_forms;
$sec = $this->module_setting();
$uniacid = $_W['uniacid']; 
$openid = $_W['openid'];
 
if($operation =='display'){
    
    
}
include $this->template('config_display');
?>
