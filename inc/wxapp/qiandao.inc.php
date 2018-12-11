<?php
/**
 * 签到插件 kunbei 2018-06-23
 */
    global $_GPC, $_W;
    $uniacid = $this->_uniacid;          
    $uid = $_W['member']['uid'];
    load()->model('mc');
    /* 代金券和折扣券的余额记录, */ 
    $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
    $jifen = $this->module['config']['credit']['qiandao'] ? max(0,$this->module['config']['credit']['qiandao']) : 5;
    /*查询今日是否签到过*/
    if(empty($uid)){
        return false;
    }
  
    $ret = pdo_fetchcolumn( "select count(*)  from " 
                . tablename("kb_sechouse_actlog") . 
                " where ecuid='$uid' and actname='qiandao' and uniacid='$uniacid'
                and (left(`addtime`,10)=left(NOW(),10)) "
            );
  
    if($ret>0){
        
        $this->result(0, 'hasdo', "今日已签到，明日再来！");
        
    }else{       
   
        if ($jifen > 0) {
            mc_credit_update($uid, 'credit1', $jifen, array($uid, '每日签到赠送积分：' . $jifen));
            pdo_insert("kb_sechouse_actlog", array(
                'actname' => 'addcredit1',
                'addtime' => date('Y-m-d H:i:s', TIMESTAMP),
                'money' => $jifen, 'ecuid' => $uid, 'acttype' => 7, 'isadd' => 1,
                'infoid' => 0, 'uniacid' => $uniacid,
                'note' => '每日签到赠送积分'
            ));
             /*添加操作日志*/
            pdo_insert("kb_sechouse_actlog", array(
                'actname' => 'qiandao',
                'addtime' => date('Y-m-d H:i:s', TIMESTAMP),
                'money' => 0, 'ecuid' => $uid, 'acttype' => 1, 'isadd' => 0,
                'infoid' => 0, 'uniacid' => $uniacid,
                'note' => '签到'
            ));
        }
    }
    $this->_send_notice_account('qiandao_notice',"【{$uid}】用户签到成功", '每日签到赠送积分：' . $jifen,  date('Y-m-d H:i:s', TIMESTAMP) );
    $this->result(0, 'success', '签到成功,积分+'.$jifen );
?>
