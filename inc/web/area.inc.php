<?php
/**
 * 这个是区域和婚姻状况 设置，小区也可以扩展
 */
defined('IN_IA') or exit('Access Denied');
global $_GPC, $_W;
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';	
$selects = $this->_forms;
$sec = $this->module_setting();
$uniacid = $_W['uniacid']; 
$openid = $_W['openid'];

$table_area = 'kb_sechouse_area';
/*类型*/
$type = 2;
if($_GPC['type']){
    $type = $_GPC['type'];
}
/*经纪人列表*/
if($operation =='display'){
     /*删除门店成功*/
     if(!empty($_GPC['id'])){
        $params = array(':uniacid' => $_W['uniacid'],':id'=> intval($_GPC['id']) );
        pdo_query("delete  from  ".tablename($table_area) ."  where uniacid=:uniacid and id=:id", $params);
        message("删除成功");
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;
    $condition = ' WHERE `uniacid` = :uniacid  and `type`=:type ';
    $params = array(':uniacid' => $uniacid,':type'=>$type );
    if (!empty($_GPC['keyword'])) {
            $condition .= ' AND `name` LIKE :name';
            $params[':name'] = '%' . trim($_GPC['keyword']) . '%';
    }
 
    $sql = 'SELECT COUNT(*) FROM ' . tablename($table_area). $condition;
    $total = pdo_fetchcolumn($sql, $params);
    if (!empty($total)) {
            $sql = 'SELECT * FROM ' .tablename($table_area) . $condition . ' ORDER BY  id desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql, $params);
            $pager = pagination($total, $pindex, $psize);
           
    } 
 
}
elseif($operation =='add'){
     $parent = select_area($type);
     
}
elseif($operation =='edit'){
      if(!empty($_GPC['id'])){
         $params = array(':uniacid' => $uniacid,':id'=> intval($_GPC['id']) );
         $item = pdo_fetch("select * from ".tablename($table_area)." where uniacid=:uniacid and id=:id", $params);
     }
     $type = $item['type'];
     if(empty($item)){
          message("参数有误");
     }
     $parent = select_area($type);
     /**
      * 提交保存数据
      */
}elseif($operation=='addpost'){  
      if(checksubmit('submit')){
         $savedata = $_GPC['data'] ;
    
         $savedata['uniacid'] = $uniacid;
         $savedata['type'] = $type;
       
         /*保存数据*/
         if(intval($_GPC['id'])>0){
         /**/
         pdo_update($table_area, 
                 $savedata, 
                 array('uniacid' => $uniacid,'id'=> intval($_GPC['id']) )           
          );
         }else{
             pdo_insert($table_area, $savedata);
         }
         
         message("操作成功",$this->createWebUrl('area' ), 'success');
     }
}
 /**
  * 婚姻状况的分类---
  */ 
function select_area($type=1){
    global $_W,$_GPC;
    $parent_type = $type-1;
    
    $sql = "select * from ". tablename("kb_sechouse_area")." where uniacid='".$_W['uniacid']."' and type='$parent_type'" ; 
    $all = pdo_fetchall($sql );
    $tmp = array();
    foreach($all as $key=>$val){
        $tmp[$val['id']] = $val['name'];
    }
    return $tmp;
}
include $this->template('area');
?>
