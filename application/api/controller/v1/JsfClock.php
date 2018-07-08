<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/6/20
 * Time: 22:46
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\JsfClock as ClockModel;
use app\api\service\JsfClock as ClockService;
use think\Request as Request;
use think\Cache;

/**
 * 健身房项目挑战赛模块
 */
class JsfClock extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'delClock,getInfo']
    ];

    public function delClock()
    {
        $data = Request::instance()->param();
        $clcokservice = new ClockService();
        $checkinfo = $clcokservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '打卡记录不属于本商户',
                    'solelyCode' => 216003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '打卡记录已删除',
                    'solelyCode' => 216002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '打卡记录不存在',
                    'solelyCode' => 216000
                ]);
        }
        $res = ClockModel::delClock($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除打卡记录成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除打卡记录失败',
                'solelyCode' => 216005
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $clcokservice = new ClockService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = ClockModel::getClockListToPaginate($info);
            $list = $clcokservice->formatPaginateList($list);
        }else{
            $list = ClockModel::getClockList($info);
            $list = $clcokservice->formatList($list);
        }
        return $list;
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $info['thirdapp_id'] = $userinfo->thirdapp_id;
        $res = ClockModel::getClockInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '打卡记录已删除',
                    'solelyCode' => 216002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '打卡记录不属于本商户',
                    'solelyCode' => 216003
                ]);
            }
            $clcokservice = new ClockService();
            $res = $clcokservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '打卡记录不存在',
                'solelyCode' => 216000
            ]);
        }
    }
}