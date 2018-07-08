<?php
namespace app\api\controller\v1;
use app\api\controller\BaseController;
use app\api\model\Remark as RemarkModel;
use think\Request as Request;
use think\Controller;
use app\api\service\Remark as RemarkService;
use think\Exception;
use app\api\validate\RemarkUptValidate;
use app\lib\exception\RemarkException;
use app\lib\exception\SuccessMessage;
use think\Cache;

class Remark extends BaseController{

    //获取指定文章/商品的评论
    public function getRemarkList(){
        $data=Request::instance()->param();
        if (!isset($data['searchItem']['art_id'])&&!isset($data['searchItem']['product_id'])&&!isset($data['searchItem']['book_id'])) {
            throw new RemarkException([
                'msg' => '缺少关键参数,没有评论对象的ID信息',
                'solelyCode' => 208005
            ]);
        }
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        //获取补充thridapp信息
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = RemarkModel::getRelationRemark($info);
        }else{
            $list = RemarkModel::getRemarkList($info);
        }
        return $list;
    }

    //删除评论
    public function delRemark()
    {
        $data=Request::instance()->param();
        $remarkservice = new RemarkService();
        $info = $remarkservice->updateinfo($data);
        switch ($info['status']) {
            case 1:
                break;
            case -1:
                throw new RemarkException([
                    'msg' => '缺少关键参数ID',
                    'solelyCode' => 208005
                ]);
            case -2:
                throw new RemarkException([
                    'msg' => '评论不存在',
                    'solelyCode' => 208007
                ]);
            case -3:
                throw new RemarkException([
                    'msg' => '评论已删除',
                    'solelyCode' => 208008
                ]);
            case -4:
                throw new RemarkException([
                    'msg' => '该评论不属于本商户',
                    'solelyCode' => 208009
                ]);
        }
        $res = RemarkModel::delRemark($data['id']);
        if ($res==1) {
            throw new SuccessMessage([
                'msg' => '成功删除评论'
            ]);
        }else{
            throw new RemarkException([
                'msg' => '删除评论失败',
                'solelyCode' => 208010
            ]);
        }
    }
}