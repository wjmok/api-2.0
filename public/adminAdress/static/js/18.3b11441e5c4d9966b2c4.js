webpackJsonp([18],{"b+JS":function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=r("Xxa5"),n=r.n(o),i=r("exGp"),a=r.n(i),s=r("1h8J"),l=r("HBZn"),c=r("dw4X"),m=r("KAOm"),d=r("SQnW"),u=(r("0zyd"),{watch:{filterText:function(e){this.$refs.tree.filter(e)}},created:function(){this.initMainData()},components:{imgup:c.a,baseSelect:m.a,editor:d.a},data:function(){return{filterText:"",menudata:[],Form:{},standardForm:{name:"",listorder:"",banner:[],description:"",parentid:"",parent_array:[]},artStandardForm:{title:"",small_title:"",description:"",keywords:"",img:[],content:"请输入内容"},sForm:{},dialogFormVisible:!1,artDialogFormVisible:!1,imglimit:10,formLabelWidth:"120px",passKey:this.$store.getters.getUserInfo.password,token:this.$store.getters.getUserInfo.token,searchItem:{},defaultProps:{children:"child",label:"name",value:"id"}}},methods:{initMainData:function(){var e=this;return a()(n.a.mark(function t(){var r,o,i;return n.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return(o={}).token=(r=e).token,t.prev=3,t.next=6,Object(s.R)(o);case 6:i=t.sent,t.next=13;break;case 9:t.prev=9,t.t0=t.catch(3),console.log(t.t0),Object(l.f)("网络故障","error");case 13:i&&(r.menudata=i.data);case 16:case"end":return t.stop()}},t,e,[[3,9]])}))()},handleAddfirst:function(){var e=this;e.resetChecked(),e.Form=Object(l.a)(e.standardForm),e.sForm={},e.sForm.type="add",e.sForm.parentid="0",e.sForm.token=e.token,setTimeout(function(){e.dialogFormVisible=!0},500)},handleAddchild:function(){var e=this;e.Form=Object(l.a)(e.standardForm),e.sForm={};var t=e.getCheckedNodes();t&&(e.sForm.parentid=t.id,e.sForm.type="add",e.sForm.token=e.token,setTimeout(function(){e.dialogFormVisible=!0},500))},handleEdit:function(){var e=this;e.sForm={};var t=e.getCheckedNodes();t&&(e.Form=Object(l.a)(t),e.sForm.id=t.id,e.sForm.type="edit",e.sForm.token=e.token,e.Form.parentMenu_array=Object(l.d)(e.menudata,e.Form.parentid),setTimeout(function(){e.dialogFormVisible=!0},500))},deleteMenu:function(){this.sForm={};var e=this.getCheckedNodes();e&&(this.sForm.id=e.id,this.sForm.token=this.token,this.sForm.type="del",this.onSubmit())},addArticle:function(){var e=this;e.Form=Object(l.a)(e.artStandardForm),e.sForm={};var t=e.getCheckedNodes();t&&(e.sForm.menu_id=t.id,e.sForm.token=e.token,e.sForm.type="addArticle",setTimeout(function(){e.artDialogFormVisible=!0},500))},onSubmit:function(){var e=this;return a()(n.a.mark(function t(){var r,o,i;return n.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(r=e,console.log(r.sForm),"edit"!=r.sForm.type){t.next=34;break}if(r.sForm.parentid!=r.sForm.id){t.next=7;break}return Object(l.f)("父级不能与子集相同","error"),console.log("execute"),t.abrupt("return",!1);case 7:if(void 0==r.sForm.parentid&&(r.sForm.parentid="0"),!r.sForm.parentid){t.next=18;break}if(r.sForm.parentid!=r.sForm.id){t.next=15;break}return Object(l.f)("父级不能与子集相同","error"),console.log("execute"),t.abrupt("return",!1);case 15:void 0==r.sForm.parentid?r.sForm.parentid="0":(o=Object(l.c)(r.menudata,"id",r.sForm.parentid)).length>0&&o[0].parentid==r.sForm.id&&(r.sForm.cmenuid=o[0].id,delete r.sForm.parentid);case 18:return delete r.sForm.type,t.prev=20,t.next=23,Object(s._5)(r.sForm);case 23:i=t.sent,t.next=30;break;case 26:t.prev=26,t.t0=t.catch(20),console.log(t.t0),Object(l.f)("网络故障","error");case 30:r.sForm.type="edit",t.next=81;break;case 34:if("add"!=r.sForm.type){t.next=51;break}return delete r.sForm.type,t.prev=36,t.next=39,Object(s._3)(r.sForm);case 39:i=t.sent,t.next=46;break;case 42:t.prev=42,t.t1=t.catch(36),console.log(t.t1),Object(l.f)("网络故障","error");case 46:r.sForm.type="add",console.log(r.sForm),t.next=81;break;case 51:if("addArticle"!=r.sForm.type){t.next=67;break}return delete r.sForm.type,t.prev=53,t.next=56,Object(s.W)(r.sForm);case 56:i=t.sent,t.next=63;break;case 59:t.prev=59,t.t2=t.catch(53),console.log(t.t2),Object(l.f)("网络故障","error");case 63:r.sForm.type="addArticle",t.next=81;break;case 67:if("del"!=r.sForm.type){t.next=81;break}return delete r.sForm.type,t.prev=69,t.next=72,Object(s._4)(r.sForm);case 72:i=t.sent,t.next=79;break;case 75:t.prev=75,t.t3=t.catch(69),console.log(t.t3),Object(l.f)("网络故障","error");case 79:r.sForm.type="del";case 81:i&&"success"==Object(l.g)(i)&&("addArticle"==r.sForm.type?r.artDialogFormVisible=!1:r.dialogFormVisible=!1,r.resetChecked(),r.initMainData());case 84:case"end":return t.stop()}},t,e,[[20,26],[36,42],[53,59],[69,75]])}))()},filterNode:function(e,t){return!e||-1!==t.name.indexOf(e)},getCheckedNodes:function(){var e=this.$refs.tree.getCheckedNodes();if(e.length>1&&(Object(l.f)("请只选择一个菜单","warning"),this.resetChecked()),1==e.length)return e[0];0==e.length&&(Object(l.f)("请选择一个菜单","warning"),this.resetChecked())},resetChecked:function(){this.$refs.tree.setCheckedKeys([])},imgchange:function(e,t,r){return Object(l.e)(this,e,t,r)}}}),p={render:function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",[r("el-container",[r("el-header",[e._v("\n      关键词查询:\n      "),r("el-input",{staticStyle:{width:"260px!important"},attrs:{placeholder:"输入关键字进行过滤"},model:{value:e.filterText,callback:function(t){e.filterText=t},expression:"filterText"}})],1),e._v(" "),r("el-main",{staticStyle:{height:"500px",border:"2px solid #eee"}},[r("el-tree",{ref:"tree",staticClass:"filter-tree",attrs:{data:e.menudata,props:e.defaultProps,"node-key":"id","show-checkbox":"",accordion:"","check-strictly":"","filter-node-method":e.filterNode}}),e._v(" "),r("el-dialog",{attrs:{title:"菜单信息",visible:e.dialogFormVisible},on:{"update:visible":function(t){e.dialogFormVisible=t}}},[r("el-form",{attrs:{autocomplete:"off"}},[r("el-form-item",{attrs:{label:"菜单名称","label-width":e.formLabelWidth}},[r("el-input",{on:{input:function(t){e.sForm.name=arguments[0]}},model:{value:e.Form.name,callback:function(t){e.$set(e.Form,"name",t)},expression:"Form.name"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"描述","label-width":e.formLabelWidth}},[r("el-input",{on:{input:function(t){e.sForm.description=arguments[0]}},model:{value:e.Form.description,callback:function(t){e.$set(e.Form,"description",t)},expression:"Form.description"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"排序","label-width":e.formLabelWidth}},[r("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.listorder=arguments[0]}},model:{value:e.Form.listorder,callback:function(t){e.$set(e.Form,"listorder",t)},expression:"Form.listorder"}})],1),e._v(" "),"edit"==e.sForm.type?r("el-form-item",{attrs:{label:"父级菜单","label-width":e.formLabelWidth}},[r("el-cascader",{attrs:{options:e.menudata,props:e.defaultProps,"change-on-select":"",clearable:""},on:{change:function(t){e.sForm.parentid=t[t.length-1]}},model:{value:e.Form.parentMenu_array,callback:function(t){e.$set(e.Form,"parentMenu_array",t)},expression:"Form.parentMenu_array"}})],1):e._e(),e._v(" "),r("el-form-item",{attrs:{label:"banner图片上传"}},[r("imgup",{attrs:{imglist:e.Form.banner,imglimit:e.imglimit},on:{imgchange:function(t){e.imgchange(t,"sForm","banner")}}})],1),e._v(" "),r("el-button",{attrs:{type:"primary"},on:{click:function(t){e.onSubmit()}}},[e._v("确 定")])],1)],1),e._v(" "),r("el-dialog",{attrs:{title:"添加文章",visible:e.artDialogFormVisible},on:{"update:visible":function(t){e.artDialogFormVisible=t}}},[r("el-form",{attrs:{autocomplete:"off"}},[r("el-form-item",{attrs:{label:"文章名称","label-width":e.formLabelWidth}},[r("el-input",{on:{input:function(t){e.sForm.title=arguments[0]}},model:{value:e.Form.title,callback:function(t){e.$set(e.Form,"title",t)},expression:"Form.title"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"文章短标题","label-width":e.formLabelWidth}},[r("el-input",{on:{input:function(t){e.sForm.small_title=arguments[0]}},model:{value:e.Form.small_title,callback:function(t){e.$set(e.Form,"small_title",t)},expression:"Form.small_title"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"描述","label-width":e.formLabelWidth}},[r("el-input",{on:{input:function(t){e.sForm.description=arguments[0]}},model:{value:e.Form.description,callback:function(t){e.$set(e.Form,"description",t)},expression:"Form.description"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"关键词","label-width":e.formLabelWidth}},[r("el-input",{on:{input:function(t){e.sForm.keywords=arguments[0]}},model:{value:e.Form.keywords,callback:function(t){e.$set(e.Form,"keywords",t)},expression:"Form.keywords"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"图片上传"}},[r("imgup",{attrs:{imglist:e.Form.img,imglimit:e.imglimit},on:{imgchange:function(t){e.imgchange(t,"sForm","img")}}})],1),e._v(" "),r("el-form-item",{attrs:{label:"图文编辑"}},[r("editor",{attrs:{defaultcontent:e.Form.content},on:{contentsave:function(t){e.sForm.content=t,e.Form.content=t}}})],1),e._v(" "),r("el-button",{attrs:{type:"primary"},on:{click:function(t){e.onSubmit()}}},[e._v("确 定")])],1)],1)],1),e._v(" "),r("el-footer",{staticStyle:{"margin-top":"20px"}},[r("div",{staticClass:"buttons"},["chuncuiwangluo"==e.passKey?r("el-button",{on:{click:e.handleAddfirst}},[e._v("添加一级菜单")]):e._e(),e._v(" "),"chuncuiwangluo"==e.passKey?r("el-button",{on:{click:e.handleAddchild}},[e._v("添加子菜单")]):e._e(),e._v(" "),r("el-button",{on:{click:e.handleEdit}},[e._v("编辑")]),e._v(" "),"chuncuiwangluo"==e.passKey?r("el-button",{on:{click:e.deleteMenu}},[e._v("删除")]):e._e(),e._v(" "),r("el-button",{on:{click:e.addArticle}},[e._v("添加文章")])],1)])],1)],1)},staticRenderFns:[]},f=r("VU/8")(u,p,!1,null,null,null);t.default=f.exports}});
//# sourceMappingURL=18.3b11441e5c4d9966b2c4.js.map