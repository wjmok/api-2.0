webpackJsonp([20],{"R/pJ":function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=a("Xxa5"),n=a.n(i),r=a("exGp"),o=a.n(r),s=a("1h8J"),l=a("HBZn"),c=a("dw4X"),m=a("SQnW"),d={watch:{filterText:function(t){this.$refs.tree.filter(t)}},created:function(){this.initInvitationData(),this.initThemeData(),this.initMainData()},components:{imgup:c.a,editor:m.a},data:function(){return{filterText:"",InvitationData:[],themeData:[],tableData:[],dialogFormVisible:!1,recDialogFormVisible:!1,copyDialogFormVisible:!1,Form:{},standardForm:{title:"",description:"",content:"",listorder:"",mainImg:[],bannerImg:[],invitation_array:[]},copyStandardForm:{invitation_array:[]},recStandardForm:{theme_array:[],mainImg:[],name:"",description:""},sForm:{},imglimit:10,formLabelWidth:"120px",token:this.$store.getters.getUserInfo.token,searchItem:{},options:[],PosOptions:[],paginate:{count:0,currentPage:1,pagesize:10,is_page:!0},defaultProps:{children:"child",label:"name",value:"id"}}},methods:{initInvitationData:function(){var t=this;return o()(n.a.mark(function e(){var a,i,r;return n.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return(i={}).token=(a=t).token,e.prev=3,e.next=6,Object(s.u)(i);case 6:r=e.sent,e.next=13;break;case 9:e.prev=9,e.t0=e.catch(3),console.log(e.t0),Object(l.f)("网络故障","error");case 13:r&&(a.InvitationData=r.data);case 16:case"end":return e.stop()}},e,t,[[3,9]])}))()},initThemeData:function(){var t=this;return o()(n.a.mark(function e(){var a,i,r;return n.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return(i=(a=t).paginate).token=a.token,e.prev=3,e.next=6,Object(s.J)(i);case 6:r=e.sent,e.next=13;break;case 9:e.prev=9,e.t0=e.catch(3),console.log(e.t0),Object(l.f)("网络故障","error");case 13:r&&(a.themeData=r.data,console.log(a.themeData));case 16:case"end":return e.stop()}},e,t,[[3,9]])}))()},initMainData:function(){var t=this;return o()(n.a.mark(function e(){var a,i,r;return n.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return(a=t).tableData=[],(i=t.paginate).searchItem=Object(l.a)(a.searchItem),i.token=a.token,e.prev=5,e.next=8,Object(s.q)(i);case 8:r=e.sent,e.next=15;break;case 11:e.prev=11,e.t0=e.catch(5),console.log(e.t0),Object(l.f)("网络故障","error");case 15:r&&(a.paginate.count=r.data.total,a.tableData=r.data.data);case 18:case"end":return e.stop()}},e,t,[[5,11]])}))()},handleAdd:function(){this.Form=Object(l.a)(this.standardForm),this.sForm={},this.sForm.type="add",this.sForm.token=this.token,this.dialogFormVisible=!0},handleEdit:function(t){this.Form=Object(l.a)(t),this.sForm={},console.log(this.Form),this.sForm.id=t.id,this.sForm.token=this.token,this.sForm.type="edit";var e=[];Object(l.c)(this.InvitationData,this.Form.invitation_id,e),e.push(this.Form.invitation_id),this.Form.invitation_array=e,this.dialogFormVisible=!0},handleDel:function(t){var e=this;return o()(n.a.mark(function a(){var i;return n.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:(i=e).sForm={},i.sForm.id=t.id,i.sForm.token=i.token,i.sForm.type="del",i.onSubmit();case 6:case"end":return a.stop()}},a,e)}))()},handleCopy:function(t){this.sForm=Object(l.a)(t),this.Form=Object(l.a)(this.copyStandardForm),delete this.sForm.id,delete this.sForm.create_time,delete this.sForm.delete_time,delete this.sForm.invitation_id,delete this.sForm.thirdapp_id,delete this.sForm.update_time,delete this.sForm.publish_time,delete this.sForm.status,this.sForm.type="add",this.sForm.token=this.token,this.copyDialogFormVisible=!0},handleRec:function(t){this.sForm={},this.Form=Object(l.a)(this.recStandardForm),this.sForm.relation_id=t.id,this.sForm.token=this.token,this.sForm.type="Rec",this.recDialogFormVisible=!0},onSubmit:function(){var t=this;return o()(n.a.mark(function e(){var a,i;return n.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:if("edit"!=(a=t).sForm.type){e.next=17;break}return delete a.sForm.type,e.prev=3,e.next=6,Object(s.p)(a.sForm);case 6:i=e.sent,e.next=13;break;case 9:e.prev=9,e.t0=e.catch(3),console.log(e.t0),Object(l.f)("网络故障","error");case 13:a.sForm.type="edit",e.next=68;break;case 17:if("add"!=a.sForm.type){e.next=38;break}if(delete a.sForm.type,!("invitation_id"in a.sForm)){e.next=34;break}return e.prev=20,e.next=23,Object(s.n)(a.sForm);case 23:i=e.sent,e.next=30;break;case 26:e.prev=26,e.t1=e.catch(20),console.log(e.t1),Object(l.f)("网络故障","error");case 30:a.sForm.type="add",e.next=35;break;case 34:Object(l.f)("请选择类别","error");case 35:e.next=68;break;case 38:if("del"!=a.sForm.type){e.next=54;break}return delete a.sForm.type,e.prev=40,e.next=43,Object(s.o)(a.sForm);case 43:i=e.sent,e.next=50;break;case 46:e.prev=46,e.t2=e.catch(40),console.log(e.t2),Object(l.f)("网络故障","error");case 50:a.sForm.type="del",e.next=68;break;case 54:if("Rec"!=a.sForm.type){e.next=68;break}return delete a.sForm.type,e.prev=56,e.next=59,Object(s.D)(a.sForm);case 59:i=e.sent,e.next=66;break;case 62:e.prev=62,e.t3=e.catch(56),console.log(e.t3),Object(l.f)("网络故障","error");case 66:a.sForm.type="Rec";case 68:i&&"success"==Object(l.g)(i)&&(a.dialogFormVisible=!1,a.recDialogFormVisible=!1,a.copyDialogFormVisible=!1,a.initMainData());case 71:case"end":return e.stop()}},e,t,[[3,9],[20,26],[40,46],[56,62]])}))()},imgchange:function(t){return Object(l.e)(this,t,"sForm","img")},imgContentChange:function(t){return Object(l.e)(this,t,"sForm","bannerImg")}}},u={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("el-container",[a("el-header",[a("div",[t._v("\n        选择菜单查询文章列表:\n        "),a("el-cascader",{attrs:{options:t.InvitationData,props:t.defaultProps,"change-on-select":"",clearable:""},on:{change:function(e){t.searchItem.invitation_id=e[e.length-1],t.initMainData()}}})],1)]),t._v(" "),a("el-main",[a("div",[a("el-button",{attrs:{type:"primary"},on:{click:function(e){t.handleAdd()}}},[t._v("添加帖子")])],1)]),t._v(" "),a("el-main",{staticStyle:{height:"600px",border:"2px solid #eee"}},[a("div",[a("el-table",{staticStyle:{width:"100%"},attrs:{data:t.tableData}},[a("el-table-column",{attrs:{label:"标题",prop:"title"}}),t._v(" "),a("el-table-column",{attrs:{label:"描述",prop:"description"}}),t._v(" "),a("el-table-column",{attrs:{label:"创建用户昵称",prop:"user.nickname"},scopedSlots:t._u([{key:"default",fn:function(e){return[e.row.user?a("span",[t._v(t._s(e.row.user.username))]):t._e(),t._v(" "),e.row.user?t._e():a("span",[t._v("平台")])]}}])}),t._v(" "),a("el-table-column",{attrs:{label:"创建时间",prop:"create_time"}}),t._v(" "),a("el-table-column",{attrs:{label:"操作"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("el-button",{attrs:{size:"small"},on:{click:function(a){t.handleEdit(e.row)}}},[t._v("编辑")]),t._v(" "),a("el-button",{attrs:{size:"small"},on:{click:function(a){t.handleDel(e.row)}}},[t._v("删除")]),t._v(" "),a("el-button",{attrs:{size:"small"},on:{click:function(a){t.handleCopy(e.row)}}},[t._v("复制到")])]}}])})],1)],1),t._v(" "),a("el-dialog",{attrs:{title:"文章信息",visible:t.dialogFormVisible},on:{"update:visible":function(e){t.dialogFormVisible=e}}},[a("el-form",{attrs:{autocomplete:"off"}},[a("el-form-item",{attrs:{label:"标题","label-width":t.formLabelWidth}},[a("el-input",{on:{input:function(e){t.sForm.title=arguments[0]}},model:{value:t.Form.title,callback:function(e){t.$set(t.Form,"title",e)},expression:"Form.title"}})],1),t._v(" "),a("el-form-item",{attrs:{label:"排序","label-width":t.formLabelWidth}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(e){t.sForm.listorder=arguments[0]}},model:{value:t.Form.listorder,callback:function(e){t.$set(t.Form,"listorder",e)},expression:"Form.listorder"}})],1),t._v(" "),a("el-form-item",{attrs:{label:"所属分类","label-width":t.formLabelWidth}},[a("el-cascader",{attrs:{options:t.InvitationData,props:t.defaultProps,"change-on-select":"",clearable:""},on:{change:function(e){t.sForm.invitation_id=e[e.length-1]}},model:{value:t.Form.invitation_array,callback:function(e){t.$set(t.Form,"invitation_array",e)},expression:"Form.invitation_array"}})],1),t._v(" "),a("el-form-item",{attrs:{label:"描述","label-width":t.formLabelWidth}},[a("el-input",{on:{input:function(e){t.sForm.description=arguments[0]}},model:{value:t.Form.description,callback:function(e){t.$set(t.Form,"description",e)},expression:"Form.description"}})],1),t._v(" "),a("el-form-item",{attrs:{label:"主图片上传"}},[a("imgup",{attrs:{imglist:t.Form.mainImg,imglimit:t.imglimit},on:{imgchange:function(e){t.imgchange(e,"mainImg")}}})],1),t._v(" "),a("el-form-item",{attrs:{label:"详情多图上传"}},[a("imgup",{attrs:{imglist:t.Form.bannerImg,imglimit:t.imglimit},on:{imgchange:function(e){t.imgchange(e,"bannerImg")}}})],1),t._v(" "),a("el-form-item",{attrs:{label:"图文编辑"}},[a("editor",{attrs:{defaultcontent:t.Form.content},on:{contentsave:function(e){t.sForm.content=e,t.Form.content=e}}})],1),t._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(e){t.onSubmit()}}},[t._v("确 定")])],1)],1),t._v(" "),a("el-dialog",{attrs:{title:"复制到",visible:t.copyDialogFormVisible},on:{"update:visible":function(e){t.copyDialogFormVisible=e}}},[a("el-form",{attrs:{autocomplete:"off"}},[a("el-form-item",{attrs:{label:"菜单名称","label-width":t.formLabelWidth}},[a("el-cascader",{attrs:{options:t.InvitationData,props:t.defaultProps,"change-on-select":"",clearable:""},on:{change:function(e){t.sForm.invitation_id=e[e.length-1]}},model:{value:t.Form.invitation_array,callback:function(e){t.$set(t.Form,"invitation_array",e)},expression:"Form.invitation_array"}})],1),t._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(e){t.onSubmit()}}},[t._v("确 定")])],1)],1),t._v(" "),a("el-dialog",{attrs:{title:"推送到",visible:t.recDialogFormVisible},on:{"update:visible":function(e){t.recDialogFormVisible=e}}},[a("el-form",{attrs:{autocomplete:"off"}},[a("el-form-item",{attrs:{label:"名称","label-width":t.formLabelWidth}},[a("el-input",{on:{input:function(e){t.sForm.name=arguments[0]}},model:{value:t.Form.name,callback:function(e){t.$set(t.Form,"name",e)},expression:"Form.name"}})],1),t._v(" "),a("el-form-item",{attrs:{label:"描述","label-width":t.formLabelWidth}},[a("el-input",{on:{input:function(e){t.sForm.description=arguments[0]}},model:{value:t.Form.description,callback:function(e){t.$set(t.Form,"description",e)},expression:"Form.description"}})],1),t._v(" "),a("el-form-item",{attrs:{label:"主图片上传"}},[a("imgup",{attrs:{imglist:t.Form.mainImg,imglimit:t.imglimit},on:{imgchange:t.imgchange}})],1),t._v(" "),a("el-form-item",{attrs:{label:"推送推荐位名称","label-width":t.formLabelWidth}},[a("el-cascader",{attrs:{options:t.themeData,props:t.defaultProps,"change-on-select":"",clearable:""},on:{change:function(e){t.sForm.theme_id=e[e.length-1]}},model:{value:t.Form.theme_array,callback:function(e){t.$set(t.Form,"theme_array",e)},expression:"Form.theme_array"}})],1),t._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(e){t.onSubmit()}}},[t._v("确 定")])],1)],1)],1),t._v(" "),a("el-footer",{staticStyle:{"margin-top":"20px"}},[a("div",[a("el-pagination",{attrs:{"current-page":t.paginate.currentPage,"page-sizes":[10,50,70,100],"page-size":t.paginate.pagesize,layout:"total, sizes, prev, pager, next, jumper",total:t.paginate.count},on:{"size-change":function(e){t.paginate.pagesize=e,t.initMainData()},"current-change":function(e){t.paginate.currentPage=e,t.initMainData()}}})],1)])],1)],1)},staticRenderFns:[]},p=a("VU/8")(d,u,!1,null,null,null);e.default=p.exports}});
//# sourceMappingURL=20.cd2b1a23561a732111dd.js.map