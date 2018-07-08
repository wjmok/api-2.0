<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2017-11-25
 * Time: 0:18
 */

namespace app\api\service;

use app\api\model\UserAddress as AddressModel;

class Address
{

    /**
     * @return array数据表空字段过滤
     */
    public function initinfo()
    {
        $addressdome = array(
            "name"        => '',
            "phone"      => '',
            "province"    => '',
            "city"        => '',
            "country"     => '',
            "detail"      => '',
            "status"      => 1,
            "isdefault"   => 0,
            "create_time" => 0,
            "update_time" => 0,
        );
        return $addressdome;
    }

    public function addinfo($data)
    {
        unset($data['version']);
        $data['create_time'] = time();
        $data = array_merge($this->initinfo(),$data);
        return $data;
    }

    /**
     * @param $id
     * @return 整理数据
     */
    public function deladdress($id)
    {   
        $data['delete_time'] = time();
        $data['status'] = -1;
        return $data;
    }

    /**
     * @deal 删除原默认地址
     */
    public function canceldefault($user_id)
    {
        $map['map']['user_id'] = $user_id;
        $addresslist = AddressModel::getAddressList($map);
        if ($addresslist) {
            foreach ($addresslist as $key => $value) {
                $upinfo['isdefault'] = 0;
                $up = AddressModel::updateinfo($value['id'],$upinfo);
            }   
        }
    }
}