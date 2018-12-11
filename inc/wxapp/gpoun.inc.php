<?php
  global $_GPC, $_W;
        $uniacid = $this->_uniacid;
        $newhouse_id = $_GPC['newhouse_id'];
        $keyword = $_GPC['keyword'];
        $tags = 0;
        $tid = 1; /*这个是获取数据类型*/
        if ($_GPC['tags']) {
            $tags = $_GPC['tags'];
        }
                 /*获取一片文章*/
        if(isset($_GPC['enews']) && ($_GPC['enews'] =='show')){
            $id = intval($_GPC['aid']);                             /* 分类统计 */
            $ret  = pdo_fetch("select * from ".tablename("kb_house_saleinfo")." where uniacid='$uniacid' and  id='$id'");
       
            /*更新浏览*/
            pdo_update('kb_house_saleinfo', array('onclick +='=>1), array('id'=> $id));
            $this->result(0, 'success', $ret);
        }
        /**/

        $limit = 4;
        if (isset($_GPC['limit']) && !empty($_GPC['limit'])) {
            $limit = intval($_GPC['limit']);
        }
        $sql_add = " s.uniacid='$uniacid' and tid='$tid'  and disabled=0";
 
    
        if ($keyword) {
            $sql_add.=" and ( s.title like '%$keyword%' )";
        }
        $page = max(1, $_GPC['page']);
        $pagesize = $limit;
        $startlimit = ($page - 1) * $pagesize;
        $category = array();
        if(isset($_GPC['total']) && ! empty($_GPC['total'])){
            $total = $_GPC['total'];   
       
        }else{
             $total = pdo_fetchcolumn("select count(*)  from ". tablename("kb_house_saleinfo") ." s where $sql_add ");

        }
       
        $ret = pdo_fetchall("select *  from " . tablename("kb_house_saleinfo") . " s   where $sql_add order by s.orderid desc, id desc limit $startlimit,$pagesize ");
        if (!empty($ret)) {
            foreach ($ret as $key => $val) {
                $ret[$key]['category'] = $this->other['saletype'][$val['tags']];
                $ret[$key]['showtime'] = date('Y年m月d日', strtotime($val['addtime']));
                $ret[$key]['thumb'] = tomedia($val['thumb']);
                if(!empty($val['shopids'])){
                    $shopids = trim($val['shopids'], ',');
                    $fileds = $this->_house_list_field();
                    $sql = "SELECT $fileds FROM " . tablename("kb_house_info") . " where  uniacid='$uniacid' and newshouse_id in ($shopids)  order by id desc ";
                    $items = pdo_fetchall($sql);
                    foreach ($items as $k => $house) {
                        $ret[$key]['newhouse'][] = $this->_format_newhouse_item($house);
                    }
                }
               
            }
           
        }
        $data['salelist'] = $ret;
 
        $data['total'] = $total;
        $this->result(0, 'success', $data);
?>
