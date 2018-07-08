<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/5/9
 * Time: 18:53
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\XgdBook as XgdBookModel;
use app\api\model\XgdBookOrder as XgdBookOrderModel;
use app\api\model\XgdBookmenu as XgdBookmenuModel;
use app\api\model\XgdUnbookOrder as XgdUnbookOrderModel;
use app\api\model\User as UserModel;
use app\api\service\XgdBook as XgdBookService;
use app\api\service\QRcode as QRcodeService;
use think\Request as Request;
use think\Cache;

/**
 * 后端西工大预约
 */
class XgdBook extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editBook,delBook,getInfo']
    ];

    public function addBook()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        if (!isset($data['start_time'])) {
            throw new TokenException([
                'msg' => '缺少关键参数start_time',
                'solelyCode'=>200012
            ]);
        }
        $bookservice = new XgdBookService();
        $info = $bookservice->addinfo($data);
        $res = XgdBookModel::addBook($info); 
        if($res!=0){
            //获取活动二维码
            $getcode = $this->getCode($userinfo['thirdapp_id'],$res);
            throw new SuccessMessage([
                'msg'=>'添加成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '新增预约失败',
                'solelyCode'=>216001
            ]);
        }
    }

    public function editBook()
    {
        $data = Request::instance()->param();
        $bookservice = new XgdBookService();
        $checkinfo = $bookservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '预约不属于本商户',
                    'solelyCode' => 216003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '预约已删除',
                    'solelyCode' => 216002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '预约信息不存在',
                    'solelyCode' => 216000
                ]);
        }
        $updateinfo = $bookservice->formatImg($data);
        $updateinfo['update_time'] = time();
        $res = XgdBookModel::updateBook($data['id'],$updateinfo);
        if($res==1){
            //如果修改了活动的时间参数，同步修改报名信息的时间
            if (isset($data['start_time'])||isset($data['end_time'])) {
                if (isset($data['start_time'])) {
                    $uporder['start_time'] = $data['start_time'];
                }
                if (isset($data['end_time'])) {
                    $uporder['end_time'] = $data['end_time'];
                }
                $ordermap['map']['book_id'] = $data['id'];
                $orderlist = XgdBookOrderModel::getOrderList($ordermap);
                if ($orderlist) {
                    foreach ($orderlist as $key => $value) {
                        $res = XgdBookOrderModel::updateOrder($value['id'],$uporder);   
                    }
                }
            }
            throw new SuccessMessage([
                'msg'=>'修改成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改预约信息失败',
                'solelyCode' => 216004
            ]);
        }
    }

    public function delBook()
    {
        $data = Request::instance()->param();
        $bookservice = new XgdBookService();
        $checkinfo = $bookservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '预约不属于本商户',
                    'solelyCode' => 216003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '预约已删除',
                    'solelyCode' => 216002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '预约信息不存在',
                    'solelyCode' => 216000
                ]);
        }
        $res = XgdBookModel::delBook($data['id']);
        if($res==1){
            //关联删除订单
            $ordermap['map']['book_id'] = $data['id'];
            $orderlist = XgdBookOrderModel::getOrderList($ordermap);
            if ($orderlist) {
                foreach ($orderlist as $key => $value) {
                    $res = XgdBookOrderModel::delOrder($value['id']); 
                }
            }
            throw new SuccessMessage([
                'msg'=>'删除成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除预约失败',
                'solelyCode' => 216005
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $bookservice = new XgdBookService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo->thirdapp_id;
        if (isset($info['map']['gtime'])) {
            $info['map']['end_time'] = array('egt',$info['map']['gtime']);
            unset($info['map']['gtime']);
        }
        if (isset($info['map']['ltime'])) {
            $info['map']['end_time'] = array('elt',$info['map']['ltime']);
            unset($info['map']['ltime']);
        }
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = XgdBookModel::getBookListToPaginate($info);
            $list = $bookservice->formatPaginateList($list);
        }else{
            $list=XgdBookModel::getBookList($info);
            $list = $bookservice->formatList($list);
        }
        return $list;
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $res = XgdBookModel::getBookInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '预约已删除',
                    'solelyCode' => 216002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '预约不属于本商户',
                    'solelyCode' => 216003
                ]);
            }
            $bookservice = new XgdBookService();
            $res = $bookservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '预约信息不存在',
                'solelyCode' => 216000
            ]);
        }
    }

    public function getCode($thirdapp_id,$id)
    {
        $qrservice = new QRcodeService();
        $url = $qrservice->getMiniCode($thirdapp_id,$id);
        //更新book信息
        $qrcodearray = array(
            "name"=>"qrcode",
            "url"=>$url,
        );
        $upbook['QRcode'] = json_encode($qrcodearray);
        $updatebook = XgdBookModel::updateBook($id,$upbook);
        if ($updatebook==1) {
            return true;
        }else{
            return false;
        }
    }

    //Excel导出活动统计信息
    public function getExcel()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $map['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $booklist = XgdBookModel::getBookList($map);

        $fileName = "活动统计数据";
        $xlsName = "ActivitiesData";
        $xlsCell = array(  
            array('id','ID'),
            array('name','活动名称'),
            array('start_time','活动时间'),
            array('duration','活动时长'),
            array('campus_id','活动校区'),
            array('bookNum','坐票人数'),
            array('unbookNum','站票人数'),
            array('unarriveNum','预约未参加人数'),
        );

        $xlsData = array();
        foreach($booklist as $k=>$v){
            $xlsData[$k]['id'] = $v['id'];
            $xlsData[$k]['name'] = $v['name'];
            $xlsData[$k]['start_time'] = date('y-m-d h:i:s',$v['start_time']);
            $xlsData[$k]['duration'] = $v['duration'];
            $campusinfo = XgdBookmenuModel::getMenuInfo($v['campus_id']);
            $xlsData[$k]['campus_id'] = $campusinfo['name'];
            $xlsData[$k]['bookNum'] = $v['bookNum'];
            $unbookmap['map']['book_id'] = $v['id'];
            $unbooklist = XgdUnbookOrderModel::getOrderList($unbookmap);
            $xlsData[$k]['unbookNum'] = count($unbooklist);
            $bookmap['map']['book_id'] = $v['id'];
            $bookmap['map']['book_status'] =  2;
            $joinlist = XgdBookOrderModel::getOrderList($bookmap);
            $xlsData[$k]['unarriveNum'] = $v['bookNum']-count($joinlist);
        }
        // return $xlsData;
        $this->exportExcel($xlsName,$xlsCell,$xlsData,$fileName);
    }

    //Excel导出学生信息
    public function getStudentExcel()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $map['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $userlist = UserModel::getUserList($map);

        $fileName = "学生信息数据";
        $xlsName = "UserData";
        $xlsCell = array(  
            array('id','ID'),
            array('username','学生姓名'),
            array('create_time','注册时间'),
            array('phone','手机号'),
            array('passage1','学号'),
            array('passage2','学院'),
        );

        $xlsData = array();
        foreach($userlist as $k=>$v){
            $xlsData[$k]['id'] = $v['id'];
            $xlsData[$k]['username'] = $v['username'];
            $xlsData[$k]['create_time'] = $v['create_time'];
            $xlsData[$k]['phone'] = $v['phone'];
            $xlsData[$k]['passage1'] = $v['passage1'];
            $xlsData[$k]['passage2'] = $v['passage2'];
        }
        // return $xlsData;
        $this->exportExcel($xlsName,$xlsCell,$xlsData,$fileName);
    }
}