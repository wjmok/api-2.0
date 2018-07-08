<?php
namespace app\api\service;
use app\api\model\XgdBookOrder as XgdOrderModel;
use app\api\model\XgdBook as XgdBookModel;
use app\api\model\XgdBookmenu as XgdMenuModel;
use app\api\model\User as UserModel;
use app\api\model\FlowScore as FlowScoreModel;
use app\api\service\Token as TokenService;

class XgdUserBookOrder{

    public function addinfo($data)
    {
        $data['status'] = 1;
        $data['book_time'] = time();
        $data['scan_time'] = time();
        $userinfo = TokenService::getUserinfo($data['token']);
        //获取thirdapp_id
        $data['user_id'] = $userinfo['id'];
        $data['thirdapp_id']=$userinfo['thirdapp_id'];
        $bookinfo = XgdBookModel::getBookInfo($data['book_id']);
        $data['start_time'] = $bookinfo['start_time'];
        $data['end_time'] = $bookinfo['end_time'];
        $data['menu_id'] = $bookinfo['menu_id'];
        $data['campus_id'] = $bookinfo['campus_id'];
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo = TokenService::getUserinfo($token);
        //获取报名信息
        $orderinfo = XgdOrderModel::getOrderInfo($id);
        if ($orderinfo) {
            if ($userinfo['thirdapp_id']!=$orderinfo['thirdapp_id']) {
                return -1;
            }
            if ($orderinfo['status']==-1) {
                return -2;
            }
            if ($userinfo['id']!=$orderinfo['user_id']) {
                return -4;
            }
        }else{
            return -3;
        }
        return 1;
    }

    //格式化分页信息
    public function formatPaginateList($list)
    {
        $list = $list->toArray();
        foreach($list['data'] as $key=>$value){
            if ($list['data'][$key]['menu']) {
                $list['data'][$key]['menu']['mainImg'] = json_decode($value['menu']['mainImg'],true);
                $list['data'][$key]['menu']['bannerImg'] = json_decode($value['menu']['bannerImg'],true);
            }
            if ($list['data'][$key]['campus']) {
                $list['data'][$key]['campus']['mainImg'] = json_decode($value['campus']['mainImg'],true);
                $list['data'][$key]['campus']['bannerImg'] = json_decode($value['campus']['bannerImg'],true);
            }
            if ($list['data'][$key]['user']) {
                $list['data'][$key]['user']['headimgurl'] = json_decode($value['user']['headimgurl'],true);
            }
            if ($list['data'][$key]['book']) {
                $list['data'][$key]['book']['mainImg'] = json_decode($value['book']['mainImg'],true);
                $list['data'][$key]['book']['bannerImg'] = json_decode($value['book']['bannerImg'],true);
                $list['data'][$key]['book']['QRcode'] = json_decode($value['book']['QRcode'],true);
            }
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list = $list->toArray();
        foreach($list as $key=>$value){
            if ($list[$key]['menu']) {
                $list[$key]['menu']['mainImg'] = json_decode($value['menu']['mainImg'],true);
                $list[$key]['menu']['bannerImg'] = json_decode($value['menu']['bannerImg'],true);
            }
            if ($list[$key]['campus']) {
                $list[$key]['campus']['mainImg'] = json_decode($value['campus']['mainImg'],true);
                $list[$key]['campus']['bannerImg'] = json_decode($value['campus']['bannerImg'],true);
            }
            if ($list[$key]['user']) {
                $list[$key]['user']['headimgurl'] = json_decode($value['user']['headimgurl'],true);
            }
            if ($list[$key]['book']) {
                $list[$key]['book']['mainImg'] = json_decode($value['book']['mainImg'],true);
                $list[$key]['book']['bannerImg'] = json_decode($value['book']['bannerImg'],true);
                $list[$key]['book']['QRcode'] = json_decode($value['book']['QRcode'],true);
            }
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        if ($info['menu']) {
            $info['menu']['mainImg'] = json_decode($info['menu']['mainImg'],true);
            $info['menu']['bannerImg'] = json_decode($info['menu']['bannerImg'],true);
        }
        if ($info['campus']) {
            $info['campus']['mainImg'] = json_decode($info['campus']['mainImg'],true);
            $info['campus']['bannerImg'] = json_decode($info['campus']['bannerImg'],true);
        }
        if ($info['user']) {
            $info['user']['headimgurl'] = json_decode($info['user']['headimgurl'],true);
        }
        if ($info['book']) {
            $info['book']['mainImg'] = json_decode($info['book']['mainImg'],true);
            $info['book']['bannerImg'] = json_decode($info['book']['bannerImg'],true);
            $info['book']['QRcode'] = json_decode($info['book']['QRcode'],true);
        }
        return $info;
    }

    //获取活动统计信息
    public function getStatistics($data)
    {
        $userinfo = TokenService::getUserinfo($data['token']);
        $menumap['map']['parentid'] = 1;
        $actlist = XgdMenuModel::getMenuList($menumap);
        foreach ($actlist as $key => $value) {
            $bookmap['map']['menu_id'] = $value['id'];
            $booklist = XgdBookModel::getBookList($bookmap);
            $total_count = count($booklist);
            $actlist[$key]['total_count'] = $total_count;
            $ordermap['map']['user_id'] = $userinfo['id'];
            $ordermap['map']['menu_id'] = $value['id'];
            $userorder = XgdOrderModel::getOrderList($ordermap);
            $useradd = 0;
            $userjoin = 0;
            $usercancel = 0;
            foreach ($userorder as $k => $v) {
                switch ($v['book_status']) {
                    case 1:
                        $useradd += 1;
                        break;
                    case 2:
                        $userjoin += 1;
                        break;
                    case 3:
                        $usercancel += 1;
                        break;
                }
            }
            $actlist[$key]['useradd'] = $useradd;
            $actlist[$key]['usercancel'] = $usercancel;
            $actlist[$key]['userjoin'] = $userjoin;
            // if ($useradd==0) {
            //     $actlist[$key]['useraddretio'] = 0;
            // }else{
            //     $actlist[$key]['useraddretio'] = (int)($useradd*100/$total_count);
            // }
            // if ($userjoin==0) {
            //     $actlist[$key]['usercancelretio'] = 0;
            // }else{
            //     $actlist[$key]['usercancelretio'] = (int)($usercancel*100/$total_count);
            // }
            // if ($usercancel==0) {
            //     $actlist[$key]['userjoinretio'] = 0;
            // }else{
            //     $actlist[$key]['userjoinretio'] = (int)($userjoin*100/$total_count);
            // }
        }
        return $actlist;
    }

    //计算预约状态并记录流水
    public function checkOrder($token)
    {
        $userinfo = TokenService::getUserinfo($token);
        $map['map']['user_id'] = $userinfo['id'];
        $map['map']['is_deal'] = "false";
        $map['map']['end_time'] = array('lt',time());
        $orderlist = XgdOrderModel::getOrderList($map);
        foreach ($orderlist as $key => $value) {
            if ($value['book_status']==1) {
                if ($userinfo['user_score']>=0) {
                    $up['user_score'] = $userinfo['user_score']-40;
                    $upuser = UserModel::updateUserinfo($userinfo['id'],$up);
                    //记录流水
                    $flow = array(
                        'thirdapp_id'=>$userinfo['thirdapp_id'],
                        'user_id'    =>$userinfo['id'],
                        'scoreAmount'=>40,
                        'order_type' =>'punish',
                        'trade_type' =>2,
                        'trade_info' =>'未参与ID为'.$value['book_id'].'活动',
                        'create_time'=>time(),
                    );
                    $addflow = FlowScoreModel::addFlow($flow);
                }
            }
            $upinfo['is_deal'] = "true";
            $uporder = XgdOrderModel::updateOrder($value['id'],$upinfo);
        }
    }

    //检测是否可以报名
    public function checkValid($token)
    {
        $userinfo = TokenService::getUserinfo($token);
        if ($userinfo['user_score']<=0) {
            $map['user_id'] = $userinfo['id'];
            $map['book_status'] = 1;
            $map['is_deal'] = "true";
            $recentorder = XgdOrderModel::getRecentBadOrder($map);
            //七天惩罚不得报名86400*7=604800
            if ($recentorder['end_time']+604800>time()) {
                return false;
            }else{
                //还原信誉值
                $up['user_score'] = 100;
                $upuser = UserModel::updateUserinfo($userinfo['id'],$up);
                //记录流水
                $flow = array(
                    'thirdapp_id'=>$userinfo['thirdapp_id'],
                    'user_id'    =>$userinfo['id'],
                    'scoreAmount'=>100,
                    'order_type' =>'reduction',
                    'trade_type' =>1,
                    'trade_info' =>'信誉值还原',
                    'create_time'=>time(),
                );
                $addflow = FlowScoreModel::addFlow($flow);
                return true; 
            }
        }else{
            return true; 
        }
    }
}