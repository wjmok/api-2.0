<?php
namespace app\api\controller\v1;
use think\Request as Request;
use think\Controller;
use app\api\controller\BaseController;
use app\api\model\ThirdApp as ThirdModel;
use app\api\model\Order as OrderModel;
use app\api\model\OrderItem as OrderItemModel;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use think\Cache;

//果行育德项目特有功能
class GuoxingydCms extends BaseController{

    //设计分销奖励
    public function setDistribution()
    {

        $data = Request::instance()->param();
        //获取thirdapp_id
        $userinfo = Cache::get('info'.$data['token']);
        if ($userinfo['primary_scope']<30) {
            throw new TokenException([
                'msg'=>'您权限不足，不能执行该操作',
                'solelyCode'=>201009
            ]);
        }
        $update['distributionRule'] = json_encode($data['distributionRule']);
        $res = ThirdModel::upTuser($userinfo['thirdapp_id'],$update);
        if ($res==1) {
            throw new SuccessMessage([
                'msg'=>'修改成功'
            ]);
        }else{
            throw new TokenException([
                'msg'=>'修改商户信息失败',
                'solelyCode'=>201012
            ]);
        }
    }

    //Excel导出
    public function getExcel()
    {
        $data = Request::instance()->param();

        // if(isset($data['searchItem'])){
        //     $info['map'] = $data['searchItem'];
        // }
        //获取thirdapp_id
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $list = OrderModel::getOrderList($info);

        $fileName = "报名数据";
        $xlsName = "ParticipationData";
        $xlsCell = array(  
            array('id','ID'),
            array('order_no','订单编号'),
            array('name','营员姓名'),
            array('IDcard','身份证号'),
            array('gender','性别'),
            array('ethnic','民族'),
            array('address','家庭地址'),
            array('parent_name','家长姓名'),
            array('contact','联系方式'),
            array('second_contacts','第二联系人'),
            array('second_contact','联系方式'),
            array('type','营种'),
            array('period','营期'),
        );
        $xlsData = array();
        foreach($list as $k=>$v){
            $info = json_decode($v['passage1'],true);
            $map['status'] = 1;
            $map['order_id'] = $v['id'];
            $orderinfo = OrderItemModel::getItemByMap($map);
            $goodsinfo = json_decode($orderinfo['snap_product'],true);
            $xlsData[$k]['id'] = $v['id'];
            $xlsData[$k]['order_no'] = $v['order_no'];
            $xlsData[$k]['name'] = $info['营员姓名'];
            $xlsData[$k]['IDcard'] = 'ID:'. $info['营员身份证'];
            $xlsData[$k]['gender'] = $info['性别'];
            $xlsData[$k]['ethnic'] = $info['民族'];
            $xlsData[$k]['address'] = $info['家庭住址'];
            $xlsData[$k]['parent_name'] = $info['家长姓名'];
            $xlsData[$k]['contact'] = isset($info['第一联系方式'])?'tel:'.$info['第一联系方式']:'';
            $xlsData[$k]['second_contacts'] = $info['第二联系人'];
            $xlsData[$k]['second_contact'] = isset($info['第二联系方式'])?'tel:'.$info['第二联系方式']:'';
            $xlsData[$k]['type'] = $goodsinfo['category']['name'];
            $xlsData[$k]['period'] = $goodsinfo['name'];
            // $xlsData[$k]['create_time']=date('y-m-d',$v['create_time']);
        }
        // return $xlsData;
        $this->exportExcel($xlsName,$xlsCell,$xlsData,$fileName);
    }
}