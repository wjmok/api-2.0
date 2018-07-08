<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018-2-11
 * Time: 11:24
 */

namespace app\api\service;
use app\api\model\Message as MessageModel;
use app\api\service\Token as TokenService;

class Message
{
    /**
     * @return array数据表空字段过滤
     */
    public function initinfo()
    {
        $remarkdome = array(
            "is_deal" => "false",
            "status"  => 1,
        );
        return $remarkdome;
    }

    public function checkadd($data)
    {
        if (isset($data['thirdapp_id'])) {
            $res['code'] = 1;
        }elseif(isset($data['token'])) {
            $tokenservice = new TokenService();
            $userinfo = TokenService::getUserinfo($data['token']);
            if ($userinfo) {
                $res['code'] = 2;
                $res['thirdapp_id'] = $userinfo['thirdapp_id'];
                $res['user_id'] = $userinfo['id'];
            }else{
                $res['code'] = 4;
            }
        }else{
            $res['code'] = 3;
        }
        return $res;
    }

    public function addinfo($data)
    {
        unset($data['version']);
        $data['create_time'] = time();
        $data = array_merge($this->initinfo(),$data);
        return $data;
    }

    //判定该信息与thirdapp是否一致
    public function ismatch($data)
    {   
        $tokenservice = new TokenService();
        $thirdapp_id = $tokenservice->getCurrentThirdAppId($data['token']);
        if (!isset($data['id'])) {
            return 2;
        }
        $messagemodel = new MessageModel();
        $res = $messagemodel->getMessageById($data['id']);
        if ($res) {
            if ($res['thirdapp_id'] == $thirdapp_id) {
                return 1;
            }else{
                return 2;
            }
        }else{
            return 3;
        }
    }
}