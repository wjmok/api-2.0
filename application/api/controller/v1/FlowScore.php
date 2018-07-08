<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/5/16
 * Time: 22:14
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\api\model\FlowScore as FlowScoreModel;
use app\api\model\User as UserModel;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use think\Request as Request; 
use think\Cache;

/**
 * 积分流水
 */
class FlowScore extends BaseController{

    public function addFlow()
    {
        $data = Request::instance()->param();
        $admininfo = Cache::get('info'.$data['token']);
        if (!isset($data['user_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数user_id',
                'solelyCode' => 226002
            ]);
        }
        $userinfo = UserModel::getUserInfo($data['user_id']);
        if (!isset($data['score'])) {
            throw new TokenException([
                'msg' => '缺少关键参数score',
                'solelyCode' => 226002
            ]);
        }
        if (!isset($data['trade_type'])) {
            throw new TokenException([
                'msg' => '缺少关键交易类型trade_type',
                'solelyCode' => 226002
            ]);
        }else{
            if ($data['trade_type']!=1&&$data['trade_type']!=2) {
                throw new TokenException([
                    'msg' => 'trade_type参数错误',
                    'solelyCode' => 226002
                ]);
            }
        }
        //更新用户信息
        if ($data['trade_type']==1) {
            $update['user_score'] = $userinfo['user_score']+(int)$data['score'];
        }elseif ($data['trade_type']==2) {
            $update['user_score'] = $userinfo['user_score']-(int)$data['score'];
            if ($update['user_score']<0) {
                throw new TokenException([
                    'msg' => '用户积分不足，扣除积分失败',
                    'solelyCode' => 226004
                ]);
            }
        }
        $upuser = UserModel::updateUserinfo($userinfo['id'],$update);
        //记录流水
        $data['create_time'] = time();
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        $data['scoreAmount'] = $data['score'];
        $data['order_type'] = "system";
        $add = FlowScoreModel::addFlow($data);
        if ($add==1) {
            throw new SuccessMessage([
                'msg' => '记录流水成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '记录流水失败',
                'solelyCode' => 226005
            ]);
        }
    }

    public function getList(){
        $data = Request::instance()->param();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['thirdapp_id'] = 1;
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = FlowScoreModel::getFlowListToPaginate($info);
        }else{
            $list = FlowScoreModel::getFlowList($info);
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