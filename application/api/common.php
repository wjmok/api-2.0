<?php

/**
 * @param string $url post请求地址
 * @param array $params
 * @return mixed
 */
use think\Cache;
use app\lib\exception\TokenException;

use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;

function curl_post($url, array $params = array())
{
    $data_string = json_encode($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt(
        $ch, CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json'
        )
    );
    $data = curl_exec($ch);
    curl_close($ch);
    return ($data);
}

function curl_post_raw($url, $rawData)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
    curl_setopt(
        $ch, CURLOPT_HTTPHEADER,
        array(
            'Content-Type: text'
        )
    );
    $data = curl_exec($ch);
    curl_close($ch);
    return ($data);
}

/**
 * @param string $url get请求地址
 * @param int $httpCode 返回状态码
 * @return mixed
 */
function curl_get($url, &$httpCode = 0)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //不做证书校验,部署在linux环境下请改为true
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
    $file_contents=curl_exec($ch);
    $httpCode=curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $file_contents;
}

//获取随机数
function getRandChar($length){
    $str=null;
    $strPol="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max=strlen($strPol)-1;
    for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];
    }
    return $str;
}

//筛选/过滤条件
function checkData($data){  
    if(!isset($data['is_page'])){
        throw new TokenException([
            'msg'=>'is_page参数只能为true或者false且不能为空',
            'solelyCode'=>200003
        ]);
    }else{
        if($data['is_page']==="true"||$data['is_page']===true){
            if(!isset($data['pagesize'])||$data['pagesize']==''){
                throw new TokenException([
                    'msg'=>'每页显示数据不能为空或未设置pagesize参数',
                    'solelyCode'=>200004
                ]);
            }
            if(!isset($data['currentPage'])||$data['currentPage']==""){
                 throw new TokenException([
                    'msg'=>'当前页码不能为空或未设置currentPage参数',
                    'solelyCode'=>200005
                ]);
            }
            return [
                'code'=>2 
            ];
        }else if($data['is_page']==="false"||$data['is_page']===false){           
            if(isset($data['pagesize'])){
                throw new TokenException([
                    'msg'=>'pagesize参数无需设置',
                    'solelyCode'=>200006
                ]);
            }
            if(isset($data['currentPage'])){
                throw new TokenException([
                    'msg'=>'currentPage参数无需设置',
                    'solelyCode'=>200007
                ]);
            }
            return [
                'code'=>2 
            ];            
        }else{
            throw new TokenException([
                'msg'=>'is_page参数只能为true或者false且不能为空',
                'solelyCode'=>200003
            ]);
        }
    } 
}

//分页/筛选数据
function preAll($data){
    if(isset($data['pagesize'])){
        $size=$data['pagesize'];
    }
    if(isset($data['currentPage'])){
       $page=$data['currentPage'];
    }
    $isPage=$data['is_page']; 
    if(isset($data['searchItem'])){
        $search=$data['searchItem'];
    }else{
        $search='';
    }
    if($isPage=='true'){
        $res=[
            'pagesize'=>$size,
            'currentPage'=>$page,
            'map'=>$search,
        ];                
    }else{      
        $res=['map'=>$search];
    }
    return $res;
}

function preAll2($data){
    
    if(isset($data['searchItem'])){
        $search=$data['searchItem'];
    }else{
        $search='';
    }
        $res=[
            'map'=>$search,
        ];
    return $res;
}

function fromArrayToModel($m,$array){
    foreach($array as $key=>$value){
        $m[$key]=$value;
    }
    return $m;
}

/**
 * @param array $img 多图数组
 * @return 检查图片内容，返回字符串
 */
function initimg($img)
{
    if($img=='empty'){
        $img=json_encode([]);
    }else{
        $img=json_encode($img);
    }
    return $img;
}

/**
 * @return 短信验证码
 */

function createSMSCode($length){
    $min = pow(10,($length-1));
    $max = pow(10, $length)-1;
    return rand($min,$max);
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @return array
 */
function clist_to_tree($list, $pk='id', $pid = 'parentid', $child = 'child', $root = 0)
{
    // 创建Tree
    $tree = array();
    if($list[0]&&isset($list[0][$pid])){
        if(is_array($list)) {
        // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                
                
                $parentId =  $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
                
                
            }
        }
    }else{
        $tree = $list;
    };
    
    return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function ctree_to_list($tree, $child = 'child', $order = 'id', &$list = array()){
    if(is_array($tree)) {
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if(isset($reffer[$child])){
                unset($reffer[$child]);
                ctree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby='asc');
    }
    return $list;
}

/**
 * 对查询结果集进行排序
 *
 * @access public
 * @param array $list
 *          查询结果
 * @param string $field
 *          排序的字段名
 * @param array $sortby
 *          排序类型
 *          asc正向排序 desc逆向排序 nat自然排序
 * @return array
 *
 */
function list_sort_by($list, $field, $sortby = 'asc') {
    if (is_array ( $list )) {
        $refer = $resultSet = array ();
        foreach ( $list as $i => $data )
            $refer [$i] = &$data [$field];
        switch ($sortby) {
            case 'asc' : // 正向排序
                asort ( $refer );
                break;
            case 'desc' : // 逆向排序
                arsort ( $refer );
                break;
            case 'nat' : // 自然排序
                natcasesort ( $refer );
                break;
        }
        foreach ( $refer as $key => $val )
            $resultSet [] = &$list [$key];
        return $resultSet;
    }
    return false;
}

//生成订单号
function makeOrderNo()
{
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    $orderSn =
        $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
            'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
            '%02d', rand(0, 99));
    return $orderSn;
}

function makeUserNo()
{
    
    $userSn ='U'. strtoupper(dechex(date('m'))) . date(
            'd') . substr(time(), -5) . substr(microtime(), 2, 5).rand(0, 99);

    return $userSn;
}


function objectToArray ($object) {  
    if(!is_object($object) && !is_array($object)) {  
        return $object;  
    }  
  
    return array_map('objectToArray', (array) $object);  
}  






//分页/筛选数据
function preGet($data){
    

    

    if(!isset($data['status'])){
        $data['map']['status'] = 1;
    };

    return $data;


}





function preAdd($data){
    
    
    if(isset($data['password'])){
        $data['password'] = md5($data['password']);
    };
    
        
    
    if(!isset($data['data']['thirdapp_id'])){
        $data['data']['thirdapp_id'] = Cache::get($data['token'])['thirdapp_id'];
    };

    
    
    $data['data']['user_no'] = Cache::get($data['token'])['user_no'];

    $data = jsonDeal($data);

    return $data;

}

//分页/筛选数据
function preUpdate($data){
    unset($data['data']['user_no']);
    unset($data['data']['id']);
    $data = jsonDeal($data);
    return $data;
}



//分页/筛选数据
function jsonDeal($data){
    foreach ($data as $key => $value) {
        if(is_array($value)){
            $data[$key] = json_encode($value);
        };
    };
    
    return $data;
}


function resDeal($data,$arr)
{   
    
    if(isset($data['data'])){
        $dealData = $data['data'];
    }else{
        $dealData = $data;
    }

    foreach ($dealData as $key => $value) {

        foreach ( $dealData[$key] as $child_key => $child_value) {
           if(in_array($child_key,$arr)){
            
            $dealData[$key][$child_key] = json_decode($child_value,true);
           }
        };
        
    };

    if(isset($data['data'])){
        $data['data'] = $dealData;
    }else{
        $data = $dealData;
    };
    
    
    
    return $data;

}



//分页/筛选数据
function dealUpdateRes($res,$name){
    


    if($res>0){
        throw new SuccessMessage([
            'msg'=>$name.'成功'
        ]);
    }else if($res==0){
        throw new ErrorMessage([
            'msg'=>'重复'.$name
        ]);
    }else{
        throw new ErrorMessage([
            'msg'=>$name.'失败'
        ]);
    }

    

}



//分页/筛选数据
function preModelStr($data){
    
    $str = "return \$model->";
    if(isset($data['map'])){
        $str = $str."where(\$data[\"map\"])->";
    };
    if(isset($data['order'])){
        $str = $str."order(\$data[\"order\"])->";
    };
    return $str;
}

//分页/筛选数据
function after($data,$arr){
    $arr = ['img','mainImg','bannerImg','headImg','child_array'];
   
    foreach ($data as $key => $value) {
       if(in_array($key,$arr)){
        $data[$key] = json_decode($value,true);
       }
    }
    
    return $data;
}


//分页/筛选数据
function chargeBlank($arr,$data){
  
    foreach ($arr as $key => $value) {
       if(!isset($data[$key])){
        $data[$key]=$value;
       };
    };

    $data = jsonDeal($data);
    
    return $data;
}



    /**
     * @param array 接收到的数据
     * @return 检查token参数是否设置
     */
    function checkToken ($data)
    {
        //$data=Request::instance()->param();
        if (!isset($data['token'])) {
            throw new TokenException();
        }
    }

    /**
     * @param array 接收到的数据
     * @return 检查thirdappID参数是否设置
     */
    function checkThirdID ()
    {
        $data=Request::instance()->param();
        if (!isset($data['thirdapp_id'])) {
            throw new TokenException([
                'msg'=>'缺少参数ThirdID',
                'solelyCode' => 200002
            ]);
        }
    }



    function checkValue ($data,$check)
    {

        foreach ($check as $key => $value) {
            
            if(in_array($data['serviceFuncName'],$value)){
                
                if(!array_key_exists($key,$data)){
                    if($key=='token'){
                        throw new ErrorMessage([
                            'msg'=>'缺少参数'.$key,
                            'solelyCode' => 200000
                        ]); 
                    }else{
                        throw new ErrorMessage([
                            'msg'=>'缺少参数'.$key,
                        ]);
                    }
                    
                }
            }
        }
        
    }

    function checkTokenAndScope ($data,$scope=0)
    {
        
        
        if(!Cache::get($data['token'])){

            throw new ErrorMessage([
                'msg'=>'token已失效',
                'solelyCode' => 200000
            ]);

        }else if(Cache::get($data['token'])['primary_scope']<$scope){
            
            throw new ErrorMessage([
                'msg'=>'权限不足',
            ]);

        };

        if(isset($data['map']['thirdapp_id'])){
            
            
            if(Cache::get($data['token'])['thirdapp_id'] != $data['map']['thirdapp_id']||!in_array($data['map']['thirdapp_id'],Cache::get($data['token'])['thirdApp']['child_array'])){

                if(Cache::get($data['token'])['primary_scope']<60){

                   throw new ErrorMessage([
                        'msg'=>'项目权限不符',
                    ]); 

                }
                
            }
            
        }else{

            $data['map']['thirdapp_id'] = Cache::get($data['token'])['thirdapp_id'];

        };


        if(Cache::get($data['token'])['primary_scope']<30){

            if(isset($data['map']['user_no'])&&$data['map']['user_no'] != Cache::get($data['token'])['user_no']){
                throw new ErrorMessage([
                    'msg'=>'项目权限不符',
                ]); 

            }else{
                $data['map']['user_no'] = Cache::get($data['token'])['user_no'];
            };
            if(isset($data['map']['thirdapp_id'])&&$data['map']['thirdapp_id'] != Cache::get($data['token'])['thirdapp_id']){
                throw new ErrorMessage([
                    'msg'=>'项目权限不符',
                ]); 

            }else{
                $data['map']['thirdapp_id'] = Cache::get($data['token'])['thirdapp_id'];
            };
            



        }else if(Cache::get($data['token'])['primary_scope']<90){

            if(isset($data['map']['thirdapp_id'])){
            
            
                if(Cache::get($data['token'])['thirdapp_id'] != $data['map']['thirdapp_id']&&!in_array($data['map']['thirdapp_id'],Cache::get($data['token'])['thirdApp']['child_array'])){

                    

                    throw new ErrorMessage([
                        'msg'=>'项目权限不符',
                    ]); 

                    
                }
                
            }else{

                $data['map']['thirdapp_id'] = Cache::get($data['token'])['thirdapp_id'];

            }; 

            if(isset($data['data']['thirdapp_id'])){
            
                if(Cache::get($data['token'])['thirdapp_id'] != $data['data']['thirdapp_id']&&!in_array($data['data']['thirdapp_id'],Cache::get($data['token'])['thirdApp']['child_array'])){
                    throw new ErrorMessage([
                        'msg'=>'项目权限不符',
                    ]); 
                }
                
            };

        };

        return $data;

        
        
    }


    function generateToken(){
        $randChar = getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $tokenSalt = config('secure.token_salt');
        return md5($randChar . $timestamp . $tokenSalt);
    }







    function preSearch($data){
    

        $data['map'] = [];

        if(isset($data['searchItem'])){

            $data['map'] = array_merge($data['map'],objectToArray($data['searchItem']));
            unset($data['searchItem']);
        }

        if(isset($data['searchItemByIn'])){
            foreach ($data['searchItemByIn'] as $key => $value) {
                $data['searchItemByIn'][$key] = ['in', $value];
            };
            $data['map'] = array_merge($data['map'],objectToArray($data['searchItemByIn']));
            unset($data['searchItemByIn']);
        }

        if(isset($data['searchItemByLike'])){
            foreach ($data['searchItemByLike'] as $key => $value) {
                $data['searchItemByLike'][$key] = ['like', $value];
            };
            $data['map'] = array_merge($data['map'],objectToArray($data['searchItemByLike']));
            unset($data['searchItemByLike']);
        }

        if(isset($data['searchItemByGt'])){
            foreach ($data['searchItemByGt'] as $key => $value) {
                $data['searchItemByGt'][$key] = ['gt', $value];
            };

            $data['map'] = array_merge($data['map'],objectToArray($data['searchItemByGt']));
            unset($data['searchItemByGt']);
        }

        if(isset($data['searchItemByLt'])){
            foreach ($data['searchItemByLt'] as $key => $value) {
                $data['searchItemByLt'][$key] = ['lt', $value];
            };
            $data['map'] = array_merge($data['map'],objectToArray($data['searchItemByLt']));
            unset($data['searchItemByLt']);
        }
        if(isset($data['searchItemByBetween'])){
            foreach ($data['searchItemByBetween'] as $key => $value) {
                $data['searchItemByBetween'][$key] = ['between', $value];
            };
            $data['map'] = array_merge($data['map'],objectToArray($data['searchItemByBetween']));
            unset($data['searchItemByBetween']);
        }

        return $data;

    }