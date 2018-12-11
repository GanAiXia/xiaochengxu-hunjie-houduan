<?php
/** * 语音回复处理类 *  * [WeEngine System] Copyright (c) 2013 WE7.CC */
defined('IN_IA') or exit('Access Denied');
class Wdl_weihouseModuleProcessor extends WeModuleProcessor {
    
    public function respond() {
        global $_W;  
        $uniacid = $_W['uniacid'];
        $content = $this->message['content'];	

        /**这里定义此模块进行消息处理时的具体过程, 请查看微擎文档来编写你的代码*/
        $rule = pdo_fetch("select rid from ".tablename("rule_keyword")." where uniacid='$uniacid' and module='wdl_weihouse' and content like '%$content%'");
        if($rule['rid']){
            $replay = pdo_fetch("select * from ".tablename("kb_sechouse_replay")." where rid=:rid", array(':rid'=>$rule['rid']));
            if(!empty($replay)){
                if(!empty($replay['titlepic'])){
                    $weburl = $this->createMobileUrl('index');
                    /*创建一个带openid信息的访问模块introduce方法的地址 */
                    if($replay['weburl']){
                        $weburl = $replay['weburl'];
                    }
                    if(!empty($replay['smalltext'])){
                        /*回应一个图文消息*/
                        return $this->respNews(array(
                                'Title' => $replay['content'],
                                'Description' =>  $replay['smalltext'],
                                'PicUrl' => tomedia( $replay['titlepic']),
                                'Url' => $weburl.'&t=233', 

                        ));
                    }elseif(!empty($replay['titlepic'])){
                        $account_api = WeAccount::create();
                        //任意指定一个文件上传
                        $result = $account_api->uploadMedia(IA_ROOT . '/attachment/'.$replay['titlepic'], 'image');
                        
                        return $this->respImage($result['media_id']);
                    }else{
                        
                    }
                    /**/
                } 
            }
        }
                                
       /*在线客服处理*/
        if(!$this->inContext) {
                $reply = '成功连线在线客服，开始提问吧！回复“结束” 退出在线客服！';                
                $this->beginContext();
                
                /*查询在线客服*/
                cache_delete('wdl_online_kefu');
                /*获取公众号access_token*/
                load()->classs('weixin.account');
                $accObj= new WeixinAccount();
                $ACCESS_TOKEN = $accObj->fetch_available_token();
                /*获取调用系统的get post 方法*/  
                load()->func('communication');
                $url = 'https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token='.$ACCESS_TOKEN;
                $ret  = ihttp_get($url);
                if($ret['code']==200){
                    $kf = json_decode($ret['content'], true );
                    if(!empty($kf['kf_online_list'])){
                         $reply .=' 编号'.$kf['kf_online_list'][0]['kf_id'] .'客服在线为您服务。';
                         
                         cache_write('wdl_online_kefu',$kf['kf_online_list'][0]['kf_account'] );
                    }else{
                        $reply .=' 当前没有客服在线，您的消息将会留言。';                        
                        
                    }
                    
                }
                 
                
                return $this->respText($reply);
                /*
                 *  如果是按照规则触发到本模块, 那么先输出提示问题语句, 并启动上下文来锁定会话, 以保证下次回复依然执行到本模块
                 */
        } else {
            
            if($this->message['content'] =='结束'){
                $this->endContext();
                $reply = ' 本次咨询结束，谢谢您的使用！';
                return $this->respText($reply);
            }
            
            /*记录在线咨询的每次提问*/
            $par['uniacid'] = $_W['uniacid'];
            $par['openid'] = $this->message['from'];
            $par['askcon'] = $content;
            $par['addtime'] = TIMESTAMP;
            
            pdo_insert('kb_ask', $par);
           
               
        }
        /*如果客服在线，转发消息到指定客服*/
        $kf_account = cache_load('wdl_online_kefu');
        $response = array();
        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $this->message['from'];
        $response['MsgType'] = 'transfer_customer_service';
        $response['CreateTime'] = TIMESTAMP;
        $response['Content'] = htmlspecialchars_decode($content); 
        if(!empty($kf_account)){
          /* $response['TransInfo']['KfAccount'] = $kf_account;*/
        }
        return $response;
        /*找不到可以回复的内容，自己回复粉丝发送的内容*/
       
         
         			
       }
       
    }
 