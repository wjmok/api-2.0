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
use app\api\model\FlowMoney as FlowMoneyModel;
use app\api\service\Token as TokenService;
use app\api\service\FlowCommon as FlowService;
use think\Request as Request; 

/**
 * 现金流水
 */
class UserFlowMoney extends CommonController{

	protected $beforeActionList = [
        'checkToken' => ['only' => 'getList']
    ];

    public function getList(){
        $data = Request::instance()->param();
        $flowservice = new FlowService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = TokenService::getUserinfo($data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $info['map']['user_id'] = $userinfo['id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = FlowMoneyModel::getFlowListToPaginate($info);
            $list = $flowservice->formatPaginateList($list);
        }else{
            $list = FlowMoneyModel::getFlowList($info);
            $list = $flowservice->formatList($list);
        }
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