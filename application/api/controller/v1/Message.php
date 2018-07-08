<?php
namespace app\api\controller\v1;
use app\api\controller\BaseController;
use app\api\model\Message as MessageModel;
use app\api\service\Message as MessageService;
use think\Request as Request;
use think\Controller;
use think\Exception;
use app\lib\exception\MessageException;
use app\lib\exception\SuccessMessage;
use app\api\service\Token as TokenService;
use think\Cache;

class Message extends BaseController{

    //获取留言列表
    public function getMessageList()
    {
        $data = Request::instance()->param();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        if(isset($data['pagesize'])&&isset($data['currentPage'])){
            $list = MessageModel::getMessageToPaginate($info);
        }else{
            $list = MessageModel::getMessageList($info);
        }
        return $list;
    }

    //处理留言
    public function dealMessage()
    {
        $data=Request::instance()->param();
        $messageservice = new MessageService();
        $check = $messageservice->ismatch($data);
        switch ($check) {
            case 1:
                break;
            case 2:
                throw new MessageException();
                break;
            case 3:
                throw new MessageException([
                    'msg'=>'此留言信息不属于本商户',
                    'solelyCode'=>207001
                ]);
                break;
            default:
                break;
        }
        $update['is_deal'] = "true";
        $update['deal_time'] = time();
        $res = MessageModel::updateInfo($data['id'],$update);
        if ($res) {
            throw new SuccessMessage([
                'msg' => '处理成功'
            ]);
        }else{
            throw new MessageException([
                'msg'=>'留言处理失败',
                'solelyCode'=>207002
            ]);
        }
    }

    //软删除留言
    public function deleteMessage()
    {
        $data=Request::instance()->param();
        $messageservice = new MessageService();
        $check = $messageservice->ismatch($data);
        switch ($check) {
            case 1:
                break;
            case 2:
                throw new MessageException();
                break;
            case 3:
                throw new MessageException([
                    'msg'=>'此留言信息不属于本商户',
                    'solelyCode'=>207001
                ]);
                break;
            default:
                break;
        }
        $update['status'] = -1;
        $update['delete_time'] = time();
        $res = MessageModel::updateInfo($data['id'],$update);
        if ($res) {
            throw new SuccessMessage([
                'msg' => '删除成功'
            ]);
        }else{
            throw new MessageException([
                'msg'=>'留言删除失败',
                'solelyCode'=>207003
            ]);
        }
    }
}