webpackJsonp([3],{TJW4:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=a("Xxa5"),o=a.n(r),n=a("exGp"),i=a.n(n),s=a("SQnW"),l=a("dw4X"),c=a("1h8J"),m=a("HBZn"),u={data:function(){return{tableData:[],bookData:[],dialogFormVisible:!1,dialogPassVisible:!1,formLabelWidth:"120px",paginate:{count:0,currentPage:1,pagesize:10,is_page:!0},searchItem:{},imglimit:2,Form:{},standardForm:{book_status:""},sForm:{},BookOrderOptions:[{value:"1",label:"成功 "},{value:"2",label:"排队"},{value:"3",label:"取消"}],userKey:this.$store.getters.getUserInfo.username,token:this.$store.getters.getUserInfo.token,thirdapp_id:this.$store.getters.getUserInfo.thirdapp_id,defaultProps:{children:"child",label:"name",value:"id"}}},created:function(){this.initMainData(),this.initBookData()},components:{editor:s.a,imgup:l.a},filters:{},methods:{formatDate:function(e){var t=new Date(1e3*e);return Object(m.b)(t,"yyyy-MM-dd hh:mm")},initBookData:function(){var e=this;return i()(o.a.mark(function t(){var a,r,n;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return(r={}).token=(a=e).token,r.is_page=!1,t.prev=4,t.next=7,Object(c.d)(r);case 7:n=t.sent,t.next=14;break;case 10:t.prev=10,t.t0=t.catch(4),console.log(t.t0),Object(m.f)("网络故障","error");case 14:n&&(a.bookData=n.data);case 17:case"end":return t.stop()}},t,e,[[4,10]])}))()},initMainData:function(){var e=this;return i()(o.a.mark(function t(){var a,r,n;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return(a=e).tableData=[],(r=a.paginate).searchItem=Object(m.a)(a.searchItem),r.token=a.token,t.prev=5,t.next=8,Object(c.g)(r);case 8:n=t.sent,t.next=15;break;case 11:t.prev=11,t.t0=t.catch(5),console.log(t.t0),Object(m.f)("网络故障","error");case 15:n&&(a.paginate.count=n.data.total,a.tableData=n.data.data);case 18:case"end":return t.stop()}},t,e,[[5,11]])}))()},handleAdd:function(){this.Form=Object(m.a)(this.standardForm),this.sForm={},this.sForm.type="add",this.sForm.token=this.token,this.dialogFormVisible=!0},handleEdit:function(e){this.Form=Object(m.a)(e),this.sForm={},this.sForm.id=e.id,this.sForm.token=this.token,this.sForm.type="edit",this.dialogFormVisible=!0},handleEditBookstatus:function(e,t){console.log(e+t);this.sForm={},this.sForm.token=this.token,this.sForm.id=e,this.sForm.book_status=t,this.sForm.type="edit",this.onSubmit()},handleDel:function(e){var t=this;return i()(o.a.mark(function a(){var r;return o.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:(r=t).sForm={},r.sForm.id=e.id,r.sForm.token=r.token,r.sForm.type="del",r.onSubmit();case 6:case"end":return a.stop()}},a,t)}))()},onSubmit:function(){var e=this;return i()(o.a.mark(function t(){var a,r;return o.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if("edit"!=(a=e).sForm.type){t.next=17;break}return delete a.sForm.type,t.prev=3,t.next=6,Object(c.f)(a.sForm);case 6:r=t.sent,t.next=13;break;case 9:t.prev=9,t.t0=t.catch(3),console.log(t.t0),Object(m.f)("网络故障","error");case 13:a.sForm.type="edit",t.next=63;break;case 17:if("add"!=a.sForm.type){t.next=33;break}return delete a.sForm.type,t.prev=19,t.next=22,BookOrderAdd(a.sForm);case 22:r=t.sent,t.next=29;break;case 25:t.prev=25,t.t1=t.catch(19),console.log(t.t1),Object(m.f)("网络故障","error");case 29:a.sForm.type="add",t.next=63;break;case 33:if("upPassword"!=a.sForm.type){t.next=49;break}return delete a.sForm.type,t.prev=35,t.next=38,Object(c._18)(a.sForm);case 38:r=t.sent,t.next=45;break;case 41:t.prev=41,t.t2=t.catch(35),console.log(t.t2),Object(m.f)("网络故障","error");case 45:a.sForm.type="upPassword",t.next=63;break;case 49:if("del"!=a.sForm.type){t.next=63;break}return delete a.sForm.type,t.prev=51,t.next=54,Object(c.e)(a.sForm);case 54:r=t.sent,t.next=61;break;case 57:t.prev=57,t.t3=t.catch(51),console.log(t.t3),Object(m.f)("网络故障","error");case 61:a.sForm.type="del";case 63:r&&"success"==Object(m.g)(r)&&("upPassword"==a.sForm.type?a.dialogPassVisible=!1:a.dialogFormVisible=!1,a.initMainData());case 66:case"end":return t.stop()}},t,e,[[3,9],[19,25],[35,41],[51,57]])}))()},imgchange:function(e,t){return Object(m.e)(this,e,"sForm",t)}}},p={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("el-container",[a("el-header",[a("div",[e._v("\n      选择菜单查询文章列表:\n      "),a("el-cascader",{attrs:{options:e.bookData,props:e.defaultProps,"change-on-select":"",clearable:""},on:{change:function(t){e.searchItem.book_id=t[t.length-1],e.initMainData()}}})],1)]),e._v(" "),a("el-main",{staticStyle:{height:"700px",border:"2px solid #eee"}},[a("el-table",{staticStyle:{width:"100%"},attrs:{data:e.tableData}},[a("el-table-column",{attrs:{label:"预约订单编号",prop:"id"}}),e._v(" "),a("el-table-column",{attrs:{label:"预约人姓名",prop:"user.nickname"}}),e._v(" "),a("el-table-column",{attrs:{label:"预约类型",prop:"book.name"}}),e._v(" "),a("el-table-column",{attrs:{label:"创建预约时间",formatter:function(t){return e.formatDate(t.book_time)}}}),e._v(" "),a("el-table-column",{attrs:{label:"取消预约时间",formatter:function(t){return e.formatDate(t.cancel_time)}}}),e._v(" "),a("el-table-column",{attrs:{label:"预约订单状态"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-select",{attrs:{value:t.row.book_status.toString()},on:{change:function(a){t.row.book_status=a,e.handleEditBookstatus(t.row.id,a)}}},e._l(e.BookOrderOptions,function(e){return a("el-option",{attrs:{label:e.label,value:e.value}})}))]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"250"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{size:"small"},on:{click:function(a){e.handleDel(t.row)}}},[e._v("\n          删除\n          ")])]}}])})],1),e._v(" "),a("el-dialog",{attrs:{title:"预约订单信息",visible:e.dialogFormVisible,id:"dialog"},on:{"update:visible":function(t){e.dialogFormVisible=t}}},[a("el-form",{ref:"Form",attrs:{model:e.Form}},[a("el-form-item",{attrs:{label:"预约订单名称","label-width":e.formLabelWidth,prop:"name"}},[a("el-input",{on:{input:function(t){e.sForm.name=arguments[0]}},model:{value:e.Form.name,callback:function(t){e.$set(e.Form,"name",t)},expression:"Form.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"预约订单数量","label-width":e.formLabelWidth,prop:"BookOrderNum"}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.BookOrderNum=arguments[0]}},model:{value:e.Form.BookOrderNum,callback:function(t){e.$set(e.Form,"BookOrderNum",t)},expression:"Form.BookOrderNum"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"预约订单描述","label-width":e.formLabelWidth,prop:"description"}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.description=arguments[0]}},model:{value:e.Form.description,callback:function(t){e.$set(e.Form,"description",t)},expression:"Form.description"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"预约订单价格","label-width":e.formLabelWidth,prop:"price"}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.price=arguments[0]}},model:{value:e.Form.price,callback:function(t){e.$set(e.Form,"price",t)},expression:"Form.price"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"数量限制","label-width":e.formLabelWidth,prop:"limitNum"}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.limitNum=arguments[0]}},model:{value:e.Form.limitNum,callback:function(t){e.$set(e.Form,"limitNum",t)},expression:"Form.limitNum"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"内容编辑"}},[a("editor",{attrs:{defaultcontent:e.Form.content},on:{contentsave:function(t){e.sForm.content=t,e.Form.content=t}}})],1),e._v(" "),a("el-form-item",{attrs:{label:"主图上传",prop:"mainImg"}},[a("imgup",{attrs:{imglist:e.Form.mainImg,imglimit:e.imglimit},on:{imgchange:function(t){return e.imgchange(t,"mainImg")}}})],1),e._v(" "),a("el-form-item",{attrs:{label:"详情轮播上传",prop:"bannerImg"}},[a("imgup",{attrs:{imglist:e.Form.bannerImg,imglimit:e.imglimit},on:{imgchange:function(t){return e.imgchange(t,"bannerImg")}}})],1),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.onSubmit()}}},[e._v("确 定")])],1)],1)],1),e._v(" "),a("el-footer",[a("el-pagination",{attrs:{"current-page":e.paginate.currentPage,"page-sizes":[10,50,70,100],"page-size":e.paginate.pagesize,layout:"total, sizes, prev, pager, next, jumper",total:e.paginate.count},on:{"size-change":function(t){e.paginate.pagesize=t,e.initMainData()},"current-change":function(t){e.paginate.currentPage=t,e.initMainData()}}})],1)],1)],1)},staticRenderFns:[]};var d=a("VU/8")(u,p,!1,function(e){a("wQqB")},null,null);t.default=d.exports},wQqB:function(e,t){}});
//# sourceMappingURL=3.b55916f564eb89c48218.js.map