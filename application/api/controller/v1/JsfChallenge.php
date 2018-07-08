<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/6/20
 * Time: 19:27
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\JsfChallenge as ChallengeModel;
use app\api\service\JsfChallenge as ChallengeService;
use think\Request as Request;
use think\Cache;

/**
 * 健身房项目挑战赛模块
 */
class JsfChallenge extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editChallenge,delChallenge,getInfo']
    ];

    public function addChallenge(){
        $data = Request::instance()->param();
        $challengeservice = new ChallengeService();
        $info = $challengeservice->addinfo($data);
        $res = ChallengeModel::addChallenge($info); 
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'添加成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '新增挑战赛失败',
                'solelyCode'=>216001
            ]);
        }
    }

    public function editChallenge(){
        $data = Request::instance()->param();
        $challengeservice = new ChallengeService();
        $checkinfo = $challengeservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '挑战赛不属于本商户',
                    'solelyCode' => 216003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '挑战赛已删除',
                    'solelyCode' => 216002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '挑战赛不存在',
                    'solelyCode' => 216000
                ]);
        }
        $updateinfo = $challengeservice->formatImg($data);
        $res = ChallengeModel::updateChallenge($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改挑战赛成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改挑战赛失败',
                'solelyCode' => 216004
            ]);
        }
    }

    public function delChallenge()
    {
        $data = Request::instance()->param();
        $challengeservice = new ChallengeService();
        $checkinfo = $challengeservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '挑战赛不属于本商户',
                    'solelyCode' => 216003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '挑战赛已删除',
                    'solelyCode' => 216002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '挑战赛不存在',
                    'solelyCode' => 216000
                ]);
        }
        $res = ChallengeModel::delChallenge($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除挑战赛成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除挑战赛失败',
                'solelyCode' => 216005
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $challengeservice = new ChallengeService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = ChallengeModel::getChallengeListToPaginate($info);
            $list = $challengeservice->formatPaginateList($list);
        }else{
            $list = ChallengeModel::getChallengeList($info);
            $list = $challengeservice->formatList($list);
        }
        return $list;
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $info['thirdapp_id'] = $userinfo->thirdapp_id;
        $res = ChallengeModel::getChallengeInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '挑战赛已删除',
                    'solelyCode' => 216002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '挑战赛不属于本商户',
                    'solelyCode' => 216003
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