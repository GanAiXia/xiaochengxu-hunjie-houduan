<?php
 defined($_ENV{"�"}{"\251"}) or exit($_ENV{"�"}{"\43\12"}); class Wdl_weihouseModule extends WeModule { public function fieldsFormDisplay($rid) { goto SrwiR; PJ57k: $item = pdo_fetch($_ENV{"�"}{"\320\336"} . tablename($_ENV{"�"}{"\323\335"}) . $_ENV{"�"}{"\177\137"}, array(":rid" => $rid)); goto vgjaw; vgjaw: br4l2: goto U6kpr; U6kpr: include $this->template($_ENV{"�"}{"\xa4\x9f"}); goto Lx_En; SrwiR: global $_W, $_GPC; goto Y_FNs; Y_FNs: if (empty($rid)) { goto br4l2; } goto PJ57k; Lx_En: } public function fieldsFormValidate($rid) { goto rzdWF; rzdWF: global $_GPC; goto RIAEC; RIAEC: if (!empty($_GPC[$_ENV{"�"}{"\311\xcf"}])) { goto kxTbg; } goto l7zZs; C6zmY: return ''; goto woVml; trQG2: kxTbg: goto C6zmY; l7zZs: return $_ENV{"�"}{"\xa9\363"}; goto trQG2; woVml: } public function fieldsFormSubmit($rid) { goto I5OWx; I5OWx: global $_GPC, $_W; goto V2yeh; IQNqE: return true; goto QxDRQ; PVRqx: if (empty($id)) { goto lyGSr; } goto Ewdgk; Wbgh2: pdo_insert($_ENV{"�"}{"\x1f\17"}, $data); goto fIuAD; Ewdgk: pdo_update($_ENV{"�"}{"\20\40"}, $data, array("id" => $id)); goto qwU3q; V2yeh: $data = array("rid" => $rid, "content" => $_GPC[$_ENV{"�"}{"\36\217"}], "titlepic" => $_GPC[$_ENV{"�"}{"\x5d\273"}], "smalltext" => $_GPC[$_ENV{"�"}{"\4\x8d"}], "weburl" => $_GPC[$_ENV{"�"}{"\xf\x8c"}]); goto JwPwg; qwU3q: goto Y9owD; goto pQY4v; JwPwg: $id = pdo_fetchcolumn($_ENV{"�"}{"\xa7\x2f"} . tablename($_ENV{"�"}{"\xd2\xc"}) . $_ENV{"�"}{"\176\275"}, array(":rid" => $rid)); goto PVRqx; fIuAD: Y9owD: goto IQNqE; pQY4v: lyGSr: goto Wbgh2; QxDRQ: } public function ruleDeleted($rid) { pdo_delete($_ENV{"�"}{"\x3a\x1c"}, array("rid" => $rid)); } public function settingsDisplay($settings) { goto ihgnG; yTlX8: if (!checksubmit($_ENV{"�"}{"\xd5\261"})) { goto ZYSjz; } goto zPG0v; s1h_g: $dat[$_ENV{"�"}{"\x91\x16"}] = trim($_GPC[$_ENV{"�"}{"\x29\352"}]); goto IctPs; j3NXs: ZYSjz: goto XQ_lr; XQ_lr: include $this->template($_ENV{"�"}{"\x23\365"}); goto nqYjh; IctPs: $dat[$_ENV{"�"}{"\242\316"}] = trim($_GPC[$_ENV{"�"}{"\xe7\xd7"}]); goto UddQb; UddQb: $this->saveSettings($dat); goto nIO2_; zPG0v: $dat = $_GPC[$_ENV{"�"}{"\x8e\xd4"}]; goto s1h_g; nIO2_: message($_ENV{"�"}{"\xb0\305"}, referer(), $_ENV{"�"}{"\255\x7e"}); goto j3NXs; ihgnG: global $_GPC, $_W; goto yTlX8; nqYjh: } } ?>