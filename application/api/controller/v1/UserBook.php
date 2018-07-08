<?php
namespace app\api\controller\v1;
use app\api\model\Book as BookModel;
use think\Request as Request;
use think\Controller;
use app\api\controller\CommonController;
use think\Exception;
use app\lib\exception\TokenException;
use app\api\service\Book as BookService;

class UserBook extends CommonController{

    //获取商品列表
    public function getList(){

        $data = Request::instance()->param();
        $bookservice = new BookService;
        if (!isset($data['thirdapp_id'])) {
            throw new TokenException([
                'msg'=>'缺少参数ThirdID',
                'solelyCode' => 200002
            ]);
        }
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info = $bookservice->initSearchTime($info);
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = BookModel::getBookListToPaginate($info);
            $list = $bookservice->formatPaginateList($list);
        }else{
            $list = BookModel::getBookList($info);
            $list = $bookservice->formatList($list);
        }
        return $list;
    }

    //获取指定预约信息
    public function getInfo(){

        $data=Request::instance()->param();
        if (!isset($data['id'])) {
            throw new TokenException([
                'msg' => '缺少参数ID',
                'solelyCode' => 200001
            ]);
        }
        if (!isset($data['thirdapp_id'])) {
            throw new TokenException([
                'msg'=>'缺少参数ThirdID',
                'solelyCode' => 200002
            ]);
        }
        $res=BookModel::getBookInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '预约已删除',
                    'solelyCode' => 216002
                ]);
            }
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '预约不属于本商户',
                    'solelyCode' => 216003
                ]);
            }
            $bookservice=new BookService();
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