<?php
namespace app\api\controller\v1;
use app\api\model\Merchant as MerchantModel;
use think\Request as Request;
use think\Controller;
use app\api\controller\CommonController;
use think\Exception;
use app\lib\exception\TokenException;
use app\api\service\Merchant as MerchantService;

class UserMerchant extends CommonController{

    protected $beforeActionList = [
        'checkThirdID' => ['only' => 'getList,getInfo'],
        'checkID' => ['only' => 'getInfo']
    ];

    //获取商家列表
    public function getList(){

        $data = Request::instance()->param();
        $merchantService = new MerchantService;
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = MerchantModel::getMerchantListToPaginate($info);
            $list = $merchantService->formatPaginateList($list);
        }else{
            $list = MerchantModel::getMerchantList($info);
            $list = $merchantService->formatList($list);
        }
        return $list;
    }

    //获取指定商家信息
    public function getInfo(){

        $data=Request::instance()->param();
        $res=MerchantModel::getMerchantInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '商家信息已删除',
                    'solelyCode' => 230002
                ]);
            }
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '该商家不属于本商户',
                    'solelyCode' => 230003
                ]);
            }
            $merchantService = new MerchantService;
            $res = $merchantService->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '商家信息不存在',
                'solelyCode' => 230000
            ]);
        }
    }
}