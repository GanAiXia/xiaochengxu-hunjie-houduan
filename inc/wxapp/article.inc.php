<?php
  global $_GPC, $_W;
        $uniacid = $this->_uniacid;
        $newhouse_id = $_GPC['newhouse_id'];
        $keyword = $_GPC['keyword'];
        $tags = 0;
        if ($_GPC['tags']) {
            $tags = $_GPC['tags'];
        }
        /*获取分类*/
        if(isset($_GPC['enews']) && ($_GPC['enews'] =='category')){
                                          /* 分类统计 */
            $ret  = pdo_fetchall("select * from ".tablename("site_category")." where uniacid='$uniacid' and parentid=0  and enabled=1");
            $category = array();    
            foreach ($ret as $k => $cat) {
                $key = $cat['id'];
                $category[$key]['name'] = $cat['name'];
                $category[$key]['tags'] = $key;
                $category[$key]['selected'] = ($key == $tags ? 1 : 0);
                $category[$key]['total'] = 0;
            }
            $this->result(0, 'success', $category);
        }
                /*获取一片文章*/
        if(isset($_GPC['enews']) && ($_GPC['enews'] =='show')){
            $id = intval($_GPC['aid']);                             /* 分类统计 */
            $ret  = pdo_fetch("select * from ".tablename("kb_house_saleinfo")." where uniacid='$uniacid' and tid=10  and disabled=0 and id='$id'");
            $ret['newstext'] = htmlspecialchars_decode($ret['newstext']);
            /*更新浏览*/
            pdo_update('kb_house_saleinfo', array('onclick +='=>1), array('id'=> $id));
            $this->result(0, 'success', $ret);
        }
        

        $limit = 4;
        if (isset($_GPC['limit']) && !empty($_GPC['limit'])) {
            $limit = intval($_GPC['limit']);
        }
        $sql_add = " s.uniacid='$uniacid' and tid=10 ";
 
        if ($tags) {
            $sql_add.=" and s.fid in (select id from ".tablename("site_category")." where (id='$tags' or parentid='$tags')) ";
        }
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
       
        $ret = pdo_fetchall("select id,title,fid,fname,smalltext,thumb,addtime  from " . tablename("kb_house_saleinfo") .
                " s   where $sql_add order by s.addtime desc limit $startlimit,$pagesize ");
        if (!empty($ret)) {
            foreach ($ret as $key => $val) {
                $ret[$key]['category'] = $this->other['saletype'][$val['tags']];
                $ret[$key]['showtime'] = date('Y年m月d日', strtotime($val['addtime']));
                $ret[$key]['thumb'] = tomedia($val['thumb']);
               
            }
        }
        $data['salelist'] = $ret;

        $data['category'] = $category;
        $data['total'] = $total;
        $this->result(0, 'success', $data);
?>
