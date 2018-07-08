<?php
/**
 * Created by 董博明.
 * Author: 董博明
 * Date: 2018/3/28
 * Time: 14:15
 */

namespace app\api\controller\v1;

use think\Db;
use think\Cache;
use think\Controller;
use app\api\controller\BaseController;
use app\api\model\Order as OrderModel;
use app\api\service\Token as TokenService;
use app\api\service\MerchantPay as PayService;
use think\Request as Request;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

class MerchantPay extends BaseController
{
    protected $beforeActionList = [
        'checkID' => ['only' => 'refund']
    ];

    //退款
    public function refund()
    {
        $data = Request::instance()->param();
        //获取订单信息
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        //获取用户信息
        $userinfo = Cache::get('info'.$data['token']);
        if ($userinfo['thirdapp_id']!=$orderinfo['thirdapp_id']) {
            throw new TokenException([
                'msg' => '该订单不属于本商户，不能退款',
                'solelyCode' => 209016
            ]);
        }
        $pay = new PayService($data['id'],$userinfo['id']);
        $checkorder = $pay->checkOrderValid();
        switch ($checkorder) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '订单不存在，请检查ID',
                    'solelyCode' => 209000
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '订单已删除',
                    'solelyCode' => 209006
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '订单未支付，不能退款',
                    'solelyCode' => 209018
                ]);     
        }
        $refundorder = $pay->refund();
        return $refundorder;
        switch ($refundorder['status']) {
            case 1:
                # code...
            case -1:
                throw new ThirdappException([
                    'msg' => '项目不存在或异常',
                    'solelyCode' => 201000
                ]);
            case -2:
                throw new ThirdappException([
                    'msg' => '该商户信息被删除',
                    'solelyCode' => 201003
                ]);
        }

    }

    //xml格式转化成数组
    private function xml2array($xml){
        $p=xml_parser_create();
        xml_parse_into_struct($p, $xml, $vals, $index);
        xml_parser_free($p);
        $data = "";
        foreach($index as $key=>$value){
            if($key=='xml'||$key =='XML') continue;
            $tag=$vals[$value[0]]['tag'];
            $value=$vals[$value[0]]['value'];
            $data[$tag]=$value;
        }
        return $data;
    }
}