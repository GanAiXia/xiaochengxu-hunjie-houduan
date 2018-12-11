<?php

/**
 * 这个是获取 首页配置的文件
 */
 
global $_GPC, $_W; 
 
$uniacid = $this->_uniacid;
$uid = $_W['member']['uid'];  
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$selects = $this->_forms;
/*按placeid获取配置*/
if($operation =='display'){

    $place = $_GPC['placeids'];

    $ret = pdo_fetchall("select * from ".  tablename("kb_config")." where uniacid='$uniacid' and placeid in($place) and module='wdl_weihouse'");

    foreach($ret as $key =>$val){
        $params[$val['confkey']] = iunserializer($val['conf_value']);
    }
    /*获取首页菜单*/
}elseif($operation =='navmenu'){
    $ret = pdo_fetch("select * from ".  tablename("kb_config")." where uniacid='$uniacid' and placeid=1 and module='wdl_weihouse'");
    $ret2 = pdo_fetch("select * from ".  tablename("kb_config")." where uniacid='$uniacid' and placeid=11 and module='wdl_weihouse'");
    $confmenu = iunserializer($ret2['conf_value']);
     
    /*默认菜单*/
    $navmenu = include  dirname(__FILE__).  "/./../../vender/menu.php";
     
    if($confmenu['index']==1){
        $navmenu =  iunserializer($ret['conf_value']);
        foreach ($navmenu as $key=>$v){
            $tmp[$v['order']] = $v;
        }
        ksort($tmp);
        $navmenu = $tmp;
    } 
    $params = array();
    foreach($navmenu as $key =>$val){
        if($val['isshow']==1){
            $params[] = $val;
        }
    }
    
   
}elseif($operation =='adinfo'){
    /*获取首页广告*/
     $adplace = include  dirname(__FILE__).  "/./../../vender/adplace.php";
     foreach($adplace as $key =>$place){
         $ret = pdo_fetch("select * from ".  tablename("kb_config")." where uniacid='$uniacid' and placeid=201 and confkey='$key' and module='wdl_weihouse' order by id desc ");
         if($ret['conf_value']){
             $adplace[$key]['place'] = iunserializer($ret['conf_value']);
         }
         $content= array();
         $ret2 = pdo_fetchall("select * from ".  tablename("kb_config")." where uniacid='$uniacid' and placeid=202 and confkey='$key' and module='wdl_weihouse' ");
         if($ret2){
             foreach($ret2 as $k => $val){
                 if(! empty($val['conf_value'])){
                     $v = iunserializer($val['conf_value']);
                     $v['thumb'] = tomedia($v['image']); 
                     $v['image'] = tomedia($v['image']);
                     $v['url'] = trim($v['url']);
                     $content[] = $v; 
                 }
                 
             }
         }
          $adplace[$key]['content'] = $content;
     }
     $params = $adplace;
}

$this->result(0, '', $params);

?>
