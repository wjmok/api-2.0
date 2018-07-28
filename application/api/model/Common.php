<?php
namespace app\api\model;
use think\Model;
use think\Loader;

use think\Cache;



class Common extends Model{

    


    public static function CommonGet($dbTable,$data)
    {

        $data = self::CommonGetPro($data);
        
        $model =Loader::model($dbTable);
        $sqlStr = preModelStr($data);
        if(!isset($data['searchItem']['status'])){
            $data['searchItem']['status'] = 1;
        };

        if(isset($data['paginate'])){  

            $pagesize = $data['paginate']['pagesize'];
            $paginate = $data['paginate'];
            $paginate['page'] = $data['paginate']['currentPage'];
            $sqlStr = $sqlStr."paginate(\$pagesize,false,\$paginate);";
            $res = eval($sqlStr);
            $res = $res->toArray();
            $final = [
                'total'=>$res['total']
            ];
            $res = $model->dealGet(resDeal($res['data']));

            $res = self::CommonGetAfter($data,$res);
           
            $final['data'] = $res;
            return $final;

        }else{

            $sqlStr = $sqlStr."select();";
            
            $res = eval($sqlStr);
            $res = $model->dealGet(resDeal($res));
            $res = self::CommonGetAfter($data,$res);
            if($dbTable=='article'){
                $updateData = [];
                foreach ($res as $key => $value) {
                    array_push($updateData,['id'=>$value['id'],'view_count'=>$value['view_count']+1])
                };
                $model->saveAll($updateData);
            };
            
            $final['data'] = resDeal($res);
            return $final;
        };
        
        
    }



    public static function CommonSave($dbTable,$data)
    {
        
        $data = self::CommonGetPro($data);
        
        $model =Loader::model($dbTable);

        $sqlStr = preModelStr($data);

        $data['data'] = keepNum($data['data']);
        $data['data'] = jsonDeal($data['data']);
        $data['data']['update_time'] = time();

        $content = $data['data'];
        
        $FuncName = $data['FuncName'];
        
        if($FuncName=='update'){
            $model->dealUpdate($data);
            $sqlStr = $sqlStr."update(\$content);";
            $res = eval($sqlStr);
            
            return $res;
        }else{

            $content = $model->dealAdd($content);
            
            $res = $model->allowField(true)->save($content);
            return $model->id;
        };
        
    }



    public static function CommonDelete($dbTable,$data)
    {
        $model =Loader::model($dbTable);
        $sqlStr = preModelStr($data);
        $sqlStr = $sqlStr."delete();";
        $res = eval($sqlStr);
        $model->realDeleteData($data);
        return $res;
    }



    public static function CommonGetPro($data)
    {
        
        if(isset($data['join'])){
            foreach ($data['join'] as $key => $value) {
                $model =Loader::model($key);
                $search = [];
                foreach ($value['searchItem'] as $c_key => $c_value) {
                    foreach ($c_value[1] as $c_current) {
                        $c_search = [];
                        $res = $model->where($c_key,$c_value[0],$c_current)->select();
                        foreach ($res as $ckey => $cvalue) {
                            array_push($c_search,$cvalue[$value['s_key']]);
                        };
                        if(empty($search)){
                            $search = $c_search;
                        }else{
                            $search = array_intersect($search,$c_search);
                        };
                        
                    };
                };
                
                if(isset($data['searchItem'])){
                    if(isset($data['searchItem'][$value['key']])){
                        $data['searchItem'][$value['key']] = [$value['condition'],array_intersect($search,$data['searchItem'][$value['key']][1])];
                    }else{
                        $data['searchItem'][$value['key']] = [$value['condition'],$search];
                    };
                    
                }else{
                    $data['searchItem'] = [];
                    $data['searchItem'][$value['key']] = [$value['condition'],$search];
                };

            };
        };
        
        return $data;
    }

    public static function CommonGetAfter($data,$res)
    {
        
        if(isset($data['joinAfter'])){

            foreach ($res as $key => $value) {

                
                foreach ($data['joinAfter'] as $c_key => $c_value) {
                    $new = [];

                    $model =Loader::model($c_key);

                    $nRes = $model->where($c_value['relation_final_key'],$c_value['relation_condition'],$value[$c_value['relation_key']])->select();
                    
                    
                    if(isset($c_value['relation_info'])&&!empty($nRes)){
                        $nRes[0] = $nRes[0]->toArray();
                        foreach ($c_value['relation_info'] as $info_key => $info_value) {
                           $new[$info_value] = $nRes[0][$info_value];
                        }; 
                    };
                    
                    if(isset($c_value['relation_compute'])){

                        foreach ($c_value['relation_compute'] as $compute_key => $compute_value) {
                            if($compute_value!='count'){
                               $new[$compute_key.$compute_value] = $model->where($c_value['relation_final_key'],$c_value['relation_condition'],$value[$c_value['relation_key']])->$compute_value($compute_key); 
                           }else{
                                $new['totalCount'] = $model->where($c_value['relation_final_key'],$c_value['relation_condition'],$value[$c_value['relation_key']])->count(); 
                                
                           };
                        };
                    };


                    $res[$key][$c_key] = $new;

                };
                
            };
            
        };

        return $res;
        
    }



    public static function CommonCompute($data)
    {
        
        
            $res = [];
  
            foreach ($data as $key => $value) {
                $new = [];

                $model =Loader::model($key);

                foreach ($value['compute'] as $compute_key => $compute_value) {
                    if($compute_value!='count'){
                       $new[$compute_key.$compute_value] = $model->where($value['searchItem'])->$compute_value($compute_key); 
                   }else{
                        $new['count'] = $model->where($value['searchItem'])->count(); 
                        
                   };
                };
                
                $res[$key] = $new;

            };
            
        return $res;
        
    }



    


}