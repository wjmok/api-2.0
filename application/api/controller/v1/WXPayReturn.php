<?php
/**
 * Created by wjm.
 * Author: wjm
 * Date: 2018/7/13
 * Time: 16:41
 */

namespace app\api\controller\v1;

use think\Db;
use think\Controller;

use app\api\model\Common as CommonModel;
use app\api\service\base\Pay as PayService;


use think\Request as Request;
use app\lib\exception\TokenException;

class WXPayReturn extends Controller
{
    //支付回调
    public function receiveNotify(){
        $xmlData = file_get_contents('php://input');
        $data = xml2array($xmlData);
        //开始支付回调逻辑....
        if($data['RESULT_CODE']=='SUCCESS'){
            $orderNo = $data['OUT_TRADE_NO'];
            
            //根据订单号查询订单信息
            
            $modelData = [];
            $modelData['map']['order_no'] = $orderNo;
            $orderinfo =  CommonModel::CommonGet('order',$modelData);
            
            //记录微信支付回调日志
            $modelData = [];
            $modelData['data'] = array(
                'title'=>'微信支付',
                'result'=>$data['RESULT_CODE'],
                'content'=>json_encode($data),
                'user_id'=>$orderinfo['user_id'],
                'thirdapp_id'=>$orderinfo['thirdapp_id'],
                'type'=>2,
            );
            $saveLog =  CommonModel::CommonSave('log',$modelData);
            
            //如果状态更新，阻止微信再次请求
            if($orderinfo['pay_status']==1){
                return true;
            }
                try{
                    $res = PayService::checkIsPayAll($orderNo);
                    Db::commit();
                    return true;
                }catch (Exception $ex){
                    Db::rollback();
                    $res = PayService::returnPay($orderNo);
                    throw $ex;
                }
            }
            
            
        }else{
            //记录微信支付回调日志

            $modelData = [];
            $modelData['data'] = array(
                'title'=>'微信支付',
                'result'=>$data['RESULT_CODE'],
                'content'=>$data['RETURN_MSG'],
                'create_time'=>time(),
                'type'=>2,
            );
            $saveLog =  CommonModel::CommonSave('log',$modelData);

        }
    }

    
}