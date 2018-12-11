<?php
/**
 * 会员管理
 */
defined('IN_IA') or exit('Access Denied');
global $_GPC, $_W;
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';	
$selects = $this->_forms;
$sec = $this->module_setting();
$uniacid = $_W['uniacid']; 
$openid = $_W['openid'];
 

/*
 * 房源自定义属性
 */
if($operation=='param'){
    $tag = random(32);	 
    include $this->template('param');
    exit;
}
/*
 * 添加房源信息
 */
if ($operation == 'display') {
    $id = intval($_GPC['id']);
    /*发布类型 zhu=女士征婚,zhutype 0=男士征婚 1=写字楼 2=女生征婚 3=生意*/
    $form_tpname = array('sec','xie','sp','pumian');
    $send_type = array(
        'sec'=>array(0=>'男士征婚', '2'=>'出租'),'xie'=> array(0=>'写字楼出售',2=>'写字楼出租'),
        'sp'=>array(0=>'商铺出售', '2'=>'商铺出租'),'pumian'=>array(0=>'生意转让' )
    );
    $renttype = intval($_GPC['rt']);
    $zhutype = intval($_GPC['zt']) ;

    /**/
    if($id >0 ){
        $item = pdo_fetch("SELECT * FROM " . tablename('kb_sechouse') . " WHERE uniacid= :uniacid AND  id = :id", array(':uniacid'=>$_W['uniacid'],':id' => $id));
        if (empty($item)) {
                message('抱歉，房源不存在或是已经删除！', '', 'error');
        }
        
        $params = pdo_fetchall("select * from " . tablename('kb_sechouse_param') . " where houseid=:id order by displayorder asc", array(':id' => $id));
        $piclist1 = unserialize($item['thumb_url']);
        $piclist = array();
        if(is_array($piclist1)){
                foreach($piclist1 as $p){
                        $piclist[] = is_array($p)?$p['attachment']:$p;
                }
        }
        /*修改的时候判断房源类型*/
        $renttype = $item['rent_type'];
        $zhutype = $item['zhutype'];
        /*处理 写字楼、商铺 租金 元/㎡/月*/
        if($zhutype==0 && $renttype==2){
            /*女士征婚 danyuan 是 租赁类型， menpai 是 押金方式，*/
        }else{
            if($item['danyuan'] && $item['menpai']){
                $item['loyer'] = $item['danyuan'];
                $item['prix_unitaire'] = $item['menpai'];
            } 
        }
      
    }
    /*控制发布的模板和 出租和出售*/
    $form_name = $form_tpname[$zhutype];
    if(empty( $form_tpname[$zhutype])){
        $form_name  = 'sec'; /*控制发布模板*/
    }
    /*控制出售=0,2=出租*/
    $form_title = $send_type[$form_name][$renttype];
    $form_input_name = "house_$form_name";
    /*女士征婚单独处理*/
    if($renttype==2 && $zhutype==0){
        $form_input_name = "house_zhu";
    }
    
    if(checksubmit('submit')){
        
        if(empty($_GPC['thumbs'])){                
            $_GPC['thumbs'] = array();
        }
        if(!empty($_GPC['thumb'])){                
            $data['ispic'] = 1;
        }
        $data = $_GPC['data'];
        $data['uniacid'] = intval($_W['uniacid']);
        $data['uid'] = $_W['uid'];
        $data['thumb'] = $_GPC['thumb'];
        $data['endtime'] = strtotime($_GPC['endtime']);        
        $data['update_time'] = TIMESTAMP;       
        $data['description'] =  htmlspecialchars_decode($_GPC['description']);
        /*处理价格 写字楼、商铺 租金 元/㎡/月*/
        if($data['prix_unitaire'] == '元/㎡/月'){
            $data['danyuan'] = $data['loyer'];
            $data['menpai'] = $data['prix_unitaire'] ;
            $data['loyer'] = $data['danyuan']* $data['superficie'];
            $data['prix_unitaire'] = '元/月';
        }else{
            /*$data['danyuan'] = '';
            $data['menpai'] = '' ;*/
        }
        $data['loyer'] = sprintf('%.2f', $data['loyer']);
        $data['isonline'] = 1;
        if(!empty($_GPC['disposition']) && is_array($_GPC['disposition'])){
            $data['disposition'] = implode(',', $_GPC['disposition']); 
        }
        /*生意转让的时候合并行业和经营类型*/
        if(!empty($_GPC['hangye']) || !empty($_GPC['jingyin'])){
            $data['disposition'] = $_GPC['hangye'].",".$_GPC['jingyin'];
        }
         
        if(is_array($_GPC['thumbs'])){
            $data['thumb_url'] = serialize($_GPC['thumbs']);
        }
        /*处理管理员，如果 有管理员就覆盖联系人*/
        if($data['broker_id']>0){
            $bro  = pdo_fetch("select * from ".tablename("kb_sechouse_broker")." where id='".$data['broker_id']."'");
            $data['broker_name'] = $bro['nickname'];
            $data['publish_name'] = $bro['nickname'];
            $data['linkphone'] = $bro['mobile'];
        }
        /*新增和修改保存数据*/
        if (empty($id)) {
                $data['refresh_time'] = TIMESTAMP;
                $data['add_time'] = TIMESTAMP;
                pdo_insert('kb_sechouse', $data);
                $id = pdo_insertid();
                
                $return_url = $this->createWebUrl('house', array('op' => 'display', 'id' => $id));
        } else {
                 
                pdo_update('kb_sechouse', $data, array('id' => $id));
                $return_url = $_GPC['referer'];
        }

        /*
         * 处理自定义参数
         */
        $param_ids = $_POST['param_id'];
        $param_titles = $_POST['param_title'];
        $param_values = $_POST['param_value'];
        $param_displayorders = $_POST['param_displayorder'];
        $len = count($param_ids);
        $paramids = array();
        for ($k = 0; $k < $len; $k++) {
                $param_id = "";
                $get_param_id = $param_ids[$k];
                $a = array(
                        "title" => $param_titles[$k],
                        "value" => $param_values[$k],
                        "displayorder" => $k,
                        "houseid" => $id,
                );
                if (!is_numeric($get_param_id)) {
                        pdo_insert("kb_sechouse_param", $a);
                        $param_id = pdo_insertid();
                } else {
                        pdo_update("kb_sechouse_param", $a, array('id' => $get_param_id));
                        $param_id = $get_param_id;
                }
                $paramids[] = $param_id;
        }
        if (count($paramids) > 0) {
                pdo_query("delete from " . tablename('kb_sechouse_param') . " where houseid=$id and id not in ( " . implode(',', $paramids) . ")");
        }
        else{
                pdo_query("delete from " . tablename('kb_sechouse_param') . " where houseid=$id");
        }
        
        message('房源更新成功！', $return_url, 'success');
    }
    /*获取所有经纪人列表*/
    $broker = pdo_fetchall("select id,nickname from ".tablename("kb_sechouse_broker")." where iscompany=1 and uniacid='$uniacid'");
    if(!empty($broker)){
        foreach($broker as $k =>$v){
            $brokers[$v['id']] = $v['nickname'];
        }
    }
    /*区域婚姻状况*/
    $sitedata['areas'] = form_element::_option($sec['areas']);
    /* 查询婚姻状况 */
    $quan = pdo_fetchall("select * from " . tablename("kb_sechouse_area") . " where uniacid='$uniacid' and type=2 order by orderid desc ,id desc");
    if (!empty($quan)) {
        $tmp = array();
        foreach ($quan as $key => $q) {
            $tmp[$q['area']][] = $q['name'];
        }
        foreach ($sitedata['areas'] as $k => $pv) {
            $sitedata['quan'][$k] = $tmp[$pv];
        }
    }
   
    $referer = $_SERVER['HTTP_REFERER'] ;    
    include $this->template('house_form'); 
}
/*删除房源*/
if($operation =='delete'){
     $id = intval($_GPC['id']);
     $info = pdo_fetch("select thumb,thumb_url from ".tablename('kb_sechouse')." where id='$id' and uniacid='$uniacid'");
     /*删除附件图片*/
     if($info['thumb']){
         @unlink(IA_ROOT .'/attachment/'. $info['thumb']);
     }
     if($info['thumb_url']){
         $thumbs = unserialize($info['thumb_url']);
         foreach($thumbs as $key => $thumb){
             @unlink(IA_ROOT .'/attachment/'. $thumb);
         }
     }
     /*删除附件图片*/
     pdo_query("delete from " . tablename('kb_sechouse') . " where id='$id' and uniacid='$uniacid'");
     pdo_query("delete from " . tablename('kb_sechouse_param') . " where houseid='$id'");
     message('删除房源成功！', $this->createWebUrl('houselist', array('op' => 'display')), 'success');  
}
 

 