<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2017-11-27
 * Time: 14:31
 */

namespace app\api\service;
use app\api\model\Remark as RemarkModel;
use think\Cache;

class Remark
{
    /**
     * @return array数据表空字段过滤
     */
    public function initinfo()
    {
        $remarkdome = array(
            "content"      => 0,
            "create_time"  => 0,
            "status"       => 1,
        );
        return $remarkdome;
    }

    public function addinfo($data)
    {
        unset($data['version']);
        $data['create_time'] = time();
        $data = array_merge($this->initinfo(),$data);
        return $data;
    }

    public function updateinfo($data)
    {
        if (!isset($data['id'])) {
            $res['status'] = -1;
            return $res;
        }
        //获取订单信息
        $remarkinfo = RemarkModel::getRemarkInfo($data['id']);
        if (empty($remarkinfo)) {
            $res['status'] = -2;
            return $res;
        }
        if ($remarkinfo['status']==-1) {
            $res['status'] = -3;
            return $res;
        }
        //获取操作者信息
        $userinfo = Cache::get('info'.$data['token']);
        if ($userinfo['thirdapp_id']!=$remarkinfo['thirdapp_id']) {
            if ($userinfo['primary_scope']<40) {
                $res['status'] = -4;
                return $res;
            }
        }
        $res['status'] = 1;
        return $res;
    }
}