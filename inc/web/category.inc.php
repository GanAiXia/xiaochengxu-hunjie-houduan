<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

global $_GPC, $_W;
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$selects = $this->_forms;
$sec = $this->module_setting();
$uniacid = $_W['uniacid'];
$openid = $_W['openid'];
 
 

if ($operation == 'display') {
	$children = array();
	$category = pdo_fetchall("SELECT * FROM ".tablename('site_category')." WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid, displayorder DESC, id");
	foreach ($category as $index => $row) {
		if (!empty($row['parentid'])){
			$children[$row['parentid']][] = $row;
			unset($category[$index]);
		}
	}
       
	include $this->template('article_category');
        
} elseif ($operation == 'post') {
	$parentid = intval($_GPC['parentid']);
	$id = intval($_GPC['id']);
        
 	if (!empty($id)) {
		$category = pdo_fetch("SELECT * FROM ".tablename('site_category')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
		if (empty($category)) {
			itoast('分类不存在或已删除', '', 'error');
		}
	 
	} else {
		$category = array(
			'displayorder' => 0,
			'css' => array(),
		);
	}
	if (!empty($parentid)) {
		$parent = pdo_fetch("SELECT id, name FROM ".tablename('site_category')." WHERE id = '$parentid'");
		if (empty($parent)) {
			itoast('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('category', array('op' => 'display')), 'error');
		}
	}else{
            $parent['id'] = 0;
            $parent['name'] = '作为一级分类';
        }
 
	if (checksubmit('submit')) {
		if (empty($_GPC['cname'])) {
			itoast('抱歉，请输入分类名称！', '', '');
		}
		$data = array(
			'uniacid' => $_W['uniacid'],
			'name' => $_GPC['cname'],
			'displayorder' => intval($_GPC['displayorder']),
			'parentid' => intval($parentid),
			'description' => $_GPC['description'],
			'styleid' => intval($_GPC['styleid']),
			'linkurl' => $_GPC['linkurl'],
			'ishomepage' => intval($_GPC['ishomepage']),
			'enabled' => intval($_GPC['enabled']),
			'icontype' => intval($_GPC['icontype']),
			'multiid' => intval($_GPC['multiid'])
		);
		 
		if (!empty($id)) {
			unset($data['parentid']);
			pdo_update('site_category', $data, array('id' => $id));
		} else {
			pdo_insert('site_category', $data);
			$id = pdo_insertid();
			 
		}
		itoast('更新分类成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
	}
	include $this->template('article_category_post');;
        
} elseif ($operation == 'delete') {
	if (checksubmit('submit')) {
		foreach ($_GPC['rid'] as $key => $id) {
			$id = intval($id);
			$category = pdo_fetch("SELECT id, parentid, nid FROM ".tablename('site_category')." WHERE id = '$id'");
			if (empty($category)) {
				itoast('抱歉，分类不存在或是已经被删除！', referer(), 'error');
			}
		 
			pdo_delete('site_category', array('id' => $id));
		}
		itoast('分类批量删除成功！', referer(), 'success');
	} else {
		$id = intval($_GPC['id']);
		$category = pdo_fetch("SELECT id, parentid, nid FROM ".tablename('site_category')." WHERE id = '$id'");
		if (empty($category)) {
			itoast('抱歉，分类不存在或是已经被删除！', referer(), 'error');
		}
	 
		pdo_delete('site_category', array('id' => $id, 'parentid' => $id), 'OR');
		itoast('分类删除成功！', referer(), 'success');
	}
} else if ($operation == 'change_status') {
	$id = intval($_GPC['id']);
	$category_exist = pdo_get('site_category', array('id' => $id, 'uniacid' => $_W['uniacid']));
	if (!empty($category_exist)) {
		$status = $category_exist['enabled'] == 1 ? 0 : 1;
		$result = pdo_update('site_category', array('enabled' => $status), array('id' => $id));
		if ($result) {
			iajax(0, '更改成功！', $this->createWebUrl('category', array('op' => 'display')));
		} else {
			iajax(1, '更改失败！', '');
		}
	} else {
		iajax(-1, '分类不存在！', '');
	}
}