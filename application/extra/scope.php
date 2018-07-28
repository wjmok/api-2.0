<?php
/**
 * Created by 董博明.
 * Author: 董博明
 * Date: 2017/12/29
 * Time: 10:54
 */
return [
    'ACCESSKEY' => 'cUmB0IsFnwioWYnZ7s0hinQgYOI4kCGXleqSYNax',
    'SECRETKEY' => 'DXf0lcY1hkUnV3xTJZqvDiVkEL6E11_BVAuKWulZ',
    'BUCKET' => 'api-2',//上传的空间
    'DOMAIN'=>'http://qiniu.solelycloud.com/'//空间绑定的域名


    'one'=>[
        0=>[
            
            [30,60]=>[
                [0,1,2]=>'canChild',
            ],
            90=>[
                [0,1,2]=>'All',
            ],
        ],
        1=>[
            30=>[],
            60=>[
                [0,1,2]=>'canChild',
            ],
            90=>[
                [0,1,2]=>'All',
            ],
        ],
        2=>[
            30=>[],
            60=>[
                [0,1,2]=>'canChild',
            ],
            90=>[
                [0,1,2]=>'All',
            ],
        ],
	],

	'two'=>[
        0=>[
            0=>[],
            30=>[
            	[0]=>'isMe'
            ],
            60=>[
                [0,1,2]=>'canChild',
            ],
            90=>[
                [0,1,2]=>'All',
            ],
        ],
        1=>[
            0=>[],
            30=>[
            	[1]=>'isMe'
            ],
            60=>[
                [0,1,2]=>'canChild',
            ],
            90=>[
                [0,1,2]=>'All',
            ],
        ],
        2=>[
            0=>[],
            30=>[
            	[2]=>'isMe'
            ],
            60=>[
                [2]=>'canChild',
                [0,1]=>'All',
            ],
            90=>[
                [0,1,2]=>'All',
            ],
        ],
    ],

    'three'=>[
        0=>[
            0=>[],
            30=>[
            	[0]=>'canChild'
            ],
            60=>[
                [0,1,2]=>'canChild',
            ],
            90=>[
                [0,1,2]=>'All',
            ],
        ],
        1=>[
            0=>[],
            30=>[
            	[1]=>'canChild'
            ],
            60=>[
                [0,1,2]=>'canChild',
            ],
            90=>[
                [0,1,2]=>'All',
            ],
        ],
        2=>[
            0=>[],
            30=>[
            	[2]=>'canChild'
            ],
            60=>[
                [2]=>'canChild',
                [0,1]=>'All',
            ],
            90=>[
                [0,1,2]=>'All',
            ],
        ],
    ]


















];