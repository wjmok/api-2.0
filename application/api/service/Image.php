<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/1/5
 * Time: 10:34
 */

namespace app\api\service;

use think\Cache;
use app\api\model\Image as ImageModel;
use app\lib\exception\ImageException;
use app\lib\exception\TokenException;
use app\lib\exception\AdminException;
use app\lib\exception\ThirdappException;
use app\api\model\Admin as AdminModel;
use app\api\model\ThirdApp as ThirdAppModel;
/**
 * 图片服务类
 */
class Image
{

   	public function initinfo($data)
   	{
   		//检验token
   		if(isset($data['token'])&&$data['token']==Cache::get('token'.$data['token'])){
        $adminInfo=AdminModel::checkStatus(Cache::get('name'.$data['token']),Cache::get('password'.$data['token']));
        if ($adminInfo['primary_scope']!=40) {
          $res['status'] = 2;
        }
        if ($adminInfo['status'] == -1) {
          $res['status'] = 3;
        }
      }else{
        $res['status'] = 4;
      }
      //检验是否是删除项目全部图片
      if (isset($data['delItem']['thirdapp_id'])) {
        $thirdappmodel = new ThirdAppModel;
        $custominfo = $thirdappmodel->getThirdUserInfo($data['delItem']['thirdapp_id']);
        if (!$custominfo) {
          $res['status'] = 5;
        }
        $res['status'] = 1;
        $res['info'] = $custominfo;
      }else{
        $res['status'] = 1;
        $res['info'] = $adminInfo;
      }
      return $res;
   	}

   	public function checkdelItem($data)
   	{
   		//检验删除条件
        if (!isset($data['delItem'])) {
        	return true;
        }
   	}

   	public function initkey($data)
   	{
   		$imagemodel = new ImageModel;
       	if(isset($data['delItem']['id'])){
       		$name = array();
       		$name = explode(',', $data['delItem']['id']);
       		$res['size'] = 0;
          $res['ids'] = $name;
       		foreach ($name as$k => $v) {
       			$img = $imagemodel->getImgByID($v);
       			$res['keys'][$k] = $img['name'];
       			$res['size'] += $img['size']; 
       		}
       		if ($res['size'] == 0) {
       			return 1;
       		}
       	}elseif (isset($data['delItem']['thirdapp_id'])) {
       		$map['map']['thirdapp_id'] = $data['delItem']['thirdapp_id'];
       		$imglist = $imagemodel->getAllImg($map);
       		$ids = array();
       		$imgname = array();
       		$res['size'] = 0;
       		foreach ($imglist as $k => $v) {
       			array_push($ids,$v['id']);
       			array_push($imgname,$v['name']);
       			$res['size'] += $v['size'];
       		}
       		$res['ids'] = $ids;
       		$res['imgname'] = $imgname;
       		if ($res['size'] == 0) {
       			return 2;
       		}
       	}
      return $res;
   	}
}