<?php
/**
 * 微房产中介
 *
 * @author 伍成才 2687431234
 * @url
 */
defined('IN_IA') or exit('Access Denied');
class Wdl_weihouseModule extends WeModule {
    public function fieldsFormDisplay($rid) {
        global $_W, $_GPC;
        /**
         * 此处分为两种情况，新增规则或是修改规则。
         * 如果rid不为0，则需要查询出此规则对应的回复数据。
         */
        if (!empty($rid)) {
            $item = pdo_fetch("SELECT * FROM " . tablename('kb_sechouse_replay') . " WHERE rid = :rid", array(':rid' => $rid));
        }
        include $this->template('rule');
    }
    public function fieldsFormValidate($rid) {
        /*
         * 规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
         */
        global $_GPC;
        /*
         * 此处服务端验证表单数据的完整性，直接返回错误信息。
         */
        if (empty($_GPC['content'])) {
            return '请填写回复内容';
        }
        return '';
    }
    public function fieldsFormSubmit($rid) {
        global $_GPC, $_W;
        /*        
         * 此处各种验证通过后，需要进行入库操作。        
         * 入库时需要注意，此处数据可能为更新操作也可能为新增数据。        
        */
        $data = array('rid' => $rid, 'content' => $_GPC['content'], 'titlepic' => $_GPC['titlepic'], 'smalltext' => $_GPC['smalltext'], 'weburl' => $_GPC['weburl'],);
        $id = pdo_fetchcolumn("SELECT id FROM " . tablename('kb_sechouse_replay') . " WHERE rid = :rid", array(':rid' => $rid));
        if (empty($id)) {
            pdo_insert('kb_sechouse_replay', $data);
        } else {
            pdo_update('kb_sechouse_replay', $data, array('id' => $id));
        }
        return true;
    }
    public function ruleDeleted($rid) {
        /*
         * 删除规则时调用，这里 $rid 为对应的规则编号         
         * 此处可能需要一些权限及数据方面的判断        
         * 除了表数据可能还需要删除一些附带的图片等资源        
        */
        pdo_delete('kb_sechouse_replay', array('rid' => $rid));
    }
    public function settingsDisplay($settings) {
        global $_GPC, $_W;
        if (checksubmit('submit')) {
            /* 字段验证, 并获得正确的数据$dat */
            $dat = $_GPC['pay'];
            $dat['logo'] = trim($_GPC['logo']);
            $dat['banner'] = trim($_GPC['banner']);
            $this->saveSettings($dat);
            message('配置参数更新成功！', referer(), 'success');
        }
        include $this->template('setting');
    }
}
