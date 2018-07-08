<?php
namespace app\api\controller\v1;
use app\api\model\JsfChallenge as ChallengeModel;
use think\Request as Request;
use think\Controller;
use app\api\controller\CommonController;
use think\Exception;
use app\lib\exception\TokenException;
use app\api\service\JsfChallenge as ChallengeService;

//健身房项目客户挑战赛接口
class JsfUserChallenge extends CommonController{

    public function getList(){

        $data = Request::instance()->param();
        $challengeservice = new ChallengeService();
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
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = ChallengeModel::getChallengeListToPaginate($info);
            $list = $challengeservice->formatPaginateList($list);
        }else{
            $list = ChallengeModel::getChallengeList($info);
            $list = $challengeservice->formatList($list);
        }
        return $list;
    }

    //获取指定预约信息
    public function getInfo(){

        $data = Request::instance()->param();
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
        $res = ChallengeModel::getChallengeInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '挑战赛已删除',
                    'solelyCode' => 216002
                ]);
            }
            $challengeservice = new ChallengeService();
            $res = $challengeservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '挑战赛不存在',
                'solelyCode' => 216000
            ]);
        }
    }
}