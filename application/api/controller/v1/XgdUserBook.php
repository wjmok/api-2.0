<?php
namespace app\api\controller\v1;
use think\Request as Request;
use app\api\service\XgdBook as XgdBookService;
use app\api\model\XgdBook as XgdBookModel;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

class XgdUserBook extends CommonController
{
    protected $beforeActionList = [
        'checkThirdID' => ['only' => 'getList,getInfo'],
        'checkID'=>['only' => 'getInfo']
    ];

    //获取指定预约信息
    public function getInfo(){
        $data=Request::instance()->param();
        $res = XgdBookModel::getBookInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '菜单已被删除',
                    'solelyCode' => 203002
                ]);
            }
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '菜单不属于该商户',
                    'solelyCode' => 203001
                ]);
            }
            $bookservice = new XgdBookService();
            $res = $bookservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '菜单信息不存在',
                'solelyCode' => 203007
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
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        if (isset($info['map']['gtime'])) {
            $info['map']['end_time'] = array('egt',$info['map']['gtime']);
            $info['sort'] = "asc";
            unset($info['map']['gtime']);
        }
        if (isset($info['map']['ltime'])) {
            $info['map']['end_time'] = array('elt',$info['map']['ltime']);
            $info['sort'] = "desc";
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
}