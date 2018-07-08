<?php
/**
 * 前端用户功能
 * Created by 董博明
 * Author: 董博明
 * Date: 2018/3/20
 * Time: 18:37
 */

namespace app\api\controller\v1;
use app\api\model\User as UserModel;
use app\api\model\ThirdApp as ThirdAppModel;
use app\api\model\FlowScore as FlowScoreModel;
use app\api\service\User as UserService;
use think\Request as Request; 
use think\Controller;
use app\api\service\Token as TokenService;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use think\Cache;

class User extends CommonController
{
    protected $beforeActionList = [
        'checkToken' => ['only' => 'editUserinfo,getUserinfo,checkPhone,signin']
    ];

    /**
     * 修改用户信息
     * @author 董博明
     * @POST data
     * @time 2017.11.4
     */
    public function editUserinfo(){
        $data = Request::instance()->param();
        if (isset($data['headimgurl'])) {
            if ($data['headimgurl']=="empty") {
                $data['headimgurl'] = json_encode([]);
            }else{
                $headimg['name'] = "headimg";
                $headimg['url'] = $data['headimgurl'];
                $data['headimgurl'] = json_encode(["0"=>['name'=>'headimg','url'=>$data['headimgurl']]]);;
            }
        }
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        $user = UserModel::updateUserinfo($userinfo['id'],$data);
        if ($user==1) {
            throw new SuccessMessage([
                'msg' => '修改信息成功'
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改信息失败',
                'solelyCode' => 205002
            ]);
        }
    }

    /**
     * 获取指定用户信息
     * @author 董博明
     * @post token
     * @time 2017.11.7
     */
    public function getUserinfo()
    {
        $data = Request::instance()->param();
        //验证token信息
        $userinfo = TokenService::getUserinfo($data['token']);
        return $userinfo;
    }

    /**
     * 验证是否绑定手机
     * @author 董博明
     * @post token
     * @time 2018.3.20
     */
    public function checkPhone()
    {
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        if (!empty($userinfo['phone'])&&!is_null($userinfo['phone'])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 签到并记录积分
     * @time 2018.4.26
     */
    public function signin()
    {
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        $newuser = UserModel::getUserInfo($userinfo['id']);
        $today = strtotime(date('Y-m-d'));
        if ($newuser['signin_time']<=$today) {
            $thirdinfo = ThirdAppModel::getThirdUserInfo($userinfo['thirdapp_id']);
            $rule = json_decode($thirdinfo['distributionRule'],true);
            if (isset($rule['signin'])) {
                $update['user_score'] = $newuser['user_score']+(int)$rule['signin'];
                $update['signin_time'] = time();
                $upuser = UserModel::updateUserinfo($newuser['id'],$update);
                //记录积分流水
                $flowScore = array(
                    'thirdapp_id'=>$newuser['thirdapp_id'],
                    'user_id'=>$newuser['id'],
                    'scoreAmount'=>(int)$rule['signin'],
                    'order_type'=>'sign',
                    'trade_type'=>1,
                    'trade_info'=>date('Y-m-d')."签到奖励",
                    'create_time'=>time(),
                    'status'=>1
                    );
                $ScoreAdd = FlowScoreModel::addFlow($flowScore);
                if ($ScoreAdd==1) {
                    throw new SuccessMessage([
                        'msg' => '签到成功'
                    ]);
                }else{
                    throw new TokenException([
                        'msg' => '签到失败',
                        'solelyCode' => 205008
                    ]); 
                }
            }else{
                throw new TokenException([
                    'msg' => '未设置签到参数',
                    'solelyCode' => 205007
                ]); 
            }
        }else{
            throw new TokenException([
                'msg' => '今天已签到',
                'solelyCode' => 205009
            ]);
        }
    }
}