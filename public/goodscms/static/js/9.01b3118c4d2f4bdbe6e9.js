webpackJsonp([9],{"6ysv":function(e,t){},ZOKc:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=a("Xxa5"),n=a.n(o),r=a("exGp"),i=a.n(r),s=a("SQnW"),l=a("dw4X"),c=a("1h8J"),m=a("HBZn"),p={data:function(){return{tableData:[],dialogFormVisible:!1,dialogPassVisible:!1,formLabelWidth:"120px",paginate:{count:0,currentPage:1,pagesize:10,is_page:!0},searchItem:{},imglimit:2,Form:{},standardForm:{name:"",description:"",content:"",discount:"",deduction:"",mainImg:[],bannerImg:[],deadline:"",passage1:"",passage2:"",passage3:"",passage4:""},sForm:{},cardOptions:[{value:"1",label:"分值卡"},{value:"2",label:"时间卡"},{value:"3",label:"单次卡"}],userKey:this.$store.getters.getUserInfo.username,token:this.$store.getters.getUserInfo.token,thirdapp_id:this.$store.getters.getUserInfo.thirdapp_id}},created:function(){this.initMainData()},components:{editor:s.a,imgup:l.a},methods:{initMainData:function(){var e=this;return i()(n.a.mark(function t(){var a,o,r;return n.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return(a=e).tableData=[],(o=a.paginate).token=a.token,t.prev=4,t.next=7,Object(c.k)(o);case 7:r=t.sent,t.next=14;break;case 10:t.prev=10,t.t0=t.catch(4),console.log(t.t0),Object(m.f)("网络故障","error");case 14:r&&(a.paginate.count=r.data.total,a.tableData=r.data.data);case 17:case"end":return t.stop()}},t,e,[[4,10]])}))()},handleAdd:function(){this.Form=Object(m.a)(this.standardForm),this.sForm={},this.sForm.type="add",this.sForm.token=this.token,this.dialogFormVisible=!0},handleEdit:function(e){this.Form=Object(m.a)(e),this.sForm={},this.Form.deadline=1e3*e.deadline,this.sForm.id=e.id,this.sForm.token=this.token,this.sForm.type="edit",this.dialogFormVisible=!0},handleDel:function(e){var t=this;return i()(n.a.mark(function a(){var o;return n.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:(o=t).sForm={},o.sForm.id=e.id,o.sForm.token=o.token,o.sForm.type="del",o.onSubmit();case 6:case"end":return a.stop()}},a,t)}))()},onSubmit:function(){var e=this;return i()(n.a.mark(function t(){var a,o;return n.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if("edit"!=(a=e).sForm.type){t.next=17;break}return delete a.sForm.type,t.prev=3,t.next=6,Object(c.j)(a.sForm);case 6:o=t.sent,t.next=13;break;case 9:t.prev=9,t.t0=t.catch(3),console.log(t.t0),Object(m.f)("网络故障","error");case 13:a.sForm.type="edit",t.next=63;break;case 17:if("add"!=a.sForm.type){t.next=33;break}return delete a.sForm.type,t.prev=19,t.next=22,Object(c.h)(a.sForm);case 22:o=t.sent,t.next=29;break;case 25:t.prev=25,t.t1=t.catch(19),console.log(t.t1),Object(m.f)("网络故障","error");case 29:a.sForm.type="add",t.next=63;break;case 33:if("upPassword"!=a.sForm.type){t.next=49;break}return delete a.sForm.type,t.prev=35,t.next=38,Object(c._18)(a.sForm);case 38:o=t.sent,t.next=45;break;case 41:t.prev=41,t.t2=t.catch(35),console.log(t.t2),Object(m.f)("网络故障","error");case 45:a.sForm.type="upPassword",t.next=63;break;case 49:if("del"!=a.sForm.type){t.next=63;break}return delete a.sForm.type,t.prev=51,t.next=54,Object(c.i)(a.sForm);case 54:o=t.sent,t.next=61;break;case 57:t.prev=57,t.t3=t.catch(51),console.log(t.t3),Object(m.f)("网络故障","error");case 61:a.sForm.type="del";case 63:o&&"success"==Object(m.g)(o)&&("upPassword"==a.sForm.type?a.dialogPassVisible=!1:a.dialogFormVisible=!1,a.initMainData());case 66:case"end":return t.stop()}},t,e,[[3,9],[19,25],[35,41],[51,57]])}))()},imgchange:function(e,t){return Object(m.e)(this,e,"sForm",t)},formatDate:function(e){var t=new Date(1e3*e);return Object(m.b)(t,"yyyy-MM-dd hh:mm")}}},d={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("el-container",[a("el-header",[a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.handleAdd()}}},[e._v("创建优惠券")])],1),e._v(" "),a("el-main",{staticStyle:{height:"700px",border:"2px solid #eee"}},[a("el-table",{staticStyle:{width:"100%"},attrs:{data:e.tableData}},[a("el-table-column",{attrs:{label:"优惠券",prop:"name"}}),e._v(" "),a("el-table-column",{attrs:{label:"描述",prop:"description"}}),e._v(" "),a("el-table-column",{attrs:{label:"有效期",formatter:function(t){return e.formatDate(t.deadline)}}}),e._v(" "),a("el-table-column",{attrs:{label:"折扣",prop:"discount"}}),e._v(" "),a("el-table-column",{attrs:{label:"抵减额",prop:"deduction"}}),e._v(" "),a("el-table-column",{attrs:{label:"创建时间",prop:"create_time"}}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"250"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{size:"small"},on:{click:function(a){e.handleEdit(t.row)}}},[e._v("\n            编辑\n          ")]),e._v(" "),a("el-button",{attrs:{size:"small"},on:{click:function(a){e.handleDel(t.row)}}},[e._v("\n          删除\n          ")])]}}])})],1),e._v(" "),a("el-dialog",{attrs:{title:"优惠券信息",visible:e.dialogFormVisible,id:"dialog"},on:{"update:visible":function(t){e.dialogFormVisible=t}}},[a("el-form",{ref:"Form",attrs:{model:e.Form}},[a("el-form-item",{attrs:{label:"优惠券名称","label-width":e.formLabelWidth,prop:"name"}},[a("el-input",{on:{input:function(t){e.sForm.name=arguments[0]}},model:{value:e.Form.name,callback:function(t){e.$set(e.Form,"name",t)},expression:"Form.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"优惠券描述","label-width":e.formLabelWidth,prop:"description"}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.description=arguments[0]}},model:{value:e.Form.description,callback:function(t){e.$set(e.Form,"description",t)},expression:"Form.description"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"优惠券折扣","label-width":e.formLabelWidth,prop:"discount"}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.discount=arguments[0]}},model:{value:e.Form.discount,callback:function(t){e.$set(e.Form,"discount",t)},expression:"Form.discount"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"优惠券抵减","label-width":e.formLabelWidth,prop:"deduction"}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.deduction=arguments[0]}},model:{value:e.Form.deduction,callback:function(t){e.$set(e.Form,"deduction",t)},expression:"Form.deduction"}})],1),e._v(" "),a("el-form-item",{attrs:{prop:"deadline","label-width":e.formLabelWidth,label:"有效期"}},[a("el-date-picker",{attrs:{type:"date",placeholder:"有效期"},on:{change:function(t){var a=new Date(t).getTime();e.sForm.deadline=a/1e3}},model:{value:e.Form.deadline,callback:function(t){e.$set(e.Form,"deadline",t)},expression:"Form.deadline"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"内容编辑"}},[a("editor",{attrs:{defaultcontent:e.Form.content},on:{contentsave:function(t){e.sForm.content=t,e.Form.content=t}}})],1),e._v(" "),a("el-form-item",{attrs:{label:"主图上传",prop:"mainImg"}},[a("imgup",{attrs:{imglist:e.Form.mainImg,imglimit:e.imglimit},on:{imgchange:function(t){return e.imgchange(t,"mainImg")}}})],1),e._v(" "),a("el-form-item",{attrs:{label:"详情轮播上传",prop:"bannerImg"}},[a("imgup",{attrs:{imglist:e.Form.bannerImg,imglimit:e.imglimit},on:{imgchange:function(t){return e.imgchange(t,"bannerImg")}}})],1),e._v(" "),a("el-form-item",{attrs:{label:"预留字段1","label-width":e.formLabelWidth,prop:"passage1"}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.passage1=arguments[0]}},model:{value:e.Form.passage1,callback:function(t){e.$set(e.Form,"passage1",t)},expression:"Form.passage1"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"预留字段2","label-width":e.formLabelWidth,prop:"passage2"}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.passage2=arguments[0]}},model:{value:e.Form.passage2,callback:function(t){e.$set(e.Form,"passage2",t)},expression:"Form.passage2"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"预留字段3","label-width":e.formLabelWidth,prop:"passage3"}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.passage3=arguments[0]}},model:{value:e.Form.passage3,callback:function(t){e.$set(e.Form,"passage3",t)},expression:"Form.passage3"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"预留字段4","label-width":e.formLabelWidth,prop:"passage4"}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.passage4=arguments[0]}},model:{value:e.Form.passage4,callback:function(t){e.$set(e.Form,"passage4",t)},expression:"Form.passage4"}})],1),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.onSubmit()}}},[e._v("确 定")])],1)],1)],1),e._v(" "),a("el-footer",[a("el-pagination",{attrs:{"current-page":e.paginate.currentPage,"page-sizes":[10,50,70,100],"page-size":e.paginate.pagesize,layout:"total, sizes, prev, pager, next, jumper",total:e.paginate.count},on:{"size-change":function(t){e.paginate.pagesize=t,e.initMainData()},"current-change":function(t){e.paginate.currentPage=t,e.initMainData()}}})],1)],1)],1)},staticRenderFns:[]};var u=a("VU/8")(p,d,!1,function(e){a("6ysv")},null,null);t.default=u.exports}});
//# sourceMappingURL=9.01b3118c4d2f4bdbe6e9.js.map