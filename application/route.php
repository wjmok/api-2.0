<?php

/**
 * 路由注册
 *
 * 以下代码为了尽量简单，没有使用路由分组
 * 实际上，使用路由分组可以简化定义
 * 并在一定程度上提高路由匹配的效率
 */
// 写完代码后对着路由表看，能否不看注释就知道这个接口的意义
use think\Route;
require_once (dirname(__FILE__).'/route/test.Route.php');
require_once (dirname(__FILE__).'/route/muyaju.Route.php');
require_once (dirname(__FILE__).'/route/themaze.Route.php');
require_once (dirname(__FILE__).'/route/guoxingyd.Route.php');
require_once (dirname(__FILE__).'/route/xigongda.Route.php');
require_once (dirname(__FILE__).'/route/yigangjfsc.Route.php');
require_once (dirname(__FILE__).'/route/zhqy.Route.php');
require_once (dirname(__FILE__).'/route/jianshenfang.Route.php');
require_once (dirname(__FILE__).'/route/base/cms.Route.php');

//计时器接口
/*分钟计时器，暂定每5分钟执行一次*/
Route::get('api/:version/Timer/TimerByMins','api/:version.Timer/timerByMins');

//ThirdApp
//添加商户企业信息
Route::post('api/:version/ThirdApp/AddUser', 'api/:version.ThirdApp/AddThirdUser');
//修改商户企业信息
Route::post('api/:version/ThirdApp/UpdateUser', 'api/:version.ThirdApp/UpdateThirdUser');
//软删除商户企业信息
Route::post('api/:version/ThirdApp/delUser', 'api/:version.ThirdApp/DelThirdUser');
//获取商户信息列表
Route::post('api/:version/ThirdApp/ListUser', 'api/:version.ThirdApp/getAllThirdUser');
//获取商户关联admin用户信息
Route::post('api/:version/ThirdApp/ListAdminUserList', 'api/:version.ThirdApp/getAdminThirdUser');
//获取商户关联user用户信息
Route::post('api/:version/ThirdApp/ListUserList', 'api/:version.ThirdApp/getUserThirdUser');
//获取指定商户信息
Route::post('api/:version/ThirdApp/userInfo', 'api/:version.ThirdApp/getThirdUserInfo');
//物理删除用户所有信息
Route::post('api/:version/ThirdApp/truedelUser', 'api/:version.ThirdApp/TrueDelThirdUser');



//menu菜单接口

//cms端
//添加菜单
Route::post('api/:version/Menu/Add','api/:version.Menu/addMenu');
//修改菜单
Route::post('api/:version/Menu/Edit','api/:version.Menu/editMenu');
//软删除菜单
Route::post('api/:version/Menu/Del','api/:version.Menu/delMenu');
//获取菜单层级
Route::post('api/:version/Menu/GetTree','api/:version.Menu/getMenuTree');
//获取菜单列表
Route::post('api/:version/Menu/GetList','api/:version.Menu/getMenuList');
//获取指定菜单信息
Route::post('api/:version/Menu/GetInfo','api/:version.Menu/getMenuinfo');

//客户端
//user获取menu层级树
Route::post('api/:version/UserMenu/GetTree', 'api/:version.UserMenu/getMenuTree');
//user获取指定menu信息
Route::post('api/:version/UserMenu/GetInfo', 'api/:version.UserMenu/getMenuInfo');



//article&articleContent

//CMS端
//添加文章
Route::post('api/:version/Article/Add', 'api/:version.Article/addArticle');
//修改文章
Route::post('api/:version/Article/Edit', 'api/:version.Article/editArticle');
//软删除文章
Route::post('api/:version/Article/Del', 'api/:version.Article/delArticle');
//获取文章列表
Route::post('api/:version/Article/GetList', 'api/:version.Article/getArticleList');
//获取指定文章信息
Route::post('api/:version/Article/GetInfo', 'api/:version.Article/getArticleinfo');

//user获取文章列表
Route::post('api/:version/UserArticle/GetList', 'api/:version.UserArticle/getArticleList');
//user获取指定文章信息
Route::post('api/:version/UserArticle/GetInfo', 'api/:version.UserArticle/getArticleinfo');
//文章浏览次数
Route::post('api/:version/UserArticle/SetViewCount', 'api/:version.UserArticle/setCount');
//获取首页文章（指定类别、指定数量）
Route::post('api/:version/UserArticle/GetHomeArticle','api/:version.UserArticle/getHomeArticle');



//category
//新增类别
Route::post('api/:version/Category/Add','api/:version.Category/addCategory');
//更新类别
Route::post('api/:version/Category/Edit','api/:version.Category/editCategory');
//软删除类别
Route::post('api/:version/Category/Del','api/:version.Category/delCategory');
//获取类别层级
Route::post('api/:version/Category/GetTree','api/:version.Category/getCategoryTree');
//获取类别列表
Route::post('api/:version/Category/GetList','api/:version.Category/getCategoryList');
//获取指定类别信息
Route::post('api/:version/Category/GetInfo','api/:version.Category/getCategoryinfo');

//客户端
//user获取category层级树
Route::post('api/:version/UserCategory/GetTree', 'api/:version.UserCategory/getTree');



//product商品

//cms后端接口
//添加商品
Route::post('api/:version/Product/Add','api/:version.Product/addProduct');
//修改商品信息
Route::post('api/:version/Product/Edit','api/:version.Product/editProduct');
//软删除
Route::post('api/:version/Product/Del','api/:version.Product/delProduct');
//获取商品列表
Route::post('api/:version/Product/GetList','api/:version.Product/getAll');
//获取指定商品信息
Route::post('api/:version/Product/GetInfo','api/:version.Product/getProductinfo');

//客户端接口
//获取商品列表
Route::post('api/:version/UserProduct/GetSortList','api/:version.UserProduct/getSortList');



//ProductModel商品型号管理

//cms后端接口
//添加型号
Route::post('api/:version/ProductModel/Add','api/:version.ProductModel/addModel');
//编辑型号
Route::post('api/:version/ProductModel/Edit','api/:version.ProductModel/editModel');
//删除型号
Route::post('api/:version/ProductModel/Del','api/:version.ProductModel/delModel');
//获取型号列表
Route::post('api/:version/ProductModel/GetList','api/:version.ProductModel/getList');
//获取指定型号
Route::post('api/:version/ProductModel/GetInfo','api/:version.ProductModel/getInfo');
//开启型号团购功能
Route::post('api/:version/ProductModel/SetGroup','api/:version.ProductModel/setGroup');
//关闭团购功能
Route::post('api/:version/ProductModel/CancelGroup','api/:version.ProductModel/cancelGroup');
//团购退款
Route::post('api/:version/ProductModel/RefundGroup','api/:version.ProductModel/refundGroup');

//客户端接口
//获取商品型号列表
Route::post('api/:version/UserProduct/GetList','api/:version.UserProduct/getList');
//获取指定型号
Route::post('api/:version/UserProduct/GetInfo','api/:version.UserProduct/getInfo');
//根据商品价格，销量，综合 降升排序
Route::post('api/:version/UserProduct/ProductSort','api/:version.UserProduct/sortByAttr');







//Miss 404
//Miss 路由开启后，默认的普通模式也将无法访问
Route::miss('api/v1.Miss/miss');



//微信公众号接口
/*公众号接口入口*/
Route::any('api/:version/WxController/:thirdapp_id','api/:version.WxController/index');
/*公众号网页授权*/
Route::any('api/:version/WxAuth/:thirdapp_id/:code','api/:version.WxAuth/index');



//SMS短信接口-限前端

//阿里云
//短信接口
Route::post('api/:version/SMSAli/Send','api/:version.SMSAli/sendMsg');
//保存用户电话信息
Route::post('api/:version/SMSAli/SavePhone','api/:version.SMSAli/updateUserPhone');

//腾讯云
//短信接口
Route::post('api/:version/SMSTencet/Send','api/:version.SMSTencet/sendMsg');
//保存用户电话信息
Route::post('api/:version/SMSTencet/SavePhone','api/:version.SMSTencet/updateUserPhone');


//Image管理
//上传，验证token
Route::post('api/:version/upload', 'api/:version.UpLoad/upload');
//删除功能，后端使用，验证token
Route::post('api/:version/delpic', 'api/:version.Image/deletepic');
//一键复制生成menu
Route::post('api/:version/Menu/CopyMenu','api/:version.Menu/copyMenu');



//获取二维码
//场景二维码，用于分销逻辑
//二维码参数由前端传递
Route::post('api/:version/UserQRcode/GetCode','api/:version.UserQRcode/getCode');


//Token
Route::post('api/:version/token/user', 'api/:version.Token/getToken');

Route::post('api/:version/token/verify', 'api/:version.Token/verifyToken');
//不想把所有查询都写在一起，所以增加by_user，很好的REST与RESTFul的区别
//Route::get('api/:version/order/by_user', 'api/:version.Order/getSummaryByUser');
//Route::get('api/:version/order/paginate', 'api/:version.Order/getSummary');



//微信支付相关

//客户端
/*Pay支付*/ 
Route::post('api/:version/pay/pre_order','api/:version.Pay/getPreOrder');
/*微信支付回调函数*/ 
Route::post('api/:version/pay/notify','api/:version.WXPayReturn/receiveNotify');
// Route::post('api/:version/pay/re_notify', 'api/:version.Pay/redirectNotify');
// Route::post('api/:version/pay/concurrency', 'api/:version.Pay/notifyConcurrency');

//cms端
/*回调函数*/ 
Route::post('api/:version/pay/refund','api/:version.MerchantPay/refund');
//todo...缺少证书



//user相关

//user操作
/*新增用户-预留*/
// Route::post('api/:version/User/AddUser','api/:version.User/addUserinfo');
/*更新用户信息，需要验证token*/
Route::post('api/:version/User/EditUser','api/:version.User/editUserinfo');
/*获取用户信息,需要验证token*/
Route::post('api/:version/User/getUser','api/:version.User/getUserinfo');
/*检查是否绑定手机,需要验证token*/
Route::post('api/:version/User/checkPhone','api/:version.User/checkPhone');
/*用户签到，记录积分*/
Route::post('api/:version/User/Signin','api/:version.User/signin');

//cms操作
/*修改用户信息*/
Route::post('api/:version/AdminUser/Edit','api/:version.AdminUser/editUser');
/*软删除*/
Route::post('api/:version/AdminUser/Del','api/:version.AdminUser/delUser');
/*彻底删除-预留*/
// Route::post('api/:version/AdminUser/DeleteUser','api/:version.AdminUser/truedelUser');
/*获取用户列表*/
Route::post('api/:version/AdminUser/getList','api/:version.AdminUser/getUserList');
/*获取指定用户信息*/
Route::post('api/:version/AdminUser/getInfo','api/:version.AdminUser/getUserinfo');



//Order相关

//user操作
/*新增订单，需要验证token*/
Route::post('api/:version/AddOrder', 'api/:version.Order/addOrder');
/*获取指定用户订单列表，需要验证token*/
Route::post('api/:version/getUserOrder','api/:version.Order/getAllByUser');
/*获取指定订单信息*/
Route::post('api/:version/getOrder','api/:version.Order/getOrderinfo');
/*取消订单,传递订单id，需要验证token*/
Route::post('api/:version/CancelOrder','api/:version.Order/cancelOrder');
/*删除订单*/
Route::post('api/:version/DeleteOrder','api/:version.Order/delOrder');
/*获取待收货订单*/
Route::post('api/:version/getReceiveOrder','api/:version.Order/getReceiveOrder');
/*订单收货*/
Route::post('api/:version/ReceiveOrder','api/:version.Order/receiveOrder');

//cms相关操作
/*更新订单内容，cms端使用此接口可以改变订单状态*/
Route::post('api/:version/EditOrder','api/:version.OrderDeal/editOrder');
/*软删除*/
Route::post('api/:version/DelOrder','api/:version.OrderDeal/delOrder');
/*获取订单列表*/
Route::post('api/:version/getAllOrder','api/:version.OrderDeal/getAll');
/*获取指定订单信息*/
Route::post('api/:version/getOrderInfo','api/:version.OrderDeal/getOrderinfo');



//shop_cart购物车-仅前端使用
/*记录购物车*/
Route::post('api/:version/saveCart','api/:version.ShopCart/saveCart');
/*读取购物车信息*/ 
Route::post('api/:version/getShopCart','api/:version.ShopCart/getShopCart');



//address相关-仅前端使用
/*新增地址，需要验证token*/
Route::post('api/:version/AddAddress','api/:version.Address/addAddress');
/*更新地址信息，需要验证token*/
Route::post('api/:version/EditAddress','api/:version.Address/editAddress');
/*软删除*/
Route::post('api/:version/DelAddress','api/:version.Address/delAddress');
/*获取指定用户地址列表，需要验证token*/
Route::post('api/:version/getUserAddress','api/:version.Address/getAllByUser');
/*获取指定地址信息*/
Route::post('api/:version/getAddress','api/:version.Address/getAddressInfo');
/*设置默认地址,需要提交新地址id，，需要验证token*/
Route::post('api/:version/SetAddress','api/:version.Address/setAddress');
/*获取指定用户的默认地址，需要验证token*/
Route::post('api/:version/getDefaultAddress','api/:version.Address/getDefaultAddress');



//remark相关

//客户端接口
/*新增评论，需要验证token*/
Route::post('api/:version/UserRemark/add','api/:version.UserRemark/addRemark');
/*获取指定商品评论*/
Route::post('api/:version/UserRemark/getList','api/:version.UserRemark/getList');

//cms端接口
/*获取指定商品评论*/
Route::post('api/:version/Remark/getRemark','api/:version.Remark/getRemarkList');
/*软删除*/
Route::post('api/:version/Remark/Del','api/:version.Remark/delRemark');



//message留言

//user操作
/*新增留言，需要验证token*/
Route::post('api/:version/UserMessage/addMessage','api/:version.UserMessage/addMessage');

//cms操作
//获取留言列表
Route::post('api/:version/Message/getMessageList','api/:version.Message/getMessageList');
//软删除留言
Route::post('api/:version/Message/delMessage','api/:version.Message/deleteMessage');
//处理留言
Route::post('api/:version/Message/dealMessage','api/:version.Message/dealMessage');



//theme主题
//themeType分为article,product...

//CMS端
/*新增主题*/
Route::post('api/:version/Theme/Add','api/:version.Theme/AddTheme');
/*编辑主题*/
Route::post('api/:version/Theme/Edit','api/:version.Theme/EditTheme');
/*删除主题*/
Route::post('api/:version/Theme/Del','api/:version.Theme/DelTheme');
/*获取主题层级*/
Route::post('api/:version/Theme/GetTree','api/:version.Theme/getThemeTree');
/*获取指定主题信息*/
Route::post('api/:version/Theme/GetInfo', 'api/:version.Theme/getThemeinfo');

//客户端
/*获取指定主题信息*/
Route::post('api/:version/UserTheme/GetInfo', 'api/:version.UserTheme/getThemeinfo');

 
//theme_content主题内容

//CMS端
/*新增内容*/
Route::post('api/:version/ThemeContent/Add','api/:version.ThemeContent/addContent');
/*编辑内容*/
Route::post('api/:version/ThemeContent/Edit', 'api/:version.ThemeContent/editContent');
/*删除内容*/
Route::post('api/:version/ThemeContent/Del', 'api/:version.ThemeContent/delContent');
/*获取内容列表*/
Route::post('api/:version/ThemeContent/GetList','api/:version.ThemeContent/getContentList');
/*获取指定内容信息*/
Route::post('api/:version/ThemeContent/GetInfo', 'api/:version.ThemeContent/getContentinfo');

//客户端
/*获取内容列表*/
Route::post('api/:version/UserContent/GetList','api/:version.UserThemeContent/getContentList');
/*获取指定内容信息*/
Route::post('api/:version/UserContent/GetInfo', 'api/:version.UserThemeContent/getContentinfo');
/*获取首页内容（指定类别、指定数量）*/
Route::post('api/:version/UserContent/GetHomeTheme','api/:version.UserThemeContent/getHomeTheme');



//member会员

//cms端
/*编辑会员*/
Route::post('api/:version/Member/Edit','api/:version.Member/editMember');
/*删除会员*/
Route::post('api/:version/Member/Del','api/:version.Member/delMember');
/*获取会员列表*/
Route::post('api/:version/Member/GetList','api/:version.Member/getList');
/*获取指定会员信息*/
Route::post('api/:version/Member/GetInfo','api/:version.Member/getInfo');
/*获取会员tree*/
Route::post('api/:version/Member/GetTree','api/:version.Member/getTree');

//客户端
/*注册会员*/
Route::post('api/:version/UserMember/Add','api/:version.UserMember/addMember');
/*编辑会员*/
Route::post('api/:version/UserMember/Edit','api/:version.UserMember/editMember');
/*获取个人会员信息*/
Route::post('api/:version/UserMember/GetInfo','api/:version.UserMember/getInfo');
/*获取我的直接下级*/
Route::post('api/:version/UserMember/GetMyTeam','api/:version.UserMember/getMyTeam');
/*获取我的下级tree*/
Route::post('api/:version/UserMember/GetMyTree','api/:version.UserMember/getMyTree');



//member_card会员卡

//cms端
/*新增会员卡*/
Route::post('api/:version/MemberCard/Add','api/:version.MemberCard/addCard');
/*编辑会员卡*/
Route::post('api/:version/MemberCard/Edit','api/:version.MemberCard/editCard');
/*删除会员卡*/
Route::post('api/:version/MemberCard/Del','api/:version.MemberCard/delCard');
/*获取会员卡列表*/
Route::post('api/:version/MemberCard/GetList','api/:version.MemberCard/getList');
/*获取指定会员卡信息*/
Route::post('api/:version/MemberCard/GetInfo','api/:version.MemberCard/getInfo');

//客户端
/*获取会员卡列表*/
Route::post('api/:version/UserMemberCard/GetList','api/:version.UserMemberCard/getList');
/*获取指定会员卡*/
Route::post('api/:version/UserMemberCard/GetInfo','api/:version.UserMemberCard/getInfo');



//user_card用户购买的会员卡

//cms端
/*编辑会员卡*/
Route::post('api/:version/CustomerCard/Edit','api/:version.CustomerCard/editCard');
/*删除会员卡*/
Route::post('api/:version/CustomerCard/Del','api/:version.CustomerCard/delCard');
/*获取会员卡列表*/
Route::post('api/:version/CustomerCard/GetList','api/:version.CustomerCard/getList');
/*获取指定会员卡信息*/
Route::post('api/:version/CustomerCard/GetInfo','api/:version.CustomerCard/getInfo');

//客户端
// 购买会员卡
// Route::post('api/:version/UserCard/Add','api/:version.UserCard/addCard');
Route::post('api/:version/AddOrder','api/:version.Order/addOrder');
/*获取个人会员卡列表*/
Route::post('api/:version/UserCard/GetList','api/:version.UserCard/getList');
/*获取指定会员卡信息*/
Route::post('api/:version/UserCard/GetInfo','api/:version.UserCard/getInfo');



//book预约

//cms端
/*新增预约*/
Route::post('api/:version/Book/Add','api/:version.Book/addBook');
/*编辑预约*/
Route::post('api/:version/Book/Edit','api/:version.Book/editBook');
/*删除预约*/
Route::post('api/:version/Book/Del','api/:version.Book/delBook');
/*获取预约列表*/
Route::post('api/:version/Book/GetList','api/:version.Book/getList');
/*获取指定预约信息*/
Route::post('api/:version/Book/GetInfo','api/:version.Book/getInfo');

//客户端
/*获取预约列表*/
Route::post('api/:version/UserBook/GetList','api/:version.UserBook/getList');
/*获取指定预约信息*/
Route::post('api/:version/UserBook/GetInfo','api/:version.UserBook/getInfo');



//book_order预约订单

//cms端
/*编辑预约订单*/
Route::post('api/:version/BookOrder/Edit','api/:version.BookOrder/editOrder');
/*删除预约订单*/
Route::post('api/:version/BookOrder/Del','api/:version.BookOrder/delOrder');
/*获取预约订单列表*/
Route::post('api/:version/BookOrder/GetList','api/:version.BookOrder/getList');
/*获取指定预约订单信息*/
Route::post('api/:version/BookOrder/GetInfo','api/:version.BookOrder/getInfo');

//客户端
/*预约订单*/
Route::post('api/:version/UserBookOrder/Add','api/:version.UserBookOrder/addOrder');
/*取消预约*/
Route::post('api/:version/UserBookOrder/Cancel','api/:version.UserBookOrder/cancelOrder');
/*获取个人预约订单列表*/
Route::post('api/:version/UserBookOrder/GetList','api/:version.UserBookOrder/getList');
/*获取指定预约订单信息*/
Route::post('api/:version/UserBookOrder/GetInfo','api/:version.UserBookOrder/getInfo');



//coupon优惠券

//cms端
/*新增优惠券*/
Route::post('api/:version/Coupon/Add','api/:version.Coupon/addCoupon');
/*编辑优惠券*/
Route::post('api/:version/Coupon/Edit','api/:version.Coupon/editCoupon');
/*删除优惠券*/
Route::post('api/:version/Coupon/Del','api/:version.Coupon/delCoupon');
/*获取优惠券列表*/
Route::post('api/:version/Coupon/GetList','api/:version.Coupon/getList');
/*获取指定优惠券信息*/
Route::post('api/:version/Coupon/GetInfo','api/:version.Coupon/getInfo');

//客户端
/*获取优惠券列表*/
Route::post('api/:version/CouponInfo/GetList','api/:version.CouponInfo/getList');
/*获取指定优惠券信息*/
Route::post('api/:version/CouponInfo/GetInfo','api/:version.CouponInfo/getInfo');



//user_coupon用户所有优惠券

//cms端
/*编辑用户优惠券*/
Route::post('api/:version/CustomerCoupon/Edit','api/:version.CustomerCoupon/editCoupon');
/*删除用户优惠券*/
Route::post('api/:version/CustomerCoupon/Del','api/:version.CustomerCoupon/delCoupon');
/*获取用户优惠券列表*/
Route::post('api/:version/CustomerCoupon/GetList','api/:version.CustomerCoupon/getList');
/*获取指定用户优惠券信息*/
Route::post('api/:version/CustomerCoupon/GetInfo','api/:version.CustomerCoupon/getInfo');

//客户端
/*领取优惠券*/
Route::post('api/:version/UserCoupon/Receive','api/:version.UserCoupon/receiveCoupon');
/*获取个人优惠券列表*/
Route::post('api/:version/UserCoupon/GetList','api/:version.UserCoupon/getList');
/*获取指定优惠券信息*/
Route::post('api/:version/UserCoupon/GetInfo','api/:version.UserCoupon/getInfo');



//invitation_menu帖子类型

//cms端
//添加帖子类型
Route::post('api/:version/InvitationMenu/Add', 'api/:version.InvitationMenu/addMenu');
//修改帖子类型
Route::post('api/:version/InvitationMenu/Edit', 'api/:version.InvitationMenu/editMenu');
//软删除帖子类型
Route::post('api/:version/InvitationMenu/Del', 'api/:version.InvitationMenu/delMenu');
//获取帖子类型层级
Route::post('api/:version/InvitationMenu/GetTree', 'api/:version.InvitationMenu/getTree');
//获取帖子类型列表
Route::post('api/:version/InvitationMenu/GetList', 'api/:version.InvitationMenu/getList');
//获取指定帖子类型信息
Route::post('api/:version/InvitationMenu/GetInfo', 'api/:version.InvitationMenu/getInfo');

//客户端
//user获取帖子类型层级树
Route::post('api/:version/UserInvitationMenu/GetTree', 'api/:version.UserInvitationMenu/getTree');
//user获取帖子类型列表
Route::post('api/:version/UserInvitationMenu/GetList', 'api/:version.UserInvitationMenu/getList');
//user获取指定帖子类型信息
Route::post('api/:version/UserInvitationMenu/GetInfo', 'api/:version.UserInvitationMenu/getInfo');



//invitation帖子管理

//cms端
//发布帖子
Route::post('api/:version/Invitation/Add','api/:version.Invitation/addInvitation');
//编辑帖子
Route::post('api/:version/Invitation/Edit','api/:version.Invitation/editInvitation');
//删除帖子
Route::post('api/:version/Invitation/Del','api/:version.Invitation/delInvitation');
//获取帖子列表
Route::post('api/:version/Invitation/GetList','api/:version.Invitation/getList');
//获取指定帖子
Route::post('api/:version/Invitation/GetInfo','api/:version.Invitation/getInfo');

//客户端
//发布帖子
Route::post('api/:version/UserInvitation/Add','api/:version.UserInvitation/addInvitation');
//编辑帖子
Route::post('api/:version/UserInvitation/Edit','api/:version.UserInvitation/editInvitation');
//删除帖子
Route::post('api/:version/UserInvitation/Del','api/:version.UserInvitation/delInvitation');
//获取帖子列表
Route::post('api/:version/UserInvitation/GetList','api/:version.UserInvitation/getList');
//获取我的帖子列表
Route::post('api/:version/UserInvitation/GetUserList','api/:version.UserInvitation/getUserList');
//获取指定帖子
Route::post('api/:version/UserInvitation/GetInfo','api/:version.UserInvitation/getInfo');



//invitation_remark回帖

//cms端
//删除评论
Route::post('api/:version/InvitationRemark/Del','api/:version.InvitationRemark/delRemark');
//获取评论列表
Route::post('api/:version/InvitationRemark/GetList','api/:version.InvitationRemark/getList');
//获取指定评论
Route::post('api/:version/InvitationRemark/GetInfo','api/:version.InvitationRemark/getInfo');

//客户端
//新增评论
Route::post('api/:version/UserInvitationRemark/Add','api/:version.UserInvitationRemark/addRemark');
//删除评论
Route::post('api/:version/UserInvitationRemark/Del','api/:version.UserInvitationRemark/delRemark');
//获取评论列表
Route::post('api/:version/UserInvitationRemark/GetList','api/:version.UserInvitationRemark/getList');
//获取指定评论
Route::post('api/:version/UserInvitationRemark/GetInfo','api/:version.UserInvitationRemark/getInfo');
//我的帖子被回复的记录
Route::post('api/:version/UserInvitationRemark/MyRemarkList','api/:version.UserInvitationRemark/getRemarkForMe');



//invitation_favor评论点赞-客户端专有
//点赞
Route::post('api/:version/UserInvitationFavor/Add','api/:version.UserInvitationFavor/addFavor');
//取消点赞
Route::post('api/:version/UserInvitationFavor/Cancel','api/:version.UserInvitationFavor/CancelFavor');
//获取点赞列表
Route::post('api/:version/UserInvitationFavor/GetList','api/:version.UserInvitationFavor/getList');
//我的获赞记录
Route::post('api/:version/UserInvitationFavor/MyFavorList','api/:version.UserInvitationFavor/getFavorForMe');



//流水记录

//cms端
/*增加现金流水*/
/*增加余额流水*/
/*增加积分流水*/
Route::post('api/:version/FlowScore/Add','api/:version.FlowScore/addFlow');
/*获取积分流水列表*/
Route::post('api/:version/FlowScore/GetList','api/:version.FlowScore/getList');

//客户端
//获取现金流水
Route::post('api/:version/UserFlowMoney/GetList','api/:version.UserFlowMoney/getList');
//获取余额流水
Route::post('api/:version/UserFlowBalance/GetList','api/:version.UserFlowBalance/getList');
//获取积分流水
Route::post('api/:version/UserFlowScore/GetList','api/:version.UserFlowScore/getList');



//提现申请

//cms
//同意提现-线下处理
Route::post('api/:version/ApplyCash/AgreeApply','api/:version.ApplyCash/agreeApply');
//拒绝提现
Route::post('api/:version/ApplyCash/RefuseApply','api/:version.ApplyCash/refuseApply');
//删除提现申请
Route::post('api/:version/ApplyCash/Del','api/:version.ApplyCash/delApply');
//获取提现列表
Route::post('api/:version/ApplyCash/GetList','api/:version.ApplyCash/getList');
//获取指定提现信息
Route::post('api/:version/ApplyCash/GetInfo','api/:version.ApplyCash/getInfo');

//客户端
//申请提现
Route::post('api/:version/UserApplyCash/Add','api/:version.UserApplyCash/addApply');
//获取提现列表
Route::post('api/:version/UserApplyCash/GetList','api/:version.UserApplyCash/getList');
//获取指定提现信息
Route::post('api/:version/UserApplyCash/GetInfo','api/:version.UserApplyCash/getInfo');



//wxmenu微信公众号自定义菜单接口

//cms端
//添加菜单
Route::post('api/:version/Wxmenu/Add','api/:version.Wxmenu/addMenu');
//修改菜单
Route::post('api/:version/Wxmenu/Edit','api/:version.Wxmenu/editMenu');
//软删除菜单
Route::post('api/:version/Wxmenu/Del','api/:version.Wxmenu/delMenu');
//获取菜单层级
Route::post('api/:version/Wxmenu/GetTree','api/:version.Wxmenu/getTree');
//获取菜单列表
Route::post('api/:version/Wxmenu/GetList','api/:version.Wxmenu/getList');
//获取指定菜单信息
Route::post('api/:version/Wxmenu/GetInfo','api/:version.Wxmenu/getInfo');
/*一键推送公众号菜单*/
Route::any('api/:version/Wxmenu/PushMenu/:thirdapp_id','api/:version.WxController/pushMenu');



//collection收藏功能
//客户端
/*新增收藏*/
Route::post('api/:version/Collection/Add','api/:version.Collection/addCollection');
/*取消收藏*/
Route::post('api/:version/Collection/Cancel','api/:version.Collection/cancelCollection');
/*收藏列表*/
Route::post('api/:version/Collection/GetList','api/:version.Collection/getList');



//merchant商家
//cms端
/*新增商家*/
Route::post('api/:version/Merchant/Add','api/:version.Merchant/addMerchant');
/*编辑商家*/
Route::post('api/:version/Merchant/Edit','api/:version.Merchant/editMerchant');
/*删除商家*/
Route::post('api/:version/Merchant/Del','api/:version.Merchant/delMerchant');
/*获取商家列表*/
Route::post('api/:version/Merchant/GetList','api/:version.Merchant/getList');
/*获取指定商家*/
Route::post('api/:version/Merchant/GetInfo','api/:version.Merchant/getInfo');

//客户端
/*获取商家列表*/
Route::post('api/:version/UserMerchant/GetList','api/:version.UserMerchant/getList');
/*获取指定商家*/
Route::post('api/:version/UserMerchant/GetInfo','api/:version.UserMerchant/getInfo');