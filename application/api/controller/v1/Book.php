<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/22
 * Time: 18:13
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\Book as BookModel;
use app\api\service\Book as BookService;
use think\Request as Request;
use think\Cache;

/**
 * 后端预约模块
 */
class Book extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editBook,delBook,getInfo']
    ];

    public function addBook(){
        $data = Request::instance()->param();
        if (!isset($data['start_time'])||!isset($data['end_time'])) {
            throw new TokenException([
                'msg' => '缺少关键参数，未设置start_time或end_time',
                'solelyCode'=>216007
            ]);
        }
        $bookservice = new BookService();
        $info = $bookservice->addinfo($data);
        $res = BookModel::addBook($info); 
        if($res==1){
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

    public function editBook(){
        $data = Request::instance()->param();
        $bookservice = new BookService();
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
        $res = BookModel::updateBook($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改预约信息成功',
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
        $bookservice=new BookService();
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
        $res = BookModel::delBook($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除预约成功',
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
        $bookservice = new BookService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        if (isset($info['map']['start_time'])) {
            $info['map']['start_time'] = array('egt',$info['map']['start_time']);
        }
        if (isset($info['map']['end_time'])) {
            $info['map']['end_time'] = array('elt',$info['map']['end_time']); 
        }
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = BookModel::getBookListToPaginate($info);
            $list = $bookservice->formatPaginateList($list);
        }else{
            $list = BookModel::getBookList($info);
            $list = $bookservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取预约列表失败',
                'solelyCode' => 216006
            ]);
        }
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $info['thirdapp_id'] = $userinfo->thirdapp_id;
        $res = BookModel::getBookInfo($data['id']);
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
            $bookservice = new BookService();
            $res = $bookservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '预约信息不存在',
                'solelyCode' => 216000
            ]);
        }
    }
}