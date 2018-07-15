<?php
/**
 * Created by 七月.
 * Author: 七月
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 七月在夏天
 * Date: 2017/2/26
 * Time: 16:02
 */

namespace app\api\service;


use app\api\model\Order as OrderModel;
use app\api\model\ThirdApp as ThirdappModel;
use app\api\model\User as UserModel;
use app\api\model\UserCoupon as UserCouponModel;
use app\api\model\FlowBalance as FlowBalanceModel;
use app\api\model\Common as CommonModel;
use app\api\service\UserOrder as OrderService;
use app\api\service\base\WxpayService as WxpayService;
use app\api\service\base\CommonService as CommonService;
use app\api\validate\CommonValidate as CommonValidate;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use app\lib\exception\ErrorException;
use app\lib\exception\SuccessException;


use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;
use think\Exception;
use think\Loader;
use think\Log;


Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');


class Pay
{
    private $orderNo;
    private $orderID;
    private $token;

    function __construct()
    {
        
    };


    private function pay($data){

        $this->token = $data['token'];

        (new CommonValidate())->goCheck('six',$data);
        checkTokenAndScope($data,0);

        $modelData = [];
        $modelData['searchItem']['id'] = Cache::get($data['token'])['id'];
        $modelData = preGet($modelData);
        $userInfo =  CommonModel::CommonGet('user',$modelData);

        $data = preGet($data);
        $orderInfo =  CommonModel::CommonGet('order',$data);
        $orderInfo = $orderInfo[0];
        

        $this->checkOrderValid($orderInfo);
        Db::startTrans();
        try{
            if(isset($pay['balance'])){
                $this->balancePay($userInfo,$orderinfo);
            };

            if(isset($pay['score'])){
                $this->scorePay($orderinfo);
            };

            if(isset($pay['coupon'])){
                $this->couponPay($userInfo,$orderinfo);
            };

            if(isset($pay['wxPay'])){
                 return $this->WxpayService($orderinfo);
            };
            Db::commit();
        }catch (Exception $ex){
            Db::rollback();
            throw $ex;
        };

        

        if(!isset($pay['wxPay'])){
            $pass = $this->checkIsPayAll();
            if($pass){
                throw new SuccessMessage([
                    'msg' => '支付成功',
                ]);
            }else{
                $this->returnPay();
                throw new ErrorMessage([
                    'msg' => '支付失败',
                ]);

            }
        };


    }


    private function returnPay($orderNo){
        
        
        $modelData = [];
        $modelData['searchItem']['order_no'] = $orderNo;
        $modelData['modelName'] = 'flow_log';
        $modelData['token'] = $this->token;
        $res =  CommonService::get($modelData);

        if(count($res)>0){
            foreach ($res as $key => $value) {
                if($res[$key]['type']==2){
                    $this->returnBalance($value);
                }else if($res[$key]['type']==3){
                    $this->returnScore($value);
                }else if($res[$key]['type']==4){
                    $this->returnCoupon($value);
                }
            }
        };

    }

    

    private function balancePay($userInfo,$orderinfo)
    {
        if($userInfo['info']['balance']<$orderinfo['pay']['score']){
            throw new ErrorMessage([
                'msg' => '余额不足',
            ]);
        };

        $modelData = [];
        $modelData = array(
            'type' => 2,
            'count'=>$orderinfo['pay']['score'],
            'order_no'=>$orderinfo['order_no'],
            'trade_info'=>'余额支付',
            'thirdapp_id'=>$userInfo['thirdapp_id'],
            'user_no'=>$userInfo['user_no'],
        );
        $res =  CommonModel::CommonSave('flow_log',$modelData);
        if(!$res>0){
            
            throw new ErrorMessage([
                'msg'=>'余额支付失败'
            ]);
        };

        $modelData = [];
        $modelData['map']['user_no'] = $userInfo['user_no'];
        $modelData['data']['balance'] = $userInfo['balance'] - $orderinfo['pay']['score'];

        $res =  CommonModel::CommonSave('user',$modelData['data'],$modelData['map']);

        if(!$res>0){
            throw new ErrorMessage([
                'msg'=>'更新用户信息失败'
            ]);
        };

    }

    private function returnBalance($FlowLog)
    {
        $modelData = [];
        $modelData['searchItem']['id'] = Cache::get($data['token'])['id'];
        $modelData = preGet($modelData);
        $userInfo =  CommonModel::CommonGet('user',$modelData);

        
        $modelData = [];
        $modelData['searchItem']['id'] = $FlowLog['id'];
        $modelData = preSearch($modelData);
        $res =  CommonModel::CommonDelete('order_item',$modelData);

        if($res>0){
            $modelData = [];
            $modelData['searchItem']['user_no'] = Cache::get($data['token'])['user_no'];
            $modelData['searchItem']['balance'] = $userInfo['balance'];
            $modelData['data']['balance'] = $userInfo['balance'] + $FlowLog['count'];
            $modelData = preSearch($modelData);
            $res = CommonModel::CommonSave('user_info',$modelData['data'],$modelData['map']);

            if(!$res>0){
                throw new ErrorMessage([
                    'msg' => '余额回滚失败',
                ]);
            };

        }else{
            throw new ErrorMessage([
                'msg' => '余额支付记录删除失败',
            ]);
        }
        

    }

    private function couponPay($userInfo,$orderinfo)
    {


        $modelData = [];
        $modelData['searchItem']['order_no'] = $orderinfo['pay']['coupon']['coupon_no'];
        $modelData['modelName'] = 'order';
        $modelData['token'] = $this->token;
        
        $couponInfo =  CommonService::get($modelData,true);
        $couponInfo = $couponInfo[0];

        $modelData = [];
        $modelData = array(
            'type' => $couponInfo['product_type'],
            'count'=>$orderinfo['pay']['coupon']['fee'],
            'order_no'=>$orderinfo['order_no'],
            'trade_info'=>'优惠券抵减',
            'thirdapp_id'=>$userInfo['thirdapp_id'],
            'user_no'=>$userInfo['user_no'],
            'relation_id'=>$couponInfo['order_no'],
        );

        $res =  CommonModel::CommonSave('flow_log',$modelData);
        if(!$res>0){
            throw new ErrorMessage([
                'msg'=>'核销优惠卷失败'
            ]);
        };

        $modelData = [];
        $modelData['searchItem']['order_no'] = $couponInfo['order_no'];
        $modelData['token'] = $this->token;
        $modelData['modelName'] = 'order';

        $res =  CommonService::delete($modelData,true);

        if(!$res>0){
            throw new ErrorMessage([
                'msg'=>'更新用户信息失败'
            ]);
        };


    }

    private function returnCoupon($FlowLog)
    {


        $modelData = [];
        $modelData['searchItem']['id'] = $FlowLog['id'];
        $modelData = preSearch($modelData);
        $res =  CommonModel::CommonDelete('order_item',$modelData);
        


        if($res>0){

            $modelData = [];
            $modelData['searchItem']['order_no'] = $FlowLog['relation_id'];
            $modelData['data']['status'] = 1;
            $modelData['modelName'] = 'order';
            $modelData['token'] = $this->token;
            $res =  CommonService::update($modelData);

            if(!$res>0){
                throw new ErrorMessage([
                    'msg' => '优惠券回滚失败',
                ]);
            };

        }else{
            throw new ErrorMessage([
                'msg' => '优惠券抵扣记录删除失败',
            ]);
        }
        

    }

    private function scorePay()
    {
        if($userInfo['info']['score']<$orderinfo['pay']['score']/$userInfo['info']['score_ratio']){
            throw new ErrorMessage([
                'msg' => '积分不足',
            ]);
        };

        $modelData = [];
        $modelData = array(
            'type' => 3,
            'count'=>$orderinfo['pay']['score'],
            'order_no'=>$orderinfo['order_no'],
            'trade_info'=>'积分支付,积分兑付比率为:'.$userInfo['info']['score_ratio'],
            'thirdapp_id'=>$userInfo['thirdapp_id'],
            'user_no'=>$userInfo['user_no'],
        );
        $res =  CommonModel::CommonSave('flow_log',$modelData);
        if(!$res>0){
            
            throw new ErrorMessage([
                'msg'=>'积分支付失败'
            ]);

        };

        $modelData = [];
        $modelData['map']['user_no'] = $userInfo['user_no'];
        $modelData['data']['score'] = $userInfo['score'] - $orderinfo['pay']['score'];

        $res =  CommonModel::CommonSave('user',$modelData['data'],$modelData['map']);

        if(!$res>0){
            throw new ErrorMessage([
                'msg'=>'更新用户信息失败'
            ]);
        };

    }

    private function returnScore($FlowLog)
    {
        $modelData = [];
        $modelData['searchItem']['id'] = Cache::get($data['token'])['id'];
        $modelData = preGet($modelData);
        $userInfo =  CommonModel::CommonGet('user',$modelData);

        
        $modelData = [];
        $modelData['searchItem']['id'] = $FlowLog['id'];
        $modelData = preSearch($modelData);
        $res =  CommonModel::CommonDelete('order_item',$modelData);

        if($res>0){
            $modelData = [];
            $modelData['searchItem']['user_no'] = Cache::get($data['token'])['user_no'];
            $modelData['searchItem']['score'] = $userInfo['score'];
            $modelData['data']['score'] = $userInfo['score'] + $FlowLog['count'];
            $modelData = preSearch($modelData);
            $res = CommonModel::CommonSave('user_info',$modelData['data'],$modelData['map']);

            if(!$res>0){
                throw new ErrorMessage([
                    'msg' => '积分回滚失败',
                ]);
            };

        }else{
            throw new ErrorMessage([
                'msg' => '积分支付记录删除失败',
            ]);
        }
        

    }



    private function checkOrderValid($data)
    {
        
        if (!$data||count($data)>0)
        {
            throw new ErrorMessage([
                'msg' => '订单不存在',
            ]);
        }
        
        if($data[0]['pay_status'] != 1){
            throw new OrderException([
                'msg' => '订单已支付',
                 'solelyCode' => 209017,
            ]);
        }

    }

    private function checkIsPayAll($orderNo)
    {
        $modelData = [];
        $modelData['map']['order_no'] = $orderNo;
        $orderInfo =  CommonModel::CommonGet('order',$modelData);
        $orderInfo = $orderInfo[0];

        $pass = true;

        if(isset($orderInfo['pay']['balance'])){
            $this->WxpayService($orderinfo);
            $modelData = [];
            $modelData['searchItem']['order_no'] = $orderInfo['order_no'];
            $modelData['searchItem']['type'] = 2;
            $modelData['searchItem']['count'] = $orderInfo['pay']['balance'];
            $orderInfo =  CommonModel::CommonGet('flow_log',$modelData);
            if(count($orderInfo)==0){
                $pass = false;
            }

        };


        if($pass){
            $modelData = [];
            $modelData['map']['id'] = $orderInfo['id'];
            $modelData['data']['pay_status'] = 1;
            $modelData['data']['update_time'] = time();
            $res =  CommonModel::CommonSave('order',$modelData['data'],$modelData['map']);
        }
        return $pass;

    }


}