<?php

namespace app\api\model;
use think\Model;
use app\api\model\relation;
use app\api\model\Label;
use app\api\model\labelItem;
use app\api\model\Product;





class Sku extends Model{

    public static function dealAdd($data)
    {   
        
        $standard = ['sku_no'=>'','title'=>'','label_array'=>'','sku_array'=>'','product_id'=>'','stock'=>'','price'=>'','mainImg'=>[],'description'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','thirdapp_id'=>'','status'=>1,'user_no'=>'',];
        $data = chargeBlank($standard,$data);

        $res = Product::get(['id' => $data['product_id']]);

        if(!$res){
            throw new ErrorMessage([
                'msg' => '关联信息有误',
            ]);
        };
        $data['category_id'] = $res['category_id'];
        $data['spu_array'] = $res['spu_array'];
        $data['sku_no'] = generateSpuNo($res,$data['sku_array']);

        return $data;
        
    }

    public static function dealGet($data)
    {   
        

        $product_array = [];

        foreach ($data as $key => $value) {
            array_push($product_array,$value['product_id']);
        };

        $product = resDeal((new product())
            ->where('id','in',$product_array)
            ->select()
        );
        

        foreach ($product as $key => $value) {
            $sku_array = $value['sku_array'];
            array_push($sku_array,$value['category_id']);
            $label = resDeal((new Label())->where('id','in',$sku_array)->whereOr('parentid','in',$sku_array)->select());
            $label = clist_to_tree($label);
            $label = changeIndexArray('id',$label);
            $product[$key]['label'] = $label;
        };

        $product = changeIndexArray('id',$product);
        

        foreach ($data as $key => $value) {
            if(isset($product[$value['product_id']])){
                $data[$key]['product'] = $product[$value['product_id']];  
            }else{
                $data[$key]['product'] = [];
            };
        };
        
        
        return $data;
        
        
    }

    public static function dealUpdate($data)
    {   

        

        

    }

    public static function dealRealDelete($data)
    {   

        
        
    }


    
}
