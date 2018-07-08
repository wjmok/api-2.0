<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/4/5
 * Time: 0:00
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\CommonController;
use app\api\model\FlowScore as FlowScoreModel;
use app\api\model\ThirdApp as ThirdAppModel;
use app\api\service\Token as TokenService;
use app\api\service\FlowCommon as FlowService;
use app\api\service\XgdUserBookOrder as XgdOrderService;
use think\Request as Request; 

/**
 * 积分流水
 */
class UserFlowScore extends CommonController{

	protected $beforeActionList = [
        'checkToken' => ['only' => 'getList']
    ];

    public function getList()
    {
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        $thirdinfo = ThirdAppModel::getThirdUserInfo($userinfo['thirdapp_id']);
        if ($thirdinfo['codeName']=="xgd") {
            //获取信誉记录时计算历史订单——西工大项目
            $xgdservice = new XgdOrderService();
            $docheck = $xgdservice->checkOrder($data['token']);
            $checkValid = $xgdservice->checkValid($data['token']);
        }
        $flowservice = new FlowService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $info['map']['user_id'] = $userinfo['id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = FlowScoreModel::getFlowListToPaginate($info);
            $list = $flowservice->formatPaginateList($list);
        }else{
            $list = FlowScoreModel::getFlowList($info);
            $list = $flowservice->formatList($list);
        }
        //拼接总分值
        $list['user_score'] = $userinfo['user_score'];
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取流水列表失败',
                'solelyCode' => 226001
            ]);
        }
    }
}