<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/5/20
 * Time: 18:14
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\model\Wxlog as WxlogModel;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\service\ProductModel as ProductModelService;

/**
 * 计时器
 */
class Timer extends Controller{

    //按分钟记录的及时器，暂定5分钟执行一次
    public function timerByMins(){

        //检测团购订单并结算
        $productmodelservice = new ProductModelService();
        $dogroup = $productmodelservice->settlementGroup();
        //测试计时器
        // $log = array(
        // 	'title'=>'计时器测试',
        // 	'type'=>'system',
        // 	'content'=>'test',
        // 	'create_time'=>time(),
        // 	'user_id'=>0,
        // 	'thirdapp_id'=>0,
        // );
        // $addlog = WxlogModel::addLog($log);
    }
}