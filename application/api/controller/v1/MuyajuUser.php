<?php
namespace app\api\controller\v1;
use think\Controller;
use app\api\controller\CommonController;
use app\api\model\Product as ProductModel;
use app\api\model\ThemeContent as ThemeContentModel;
use think\Request as Request;
use app\api\service\Token as TokenService;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

//沐雅居项目特有功能
class MuyajuUser extends CommonController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'countProductShare'],
        'checkToken' => ['only' => 'countProductShare,getBookTime']
    ];

    //增加商品的分享次数
    public function countProductShare(){

        $data = Request::instance()->param();
        //获取thirdapp_id
        $userinfo = TokenService::getUserinfo($data['token']);
        $productinfo = ProductModel::getProductInfo($data['id']);
        if (empty($productinfo)) {
            throw new TokenException([
                'msg'=>'指定商品不存在',
                'solelyCode'=>211000
            ]);
        }else{
            if ($productinfo['status']==-1) {
                throw new TokenException([
                    'msg'=>'指定商品已删除',
                    'solelyCode'=>211001
                ]);
            }else{
                if($productinfo['thirdapp_id']!=$userinfo['thirdapp_id']){
                    throw new TokenException([
                        'msg'=>'该商品不属于本商家',
                        'solelyCode'=>211004
                    ]);
                }
                //记录分享次数
                if (empty($productinfo['passage4'])) {
                    $updateinfo['passage4'] = 1;
                }else{
                    $updateinfo['passage4'] = (int)$productinfo['passage4']+1;
                }
                $update = ProductModel::updateinfo($data['id'],$updateinfo);
                if ($update==1) {
                    throw new SuccessMessage([
                        'msg'=>'分享统计成功'
                    ]);
                }else{
                    throw new TokenException([
                        'msg'=>'分享统计失败'
                    ]);
                }
            }
        }
    }

    //获取预约时间列表
    public function getBookTime()
    {
        $data = Request::instance()->param();
        $contentinfo = ThemeContentModel::getContentInfo(2);
        $userinfo = TokenService::getUserinfo($data['token']);
        if ($contentinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
            throw new TokenException([
                'msg'=>'获取的信息不属于本商家',
                'solelyCode'=>211004
            ]);
        }
        if (!empty($contentinfo['description'])) {
            $booklist = (explode(",",$contentinfo['description']));
            return $booklist;
        }else{
            throw new TokenException([
                'msg'=>'获取预约时间失败',
                'solelyCode'=>213016
            ]);
        }
    }
}