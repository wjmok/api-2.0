<?php
/**
 * Created by 董博明.
 * Author: 董博明
 * 分销逻辑主类，根据各项目不同，调用各个分销类
 * Date: 2018/3/28
 * Time: 18:32
 */

namespace app\api\service;
use app\api\model\Order as OrderModel;
use app\api\model\ThirdApp as ThirdAppModel;
use app\api\model\User as UserModel;

//引入项目分销逻辑
use app\api\service\DistributionMuyaju as MuyajuService;
use app\api\service\DistributionThemaze as ThemazeService;
use app\api\service\DistributionGuoxingyd as GuoxingydService;

class DistributionMain{
    
    public function OrderDistribution($id)
    {
    	$orderinfo = OrderModel::getOrderInfo($id);
    	$thirdinfo = ThirdAppModel::getThirdUserInfo($orderinfo['thirdapp_id']);
    	//过滤实际未设置分销逻辑的项目
    	if ($thirdinfo['distributionRule']=='[]') {
    		return 1;
    	}
    	//根据项目执行不同的分销逻辑
    	if ($thirdinfo['codeName']=="muyaju") {
            $res = MuyajuService::payReturn($orderinfo,$thirdinfo);
            return $res;
    	}
        if ($thirdinfo['codeName']=="themaze") {
            $res = ThemazeService::payReturn($orderinfo,$thirdinfo);
            return $res;
        }
    }

    public function SubscribeDistribution($userid)
    {
        $userinfo = UserModel::getUserInfo($userid);
        $thirdinfo = ThirdAppModel::getThirdUserInfo($userinfo['thirdapp_id']);
        if ($thirdinfo['codeName']=="guoxingyude") {
            $res = GuoxingydService::subscribe($userinfo,$thirdinfo);
            return $res;
        }
    }

    public function CompleteInfo($userid)
    {
        $userinfo = UserModel::getUserInfo($userid);
        $thirdinfo = ThirdAppModel::getThirdUserInfo($userinfo['thirdapp_id']);
        if ($thirdinfo['codeName']=="guoxingyude") {
            $res = GuoxingydService::completeInfo($userinfo,$thirdinfo);
            return $res;
        }
    }
}