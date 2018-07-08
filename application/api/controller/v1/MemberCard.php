<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/21
 * Time: 21:07
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\MemberCard as CardModel;
use app\api\service\MemberCard as MemberCardService;
use think\Request as Request;
use think\Cache;

/**
 * 后端会员卡模块
 */
class MemberCard extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editCard,delCard,getInfo']
    ];

    public function addCard(){
        $data = Request::instance()->param();
        $cardservice=new MemberCardService();
        $info=$cardservice->addinfo($data);
        $res=CardModel::addCard($info); 
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'添加成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '新增会员卡失败',
                'solelyCode'=>215001
            ]);
        }
    }

    public function editCard(){
        $data = Request::instance()->param();
        $cardservice=new MemberCardService();
        $checkinfo = $cardservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '会员卡不属于本商户',
                    'solelyCode' => 215003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '会员卡已删除',
                    'solelyCode' => 215002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '会员卡不存在',
                    'solelyCode' => 215000
                ]);
        }
        $updateinfo = $cardservice->formatImg($data);
        $res = CardModel::updateCard($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改会员卡成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改会员卡失败',
                'solelyCode' => 215004
            ]);
        }
    }

    public function delCard()
    {
        $data = Request::instance()->param();
        $cardservice=new MemberCardService();
        $checkinfo = $cardservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '会员卡不属于本商户',
                    'solelyCode' => 215003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '会员卡已删除',
                    'solelyCode' => 215002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '会员卡不存在',
                    'solelyCode' => 215000
                ]);
        }
        $res = CardModel::delCard($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除会员卡成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除会员卡失败',
                'solelyCode' => 215005
            ]);
        }
    }

    public function getList()
    {
        $data=Request::instance()->param();
        $cardservice=new MemberCardService();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info=preAll($data);
        $userinfo=Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id']=$userinfo->thirdapp_id;
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = CardModel::getCardListToPaginate($info);
            $list = $cardservice->formatPaginateList($list);
        }else{
            $list=CardModel::getCardList($info);
            $list = $cardservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取会员卡列表失败',
                'solelyCode' => 215006
            ]);
        }
    }

    public function getInfo()
    {
        $data=Request::instance()->param();
        $userinfo=Cache::get('info'.$data['token']);
        $res=CardModel::getCardInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '会员卡已删除',
                    'solelyCode' => 215002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '会员卡不属于本商户',
                    'solelyCode' => 215003
                ]);
            }
            $cardservice=new MemberCardService();
            $res = $cardservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '会员卡不存在',
                'solelyCode' => 215000
            ]);
        }
    }
}