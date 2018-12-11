<?php

/**
 * 这个是看房团活动管理
 */
defined('IN_IA') or exit('Access Denied');
global $_GPC, $_W;
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$selects = $this->_forms;
$sec = $this->module_setting();
$uniacid = $_W['uniacid'];
$openid = $_W['openid'];

/* 添加看房活动 */
if ($operation == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $id => $displayorder) {
            pdo_update('kb_house_saleinfo', array('orderid' => $displayorder), array('id' => $id, 'uniacid' => $_W['uniacid']));
        }
        message('排序更新成功！', $this->createWebUrl('article', array('op' => 'display')), 'success');
    }
    /*     * 分页显示 */
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;
    $status = $_GPC['status'];
   
    $condition = " o.uniacid = :uniacid and tid=:tid ";
    $paras = array(':uniacid' => $_W['uniacid'], ':tid'=>10);
    $sql = 'SELECT COUNT(*) FROM ' . tablename('kb_house_saleinfo') . ' AS `o` WHERE ' . $condition;
    $total = pdo_fetchcolumn($sql, $paras);
    if ($total > 0) {
        if ($_GPC['export'] != 'export') {
            $limit = ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
        }
        $sql = 'SELECT * FROM ' . tablename('kb_house_saleinfo') . ' AS `o` WHERE ' . $condition .
                ' ORDER BY `o`.`orderid` desc, `o`.`id` DESC ' . $limit;
        $list = pdo_fetchall($sql, $paras);
        $pager = pagination($total, $pindex, $psize);
    }
    /**
     * 添加一个活动
     */
} elseif ($operation == 'post') {

    $selects['disabled'] = array('显示', '关闭');
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $info = pdo_fetch("SELECT * FROM " . tablename('kb_house_saleinfo') . " WHERE tid=10 and id = :id AND uniacid = :uniacid", array(':id' => $id, ':uniacid' => $_W['uniacid']));
        $info['newstext'] = htmlspecialchars_decode($info['newstext']);
        
    } else {
        $info = array(
            'disabled' => 0,
        );
    }
    if (checksubmit('submit')) {
        $data = $_GPC['data'];
        $data['thumb'] = $_GPC['thumb'];
        $data['newstext'] = ($_GPC['newstext']);
        if (empty($data['title'])) {
            message('抱歉，请输入新闻名称！');
        }
        /*栏目信息*/
        $data['fid'] = intval($_GPC['fid']);
        $data['fname'] = pdo_fetchcolumn("select name from ".tablename('site_category')." where id='".$data['fid']."'");
    
        if (!empty($id)) {
            pdo_update('kb_house_saleinfo', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
           
        } else {
            $data['uniacid'] = $_W['uniacid'];
            $data['addtime'] =  date('Y-m-d H:i:s', TIMESTAMP);
            $data['tid'] = 10; /**数据分类 活动*/
            pdo_insert('kb_house_saleinfo', $data);
            $id = pdo_insertid();
        }
        message('更新信息成功', $this->createWebUrl('article', array('op' => 'display')), 'success');
    }
    /*发布栏目*/
   $children = array();
	$category = pdo_fetchall("SELECT * FROM ".tablename('site_category')." WHERE uniacid = '{$_W['uniacid']}' and enabled=1 ORDER BY parentid, displayorder DESC, id");
	foreach ($category as $index => $row) {
		if (!empty($row['parentid'])){
			$children[$row['parentid']][] = $row;
			unset($category[$index]);
		}
	} 
    /**
     * 删除一个记录
     */
} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $category = pdo_fetch("SELECT id,title FROM " . tablename('kb_house_saleinfo') . " WHERE id = '$id' and tid=10 ");
    if (empty($category)) {
        message('抱歉，不存在或是已经被删除！', $this->createWebUrl('gpoun', array('op' => 'display')), 'error');
    }
    pdo_delete('kb_house_saleinfo', array('id' => $id, 'uniacid' => $_W['uniacid']));
    message('删除成功！', $this->createWebUrl('article', array('op' => 'display')), 'success');

    
 /**
  * 添加栏目
  */  
}elseif($operation =='category'){
 
    if(isset($_GPC['keyword']) && !empty($_GPC['keyword'])){
        $keyword = trim($_GPC['keyword']);
        $sql = "select id,newshouse_id,house_title from ".tablename('kb_house_info')." where uniacid='$uniacid'  and house_title like '%$keyword%' order by id desc limit 20 ";
        $houselist = pdo_fetchall($sql);
        
    }
    $info = pdo_fetch("select * from ".tablename("kb_house_saleinfo")." where id='$id' and uniacid='$uniacid'");
    $shopids = trim($info['shopids'], ',');
    if(!empty($shopids)){
        $sql = "select id,newshouse_id,house_title from ".tablename('kb_house_info')." where uniacid='$uniacid'  and newshouse_id in($shopids) order by id desc ";
        $hashouse = pdo_fetchall($sql);
    }
    /*新增*/
    if(isset($_GPC['addhouse']) && !empty($_GPC['addhouse'])){
        $shopids.=  ",". implode(',', $_GPC['addhouse']) ;
    }
    $shops = explode(',', $shopids);
     
    if(isset($_GPC['delids']) && !empty($_GPC['delids'])){
         $shops = array_diff($shops, $_GPC['delids']);
    }
    /*提交数据*/
    if(!empty($_GPC['savehouse'])){
        $shopids = implode(',', $shops);
        $sql = "select id,newshouse_id,house_title from ".tablename('kb_house_info')." where uniacid='$uniacid'  and newshouse_id in($shopids) order by id desc ";
        $hashouse = pdo_fetchall($sql);
        $smalltext = $dot = '';
        foreach($hashouse as $key =>$val){
            $smalltext.= $dot .$val['house_title'];
            $dot = ",";
        }
        pdo_update('kb_house_saleinfo', array('shopids'=> ','.$shopids.',', 'smalltext'=> $smalltext), array('id'=>$id));
    
        message('添加成功',$this->createWebUrl('article', array('op' => 'display')), 'success');
    }
}


include $this->template('article');
