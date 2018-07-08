<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/23
 * Time: 22:10
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\UserCard as UserCardModel;
use app\api\model\MemberCard as MemberCardModel;
use app\api\service\UserCard as CardService;
use think\Request as Request;
use app\api\service\Token as TokenService;
use think\Db;

/**
 * 前端用户会员卡模块
 */
class UserCard extends CommonController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'getInfo'],
        'checkToken' => ['only' => 'addCard,getList,getInfo']
    ];

    public function addCard(){
        $data = Request::instance()->param();
        if (!isset($data['card_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数CardID',
                'solelyCode'=>218007
            ]);
        }
        //获取会员卡信息
        $cardinfo = MemberCardModel::getCardInfo($data['card_id']);
        if (empty($cardinfo)) {
            throw new TokenException([
                'msg' => '购买的会员卡不存在',
                'solelyCode'=>215000
            ]);
        }
        $cardservice = new CardService();
        $info = $cardservice->addinfo($data);
        Db::startTrans();
        try{
            $res = UserCardModel::addCard($info);
            if ($res==1) {
                Db::commit();
            }else{
                throw new TokenException([
                    'msg' => '购买会员卡失败',
                    'solelyCode'=>218001
                ]);
            }
        }catch (Exception $ex){
            Db::rollback();
            throw $ex;
        }
        throw new SuccessMessage([
            'msg'=>'购买成功',
        ]);
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
        $userinfo = TokenService::getUserinfo($data['token']);
        //检查更新过期会员卡
        $check = $cardservice->checkdeadline($userinfo);
        $info['map']['thirdapp_id']=$userinfo['thirdapp_id'];
        $info['map']['user_id']=$userinfo['id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = UserCardModel::getCardListToPaginate($info);
            $list = $cardservice->formatPaginateList($list);
        }else{
            $list = UserCardModel::getCardList($info);
            $list = $cardservice->formatList($list);
        }
        return $list;
    }

    public function getInfo()
    {
        $data=Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
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
            if ($res['user_id']!=$userinfo['id']) {
                throw new TokenException([
                    'msg' => '会员卡不属于你',
                    'solelyCode' => 218007
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