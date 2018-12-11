<?php

/* 
 * siyueweb.cn 微乐居
 * @author 34010235 经纪人管理
 */
defined('IN_IA') or exit('Access Denied');
global $_GPC, $_W;
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';	
$selects = $this->_forms;
$sec = $this->module_setting();
$uniacid = $_W['uniacid']; 
$openid = $_W['openid'];

$table_borker = 'kb_sechouse_broker';
/*判断操作权限*/
$opersql = '';
if($_W['user']['type']==3){    
    $opersql = " AND `uid` =:uid ";
}
/*经纪人列表*/
if($operation =='display'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;
    
     $condition = ' WHERE `uniacid` = :uniacid   ';
    /*类型*/
    $rt = intval($_GPC['rt']);
    if(empty($rt)){
        $rt = 1;
    }
    if($rt ==1){
        $condition.= "  and groupid = 1";
    }elseif($rt==2){
        $condition.=" and groupid=2 ";
    }elseif($rt==3){
        $condition.=" and groupid=0 ";
    }elseif($rt==4){
        $condition.=" and iscompany=1 ";
    }
   
    $params = array(':uniacid' => $uniacid );
    if (!empty($_GPC['keyword'])) {
            $condition .= ' AND `nickname` LIKE :username';
            $params[':username'] = '%' . trim($_GPC['keyword']) . '%';
    }
    if($opersql){
        $condition .= $opersql;
        $params[':uid'] = $_W['uid'];
    }
    $sql = 'SELECT COUNT(*) FROM ' . tablename($table_borker). $condition;
    $total = pdo_fetchcolumn($sql, $params);
    if (!empty($total)) {
            $sql = 'SELECT * FROM ' .tablename($table_borker) . $condition . ' ORDER BY  id desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql, $params);
            $pager = pagination($total, $pindex, $psize);
           
    } 
 
}
elseif($operation =='editbroker'){
        if(!empty($_GPC['id'])){
         $params = array(':uniacid' => $uniacid,':id'=> intval($_GPC['id']) );
         $item = pdo_fetch("select * from ".tablename($table_borker)." where uniacid=:uniacid and id=:id", $params);
     }
     if(empty($item)){
          message("参数有误");
     }
     $admin = get_shop_permission($uniacid, $modelname); 
     
     if(checksubmit('submit')){
         $username = $_GPC['data']['nickname'];
         $savedata = $_GPC['data'];
         if(!empty($_GPC['thumb'])){
            $savedata['avatar'] = $_GPC['thumb'];
         }
          $savedata['end_time'] =  $_GPC['end_time'];
         
         if($savedata['shopid']>0){
            $r  = pdo_fetch("select uid,shopname from ".tablename("kb_sechouse_shop")." where id='".$savedata['shopid']."'");
            $savedata['uid'] = $r['uid'];
            $savedata['company'] = $r['shopname'];              
         }         
         $desc = $_GPC['data']['desc'];
         /**/
         pdo_update($table_borker, 
                 $savedata,
                /* array(
                     'disabled'=>$_GPC['data']['disabled'],
                     'mobile'=>$_GPC['data']['mobile'],
                     'vtags'=>$_GPC['data']['vtags'],
                     'company'=>$_GPC['data']['company'],
                     'nickname'=> $username, 
                     'avatar'=>$avatar,
                     'desc'=>$desc), */
                 array('uniacid' => $uniacid,'id'=> intval($_GPC['id']) )           
          );
         if(!empty($_GPC['referer'])){
             $return_url = $_GPC['referer'];
         }else{
              $return_url = $this->createWebUrl('broker' );
         }
         message("修改成功", $return_url , 'success');
     }
/* 设置经纪人的属性*/
}elseif($operation=='setproperty'){
    
    $id = intval($_GPC['id']);
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if (in_array($type, array('istop', 'isyou', 'ischeng'))) {
            $data = ($data==1?'0':'1');
            pdo_update("kb_sechouse_broker", array(  $type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            die(json_encode(array("result" => 1, "data" => $data)));
    }
 
    die(json_encode(array("result" => 0)));
}
 /*删除经纪人*/
elseif($operation =='delbroker'){
    if(!empty($_GPC['id'])){
        $params = array(':uniacid' => $_W['uniacid'],':id'=> intval($_GPC['id']) );
        pdo_query("delete  from  ".tablename($table_borker) ."  where uniacid=:uniacid and id=:id", $params);
        
        message("删除经纪人成功");
    }
}
include $this->template('broker');

/**
 * 查找到模块的管理员账号
 */
function get_shop_permission($uniacid){
    global $_W,$GPC; 
    $opersql = '';
    if($_W['user']['type']==3){    
        $opersql = " AND  `uid` ='".$_W['uid']."' ";        
    }
    
    $role = pdo_fetchall("select shopname,id from ".tablename("kb_sechouse_shop").
            "  where uniacid='$uniacid'  $opersql");
    $admin = array();
    if(!empty($role)){
        foreach($role as $key=>$r){
            $admin[$r['id']] = $r['shopname'];
        }
    }
    return $admin;
}