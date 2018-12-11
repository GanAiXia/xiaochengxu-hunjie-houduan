<?php
/** 
 * 伍成才 技术出品
 * 微房产中介公司营销房源，目标是可以省去58赶集上 发布房源的费用
 * @author kunbei   联系QQ 2687431234
 * @url http://blog.siyueweb.cn;
 */
defined('IN_IA') or exit('Access Denied');
define('IMS_FAMILY', 'kunbe');
session_start();

require IA_ROOT . '/addons/wdl_weihouse/vender/element.class.php';
 

class Wdl_weihouseModuleSite extends WeModuleSite {
    
    public $settings;
    public $_forms =  null;
    /**
     * 构造函数
     * @global type $_GPC
     * @global type $_W
     */

    public function __construct() {
        $adapter =  include  IA_ROOT. "/addons/wdl_weihouse/vender/adapter.php";
       
        $this->_forms = $adapter['sechouse'];
    }
    
    public function module_setting(){
        global $_GPC, $_W;
        $sql = 'SELECT `settings` FROM ' . tablename('uni_account_modules') . ' WHERE `uniacid` = :uniacid AND `module` = :module';
        $settings = pdo_fetchcolumn($sql, array(':uniacid' => $_W['uniacid'], ':module' => 'wdl_weihouse'));
        return  iunserializer($settings);
    }
    
}