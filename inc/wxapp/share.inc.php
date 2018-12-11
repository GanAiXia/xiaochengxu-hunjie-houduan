<?php
/**
 * 这个是分享中心的小程序接口
 */
global $_GPC, $_W;
$uniacid = $this->_uniacid;

$uid = $_W['member']['uid'];
      $openid = $_W['openid'];
$operation = 'display';
if(isset($_GPC['op']) && !empty($_GPC['op'])){
    $operation = $_GPC['op'];
}
if($operation =='display'){
     $post_uid = $_GPC['uid'];
     $ret = pdo_fetch("select * from ".tablename("kb_share_user"). " where uniacid='$uniacid' and uid='$post_uid'");
      
     $this->result(0, 'success', $ret);
}
/*获取我推广过的房源*/
elseif($operation =='myshare'){
    $limit = 4;
    if (isset($_GPC['limit']) && !empty($_GPC['limit'])) {
        $limit = intval($_GPC['limit']);
    }
    $page = max(1, $_GPC['page']);
    $pagesize = $limit;
    $startlimit = ($page - 1) * $pagesize;
    
    $sql_add_sec = " id in (select infoid from ".tablename("kb_share_scence")." where uid='$uid' and uniacid='$uniacid' and category='sec' group by infoid  )";    
    
    $sql_add_newshop =  " id in (select infoid from ".tablename("kb_share_scence")." where uid='$uid' and uniacid='$uniacid' and category='newshop' group by infoid  )";    
    
    $ret_sec = pdo_fetchall("select id, title, loyer,prix_unitaire, hall,room,garder, rent_type from " .tablename("kb_sechouse")." where $sql_add_sec  limit 50 " );
    
    $ret_newshop = pdo_fetchall("select newshouse_id, house_title, house_type,average_price  from " .tablename("kb_house_info")." where $sql_add_newshop  limit 50");
    
    $data['salelist'] = array('sec'=>$ret_sec ,'newshop'=> $ret_newshop); 
    $data['total'] = $total;
    $this->result(0, 'success', $data);
    
}
/*获取我的客户*/
elseif($operation =='mytree'){
    $limit = 4;
    if (isset($_GPC['limit']) && !empty($_GPC['limit'])) {
        $limit = intval($_GPC['limit']);
    }
    $sql_add = " uniacid='$uniacid' and parent_uid='$uid' ";

    $page = max(1, $_GPC['page']);
    $pagesize = $limit;
    $startlimit = ($page - 1) * $pagesize;
    $category = array();
    if(isset($_GPC['total']) && ! empty($_GPC['total'])){
        $total = $_GPC['total'];  
     }else{
         $total = pdo_fetchcolumn("select count(*)  from ". tablename("kb_share_user") ."  where $sql_add ");
    }       
    $ret = pdo_fetchall("select *  from " . tablename("kb_share_user") . "   where $sql_add order by  addtime desc limit $startlimit,$pagesize ");
    if (!empty($ret)) {
        foreach ($ret as $key => $val) {               
            $ret[$key]['showtime'] = date('Y年m月d日', ($val['addtime']));              

        }
    }
    $data['salelist'] = $ret; 
    $data['total'] = $total;
    $this->result(0, 'success', $data); 
    
}
/*保存我的分销人资料*/
elseif($operation=='modify'){
      $post_uid = $_GPC['uid'];
      /**/
      $save['uid'] = $_GPC['uid'];
      $save['name'] = $_GPC['username'];
      $save['mobile'] = $_GPC['mobile'];
      $save['identify'] = $_GPC['identify'];
      $save['openid'] = $openid;
      $save['uniacid'] = $uniacid;
      /*判断是否扫码进来的*/
      $scence = $_GPC['scence'];
      if(isset($_GPC['scence']) && !empty($_GPC['scence'])){
          $qrinfo = pdo_fetch("select * from ". tablename("kb_share_scence")."  where scence='$scence' order by id desc");
          if($qrinfo['uid'] != $uid){
                $save['parent_uid'] = $qrinfo['uid'];
          }else{
              $save['parent_uid'] = 0;
          }
      }
     /**/
      $ret = pdo_fetch("select * from ".tablename("kb_share_user"). " where uniacid='$uniacid' and uid='$post_uid'");
      if($ret['id']>0){
          pdo_update("kb_share_user", $save, array('id'=> $ret['id']));
      }else{
          $save['addtime'] = TIMESTAMP;
          pdo_insert("kb_share_user", $save);
      }
      
       $this->result(0, 'success', $save);
}/*获取场景信息*/
elseif($operation=='scence'){
 
      /*判断是否扫码进来的*/
      $scence = $_GPC['scence'];
      $qrinfo = array();
      if(isset($_GPC['scence']) && !empty($_GPC['scence'])){
          $qrinfo = pdo_fetch("select * from ". tablename("kb_share_scence")."  where scence='$scence' order by id desc");
          
      } 
 
      
      $this->result(0, 'success', $qrinfo);
}
?>
