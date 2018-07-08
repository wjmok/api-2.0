<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/22
 * Time: 20:58
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\MemberCard as CardModel;
use app\api\service\MemberCard as MemberCardService;
use think\Request as Request;

/**
 * 前端会员卡模块
 */
class UserMemberCard extends CommonController{

    public function getList()
    {
        $data=Request::instance()->param();
        if (!isset($data['thirdapp_id'])) {
            throw new TokenException([
                'msg'=>'缺少参数ThirdID',
                'solelyCode' => 200002
            ]);
        }
        $cardservice=new MemberCardService();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info=preAll($data);
        $info['map']['thirdapp_id']=$data['thirdapp_id'];
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
        if (!isset($data['id'])) {
            throw new TokenException([
                'msg' => '缺少参数ID',
                'solelyCode' => 200001
            ]);
        }
        if (!isset($data['thirdapp_id'])) {
            throw new TokenException([
                'msg'=>'缺少参数ThirdID',
                'solelyCode' => 200002
            ]);
        }
        $res=CardModel::getCardInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '会员卡已删除',
                    'solelyCode' => 215002
                ]);
            }
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
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