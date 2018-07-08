<?php
namespace app\api\controller\v1;
use app\api\controller\BaseController;
use app\api\model\Category as CategoryModel;
use think\Controller;
use think\Request as Request;
use app\api\service\Category as CategoryService;
use app\api\validate\CategoryValidate;
use app\api\validate\CategoryUptValidate;
use app\lib\exception\CategoryException;
use app\lib\exception\SuccessMessage;
use app\lib\exception\TokenException;
use think\Cache;

class Category extends BaseController{
    //新增类别
    public function addCategory(){
        $data = Request::instance()->param();
        $validate = new CategoryValidate();
        $validate->goCheck();
        $categoryservice = new CategoryService();
        $info = $categoryservice->addinfo($data);
        $res = CategoryModel::addCategry($info);
        if($res){
            throw new SuccessMessage([
                'msg'=>'添加分类成功'
            ]);
        }else{
            throw new CategoryException([
                'msg' => '添加分类失败',
                'solelyCode' => 212003
            ]);
        }     
    }

    //修改类别信息
    public function editCategory(){
        $data = Request::instance()->param();
        $validate = new CategoryUptValidate();
        $validate->goCheck();
        if (isset($data['cCategoryid'])) {
            $change = $this->changeClass($data['id'],$data['cCategoryid'],$data['token']);
            if ($change) {
                unset($data['cCategoryid']);
            }
        }
        $userinfo = Cache::get('info'.$data['token']);
        $categoryinfo = CategoryModel::getCategoryInfo($data['id']);
        if ($categoryinfo) {
            if ($categoryinfo['status']==-1) {
                throw new CategoryException([
                    'msg' => '指定分类已删除',
                    'solelyCode' => 212001
                ]);
            }
            if ($categoryinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new CategoryException([
                    'msg' => '此分类不属于本商家',
                    'solelyCode' => 212002
                ]);
            }
        }else{
            throw new TokenException([
                'msg' => '指定分类不存在',
                'solelyCode' => 212000
            ]);
        }
        $res = CategoryModel::updateCategory($data['id'],$data);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改分类成功'
            ]);         
        }else{
            throw new CategoryException();
        }
    }

    //软删除
    public function delCategory()
    {
        $data = Request::instance()->param();
        $validate = new CategoryUptValidate();
        $validate->goCheck();
        $articleservice = new CategoryService();
        $info = $articleservice->delCategory($data);
        $res = CategoryModel::delCategory($data['id'],$info);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除分类成功'
            ]);
        }else if($res==0){
            throw new CategoryException([
                'msg' => '指定分类已删除',
                'solelyCode' => 212001
            ]);
        }else if($res==-2){
            throw new CategoryException([
                'msg' => '此分类不属于本商家',
                'solelyCode' => 212002
            ]);
        }else{
            throw new CategoryException();
        }
    }

    //获取类别层级
    public function getCategoryTree()
    {
        $data = Request::instance()->param();
        $categoryservice = new CategoryService();
        $info = $categoryservice->initTree($data);
        $tree = CategoryModel::getCategoryTree($info);
        return $tree;
    }

    //获取类别列表
    public function getCategoryList()
    {
        $data = Request::instance()->param();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = CategoryModel::getCategoryListToPaginate($info,$data['token']);
        }else{
            $list = CategoryModel::getCategoryList($info,$data['token']);
        }
        if($list==0){
            throw new CategoryException([
                'msg' => 'id参数权限不符',
                'solelyCode' => 212004
            ]);
        }
        return $list;
    }

    //获取指定分类信息
    public function getCategoryinfo()
    {
        $data = Request::instance()->param();
        $validate = new CategoryUptValidate();
        $validate->goCheck();
        $id = $data['id'];
        $res = CategoryModel::getCategoryInfo($id);
        if(is_object($res)){
            return $res;
        }else if($res==-2){
            throw new CategoryException([
                'msg' => '此分类不属于本商家',
                'solelyCode' => 212002
            ]);
        }else if($res==-1){
            throw new CategoryException([
                'msg' => '指定分类已删除',
                'solelyCode' => 212001
            ]);
        }else{
            throw new CategoryException();
        }
    }

    //更换两个类别的子父级关系
    public function changeClass($id,$cCategoryid,$token)
    {
        $res = CategoryService::changeClass($id,$cCategoryid,$token);
        switch ($res) {
            case 1:
                return true;
            case 2:
                throw new TokenException([
                    'msg' => '缺少关键参数ID/子级ID',
                    'solelyCode' => 203012
                ]);
            case 3:
                throw new TokenException([
                    'msg' => '指定分类不存在',
                    'solelyCode' => 212000
                ]);
            case 4:
                throw new TokenException([
                    'msg' => '指定分类已删除',
                    'solelyCode' => 212001
                ]);
            case 5:
                throw new TokenException([
                    'msg' => '此分类不属于本商家',
                    'solelyCode' => 212002
                ]);
            case 6:
                throw new TokenException([
                    'msg' => '提交的父级类别不是提交的子级类别的父级',
                    'solelyCode' => 212000
                ]);
            default:
                break;
        }
    }
}