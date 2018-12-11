<?php
/**8
 * 这个是地图找房插件
 */
  global $_GPC, $_W;
  $uniacid = $this->_uniacid;
  
  /**
   * 搜索条件
   */
  $tags = intval($_GPC['tags']);
 if($tags ==0){
     $addon = " and rent_type=0 and zhutype=0 ";
 }elseif($tags==1){
    $addon = " and rent_type=2 and zhutype=0 "; 
 } elseif($tags==2){
    $addon = "  and zhutype=1 "; 
 } elseif($tags==3){
    $addon = " and zhutype=2 "; 
 }elseif($tags==4){
    $addon = " and zhutype=3 "; 
 }
  
  $ret = pdo_fetchall("select id, shop_name,  map_x,map_y from ".tablename("kb_house"). " where map_x>0 and map_y>0 and uniacid='$uniacid' ");
  $items = array();
  foreach($ret as $key => $val){
      if($val['map_x']){
          $point = array($val['map_x'], $val['map_y']);
          /*百度坐标转换为火星坐标 腾讯地图显示火星坐标*/
            $bd_lon  = $point[0]; 
            $bd_lat  = $point[1]; 
            $X_PI = M_PI * 3000.0 / 180.0;  
            $x = $bd_lon - 0.0065;  
            $y = $bd_lat - 0.006;  
            $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $X_PI);  
            $theta = atan2($y, $x) - 0.000003 * cos($x * $X_PI);  
            $lon = $z * cos($theta);  
            $lat = $z * sin($theta); 

          $items[] = array(
              'id'=> $val['id'], 'title'=> $val['shop_name'], 'mapinfo'=> $val['mapinfo'],
              'lat' => $lat, 'lon'=> $lon
          );
      }
  }
  $this->result(0, "查询成功 $sql", $items);
  
