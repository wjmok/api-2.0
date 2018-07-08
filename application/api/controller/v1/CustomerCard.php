<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/24
 * Time: 11:55
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\UserCard as UserCardModel;
use app\api\service\CustomerCard as CardService;
use think\Request as Request; 
use think\Cache;

/**
 * 后端预约订单模块
 */
class CustomerCard extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editCard,delCard,getInfo']
    ];

    public function editCard(){
        $data = Request::instance()->param();
        $cardservice = new CardService();
        $checkinfo = $cardservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该会员卡不属于本商户',
                    'solelyCode' => 218003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '会员卡已删除',
                    'solelyCode' => 218002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '用户会员卡不存在',
                    'solelyCode' => 218000
                ]);
        }
        $res = UserCardModel::updateCard($data['id'],$data);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改会员卡成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '更新会员卡信息失败',
                'solelyCode' => 218004
            ]);
        }
    }

    public function delCard()
    {
        $data = Request::instance()->param();
        $cardservice = new CardService();
        $checkinfo = $cardservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break; 
            case -1:
                throw new TokenException([
                    'msg' => '该会员卡不属于本商户',
                    'solelyCode' => 218003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '会员卡已删除',
                    'solelyCode' => 218002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '用户会员卡不存在',
                    'solelyCode' => 218000
                ]);
        }
        $res = UserCardModel::delCard($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除会员卡成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除会员卡失败',
                'solelyCode' => 218005
            ]);
        }
    }

    public function getList()
    {
        $data=Request::instance()->param();
        $cardservice = new CardService();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info=preAll($data);
        $userinfo=Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id']=$userinfo->thirdapp_id;
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = UserCardModel::getCardListToPaginate($info);
            $list = $cardservice->formatPaginateList($list);
        }else{
            $list = UserCardModel::getCardList($info);
            $list = $cardservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取用户会员卡列表失败',
                'solelyCode' => 218006
            ]);
        }
    }

    public function getInfo()
    {
        $data=Request::instance()->param();
        $userinfo=Cache::get('info'.$data['token']);
        $res=UserCardModel::getCardInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '会员卡已删除',
                    'solelyCode' => 218002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '会员卡不属于本商户',
                    'solelyCode' => 218003
                ]);
            }
            $cardservice = new CardService();
            $res = $cardservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '用户会员卡不存在',
                'solelyCode' => 218000
            ]);
        }
    }
}