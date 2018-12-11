<?php
/**
 * 这个是楼盘相册
 */
    global $_GPC, $_W;
    $uniacid = $this->_uniacid;          
    $uid = $_W['member']['uid']; 
    $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
    
    $newhouse_id = intval($_GPC['newhouse_id']);
    
    $ret = pdo_fetchall("select * from ".  tablename("kb_house_attach"). " where newhouse_id ='$newhouse_id' and uniacid='$uniacid'");
    $images = array();
    foreach ($ret as $key => $val){
        $images[] = toimage($val['img_url']);
    }
    $this->result(0, 'success', $images );
    
?>
