<?php
namespace app\api\controller\v1;
use app\api\controller\CommonController;
use app\api\model\Message as MessageModel;
use app\api\service\Message as MessageService;
use think\Request as Request;
use think\Controller;
use think\Exception;
use app\lib\exception\MessageException;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

class UserMessage extends CommonController{

    //新增留言
    public function addMessage()
    {
        $data = Request::instance()->param();
        $messageservice = new MessageService();
        $check = $messageservice->checkadd($data);
        switch ($check['code']) {
            case 1:
                break;
            case 2:
                $data['thirdapp_id'] = $check['thirdapp_id'];
                $data['user_id'] = $check['user_id'];
                break;
            case 3:
                throw new MessageException([
                    'msg'=>'缺少thirdapp_id或token参数',
                    'solelyCode'=>207004
                ]);
                break;
            case 4:
                throw new TokenException();
                break;
            default:
                break;
        }
        $data = $messageservice->addinfo($data);
        $res = MessageModel::addMessage($data);
        if ($res) {
            throw new SuccessMessage([
                'msg' => '留言成功'
            ]);
        }else{
            throw new MessageException([
                'msg'=>'留言失败',
                'solelyCode'=>207005
            ]);
        }
    }
}