webpackJsonp([5],{CKn3:function(e,t){},"e+pZ":function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=a("Xxa5"),s=a.n(r),n=a("exGp"),o=a.n(n),l=a("SQnW"),i=a("dw4X"),p=a("1h8J"),c=a("HBZn"),u={data:function(){return{mainData:[],formLabelWidth:"120px",paginate:{count:0,currentPage:1,pagesize:10,is_page:!0},searchForm:{order_step:"",transport_status:"",pay_status:""},searchItem:{},imglimit:2,Form:{},standardForm:{},stepOptions:[{value:"0",label:"正常下单"},{value:"1",label:"申请撤单"},{value:"2",label:"同意撤单"},{value:"3",label:"完结"}],transportOptions:[{value:"0",label:"未发货"},{value:"1",label:"已发货"},{value:"2",label:"已收货"}],payOptions:[{value:"0",label:"未支付"},{value:"1",label:"已支付"},{value:"2",label:"货到付款"}],sForm:{},passForm:{password:""},userKey:this.$store.getters.getUserInfo.username,token:this.$store.getters.getUserInfo.token,thirdapp_id:this.$store.getters.getUserInfo.thirdapp_id}},created:function(){this.initMainData()},components:{editor:l.a,imgup:i.a},methods:{initMainData:function(){var e=this;return o()(s.a.mark(function t(){var a,r,n;return s.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return(a=e).mainData=[],(r=a.paginate).token=a.token,r.searchItem=Object(c.a)(a.searchItem),t.prev=5,t.next=8,Object(p._8)(r);case 8:n=t.sent,t.next=15;break;case 11:t.prev=11,t.t0=t.catch(5),console.log(t.t0),Object(c.f)("网络故障","error");case 15:n&&(a.paginate.count=n.data.total,a.mainData=n.data.data);case 18:case"end":return t.stop()}},t,e,[[5,11]])}))()},sendGoods:function(e){this.sForm={},this.sForm.id=e.id,this.sForm.orderinfo={},this.sForm.orderinfo.transport_status="1",this.sForm.token=this.token,this.sForm.type="edit",this.onSubmit()},Refund:function(e){this.sForm={},this.sForm.id=e.id,this.sForm.orderinfo={},this.sForm.orderinfo.order_step="2",this.sForm.token=this.token,this.sForm.type="edit",this.onSubmit()},completeOrder:function(e){this.sForm={},this.sForm.id=e.id,this.sForm.orderinfo={},this.sForm.orderinfo.order_step="3",this.sForm.token=this.token,this.sForm.type="edit",this.onSubmit()},onSubmit:function(){var e=this;return o()(s.a.mark(function t(){var a,r;return s.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if("edit"!=(a=e).sForm.type){t.next=17;break}return delete a.sForm.type,t.prev=3,t.next=6,Object(p._7)(a.sForm);case 6:r=t.sent,t.next=13;break;case 9:t.prev=9,t.t0=t.catch(3),console.log(t.t0),Object(c.f)("网络故障","error");case 13:a.sForm.type="edit",t.next=31;break;case 17:if("del"!=a.sForm.type){t.next=31;break}return delete a.sForm.type,t.prev=19,t.next=22,Object(p._6)(a.sForm);case 22:r=t.sent,t.next=29;break;case 25:t.prev=25,t.t1=t.catch(19),console.log(t.t1),Object(c.f)("网络故障","error");case 29:a.sForm.type="del";case 31:r&&"success"==Object(c.g)(r)&&a.initMainData();case 34:case"end":return t.stop()}},t,e,[[3,9],[19,25]])}))()}}},_={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("el-container",[a("el-header",[a("el-select",{attrs:{placeholder:"请选择订单状态",clearable:""},on:{change:function(t){t?e.searchItem.order_step=t:delete e.searchItem.order_step,e.initMainData()}},model:{value:e.searchForm.order_step,callback:function(t){e.$set(e.searchForm,"order_step",t)},expression:"searchForm.order_step"}},e._l(e.stepOptions,function(e){return a("el-option",{attrs:{label:e.label,value:e.value}})})),e._v(" "),a("el-select",{attrs:{placeholder:"请选择物流状态",clearable:""},on:{change:function(t){t?e.searchItem.transport_status=t:delete e.searchItem.transport_status,e.initMainData()}},model:{value:e.searchForm.transport_status,callback:function(t){e.$set(e.searchForm,"transport_status",t)},expression:"searchForm.transport_status"}},e._l(e.transportOptions,function(e){return a("el-option",{attrs:{label:e.label,value:e.value}})})),e._v(" "),a("el-select",{attrs:{placeholder:"请选择支付状态状态",clearable:""},on:{change:function(t){t?e.searchItem.pay_status=t:delete e.searchItem.pay_status,e.initMainData()}},model:{value:e.searchForm.pay_status,callback:function(t){e.$set(e.searchForm,"pay_status",t)},expression:"searchForm.pay_status"}},e._l(e.payOptions,function(e){return a("el-option",{attrs:{label:e.label,value:e.value}})})),e._v(" "),a("el-input",{staticStyle:{width:"200px"},attrs:{placeholder:"请输入用户ID",clearable:""},on:{blur:function(t){t.target._value?e.searchItem.user_id=t.target._value:delete e.searchItem.user_id,e.initMainData()}}})],1),e._v(" "),a("el-main",{staticStyle:{height:"700px",border:"2px solid #eee"}},[a("el-table",{staticStyle:{width:"100%"},attrs:{data:e.mainData}},[a("el-table-column",{attrs:{type:"expand"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-form",{staticClass:"demo-table-expand",attrs:{"label-position":"left",inline:""}},[t.row.snap_address.length>0?a("div",[a("el-form-item",{attrs:{label:"收件人姓名"}},[a("span",[e._v(e._s(t.row.snap_address.name))])]),e._v(" "),a("el-form-item",{attrs:{label:"收件人电话"}},[a("span",[e._v(e._s(t.row.snap_address.phone))])]),e._v(" "),a("el-form-item",{attrs:{label:"收件人地址 "}},[a("span",[e._v("\n                  "+e._s(t.row.snap_address.province)+"\n                  "+e._s(t.row.snap_address.city)+"\n                  "+e._s(t.row.snap_address.country)+"\n                  "+e._s(t.row.snap_address.detail)+"\n                ")])])],1):e._e(),e._v(" "),a("el-form-item",{attrs:{label:"是否评论 "}},[a("span",[e._v(e._s("0"==t.row.remark_num?"未评论":"已评论"))])]),e._v(" "),a("el-form-item",{attrs:{label:"下单时间"}},[a("span",[e._v(e._s(t.row.create_time))])]),e._v(" "),t.row.item[0].snap_product.product?a("el-form-item",{attrs:{label:"产品名称"}},[e._v("\n              \n                "+e._s(t.row.item[0].snap_product.product.name)+"\n              \n            ")]):e._e(),e._v(" "),t.row.item[0].snap_product.category?a("el-form-item",{attrs:{label:"产品类别"}},[e._v("\n                "+e._s(t.row.item[0].snap_product.category.name)+"\n            ")]):e._e(),e._v(" "),t.row.puser?a("el-form-item",{attrs:{label:"推荐人信息"}},[e._v("\n              姓名:"),a("span",[e._v(e._s(t.row.puser.username))]),e._v("\n              电话:"),a("span",[e._v(e._s(t.row.puser.phone))]),e._v("\n              user_id:"),a("span",[e._v(e._s(t.row.puser.id))])]):e._e(),e._v(" "),a("el-form-item",{attrs:{label:"passage1"}},[e.scope.row.passage1?[e.scope.row.passage1.url?a("div",[a("img",{staticStyle:{width:"90px"},attrs:{src:e.scope.row.headimgurl[0].url}})]):a("span",[e._v(e._s(t.row.passage1))])]:e._e()],2),e._v(" "),a("el-form-item",{attrs:{label:"passage2"}},[a("span",[e._v(e._s(t.row.passage2))])]),e._v(" "),a("div",[a("el-form-item",{attrs:{label:"订单详情"}},[a("el-table",{staticStyle:{width:"100%"},attrs:{data:t.row.item}},[a("el-table-column",{attrs:{label:"产品型号名称",prop:"snap_product.name",width:"180"}}),e._v(" "),a("el-table-column",{attrs:{label:"产品单价",prop:"snap_product.price",width:"180"}}),e._v(" "),a("el-table-column",{attrs:{label:"购买数量",prop:"count",width:"180"}})],1)],1)],1)],1)]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"订单编号",prop:"order_no"}}),e._v(" "),a("el-table-column",{attrs:{label:"总价",prop:"total_price"}}),e._v(" "),a("el-table-column",{attrs:{label:"用户ID",prop:"user_id"}}),e._v(" "),a("el-table-column",{attrs:{label:"手机号",prop:"user.phone"}}),e._v(" "),a("el-table-column",{attrs:{label:"微信昵称",prop:"user.nickname"}}),e._v(" "),a("el-table-column",{attrs:{label:"创建时间",prop:"create_time"}}),e._v(" "),a("el-table-column",{attrs:{label:"支付状态",width:"120"},scopedSlots:e._u([{key:"default",fn:function(t){return["0"==t.row.pay_status?a("span",[e._v("\n            未支付\n          ")]):e._e(),e._v(" "),"1"==t.row.pay_status?a("span",[e._v("\n            已支付\n          ")]):e._e(),e._v(" "),"2"==t.row.pay_status?a("span",[e._v("\n            货到付款\n          ")]):e._e()]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"订单状态",width:"120"},scopedSlots:e._u([{key:"default",fn:function(t){return["0"==t.row.order_step?a("span",[e._v("\n            正常下单\n          ")]):e._e(),e._v(" "),"1"==t.row.order_step?a("span",[e._v("\n            申请撤单\n          ")]):e._e(),e._v(" "),"2"==t.row.order_step?a("span",[e._v("\n            同意撤单\n          ")]):e._e(),e._v(" "),"3"==t.row.order_step?a("span",[e._v("\n            完结\n          ")]):e._e()]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"物流状态",width:"120"},scopedSlots:e._u([{key:"default",fn:function(t){return["0"==t.row.transport_status?a("span",[e._v("\n            未发货\n          ")]):e._e(),e._v(" "),"1"==t.row.transport_status?a("span",[e._v("\n            已发货\n          ")]):e._e(),e._v(" "),"2"==t.row.transport_status?a("span",[e._v("\n            已收货\n          ")]):e._e()]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"250"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{size:"small"},on:{click:function(a){e.sendGoods(t.row)}}},[e._v("\n            发货\n          ")]),e._v(" "),a("el-button",{attrs:{size:"small"},on:{click:function(a){e.Refund(t.row)}}},[e._v("\n            撤单\n          ")]),e._v(" "),a("el-button",{attrs:{size:"small"},on:{click:function(a){e.completeOrder(t.row)}}},[e._v("\n            完结\n          ")])]}}])})],1)],1),e._v(" "),a("el-footer",[a("el-pagination",{attrs:{"current-page":e.paginate.currentPage,"page-sizes":[10,50,70,100],"page-size":e.paginate.pagesize,layout:"total, sizes, prev, pager, next, jumper",total:e.paginate.count},on:{"size-change":function(t){e.paginate.pagesize=t,e.initMainData()},"current-change":function(t){e.paginate.currentPage=t,e.initMainData()}}})],1)],1)],1)},staticRenderFns:[]};var d=a("VU/8")(u,_,!1,function(e){a("CKn3")},null,null);t.default=d.exports}});
//# sourceMappingURL=5.084364c1133e05d198f0.js.map