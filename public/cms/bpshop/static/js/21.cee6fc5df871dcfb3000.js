webpackJsonp([21],{uwYn:function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=r("Xxa5"),n=r.n(o),a=r("exGp"),i=r.n(a),s=r("1h8J"),l=r("HBZn"),c=r("dw4X"),d=r("KAOm"),m=r("SQnW"),u=(r("0zyd"),{watch:{filterText:function(e){this.$refs.tree.filter(e)}},created:function(){this.initMainData()},components:{imgup:c.a,baseSelect:d.a,editor:m.a},data:function(){return{filterText:"",MainData:[],Form:{},standardForm:{name:"",listorder:"",img:[],description:"",parentid:"",parent_array:[]},artStandardForm:{title:"",small_title:"",description:"",keywords:"",img:[],content:"请输入内容"},sForm:{},dialogFormVisible:!1,artDialogFormVisible:!1,imglimit:1,formLabelWidth:"120px",passKey:this.$store.getters.getUserInfo.password,token:this.$store.getters.getUserInfo.token,searchItem:{},defaultProps:{children:"child",label:"name",value:"id"}}},methods:{initMainData:function(){var e=this;return i()(n.a.mark(function t(){var r,o,a;return n.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return(o={}).token=(r=e).token,t.prev=3,t.next=6,Object(s._3)(o);case 6:a=t.sent,t.next=13;break;case 9:t.prev=9,t.t0=t.catch(3),console.log(t.t0),Object(l.f)("网络故障","error");case 13:a&&(r.MainData=a.data);case 16:case"end":return t.stop()}},t,e,[[3,9]])}))()},handleAddfirst:function(){var e=this;e.resetChecked(),e.Form=Object(l.a)(e.standardForm),e.sForm={},e.sForm.type="add",e.sForm.parentid="0",e.sForm.token=e.token,setTimeout(function(){e.dialogFormVisible=!0},500)},handleAddchild:function(){var e=this;e.Form=Object(l.a)(e.standardForm),e.sForm={};var t=e.getCheckedNodes();t&&(e.sForm.parentid=t.id,e.sForm.type="add",e.sForm.token=e.token,setTimeout(function(){e.dialogFormVisible=!0},500))},handleEdit:function(){var e=this;e.sForm={};var t=e.getCheckedNodes();if(t){e.Form=Object(l.a)(t),e.sForm.id=t.id,e.sForm.type="edit",e.sForm.token=e.token;var r=[];Object(l.c)(e.MainData,e.Form.parentid,r),r.push(e.Form.parentid),e.Form.parentCategory_array=r,console.log(e.Form),setTimeout(function(){e.dialogFormVisible=!0},500)}},deleteCategory:function(){this.sForm={};var e=this.getCheckedNodes();e&&(this.sForm.id=e.id,this.sForm.token=this.token,this.sForm.type="del",this.onSubmit())},onSubmit:function(){var e=this;return i()(n.a.mark(function t(){var r,o,a;return n.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(r=e,console.log(r.sForm),"edit"!=r.sForm.type){t.next=32;break}if(!r.sForm.parentid){t.next=14;break}if(r.sForm.parentid!=r.sForm.id){t.next=10;break}return Object(l.f)("父级不能与子集相同","error"),console.log("execute"),t.abrupt("return",!1);case 10:o=[],Object(l.d)(r.MainData,o,"id",r.sForm.parentid),console.log(o[0]),o.length>0&&o[0].parentid==r.sForm.id&&(r.sForm.cCategoryid=o[0].id,delete r.sForm.parentid);case 14:return void 0==r.sForm.parentid&&(r.sForm.parentid="0"),delete r.sForm.type,t.prev=18,t.next=21,Object(s._2)(r.sForm);case 21:a=t.sent,t.next=28;break;case 24:t.prev=24,t.t0=t.catch(18),console.log(t.t0),Object(l.f)("网络故障","error");case 28:r.sForm.type="edit",t.next=62;break;case 32:if("add"!=r.sForm.type){t.next=48;break}return delete r.sForm.type,t.prev=34,t.next=37,Object(s._0)(r.sForm);case 37:a=t.sent,t.next=44;break;case 40:t.prev=40,t.t1=t.catch(34),console.log(t.t1),Object(l.f)("网络故障","error");case 44:r.sForm.type="add",t.next=62;break;case 48:if("del"!=r.sForm.type){t.next=62;break}return delete r.sForm.type,t.prev=50,t.next=53,Object(s._1)(r.sForm);case 53:a=t.sent,t.next=60;break;case 56:t.prev=56,t.t2=t.catch(50),console.log(t.t2),Object(l.f)("网络故障","error");case 60:r.sForm.type="del";case 62:a&&"success"==Object(l.g)(a)&&(r.dialogFormVisible=!1,r.resetChecked(),r.initMainData());case 65:case"end":return t.stop()}},t,e,[[18,24],[34,40],[50,56]])}))()},filterNode:function(e,t){return!e||-1!==t.name.indexOf(e)},getCheckedNodes:function(){var e=this.$refs.tree.getCheckedNodes();if(e.length>1&&(Object(l.f)("请只选择一个菜单","warning"),this.resetChecked()),1==e.length)return e[0];0==e.length&&(Object(l.f)("请选择一个菜单","warning"),this.resetChecked())},resetChecked:function(){this.$refs.tree.setCheckedKeys([])},imgchange:function(e,t,r){return Object(l.e)(this,e,t,r)}}}),p={render:function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",[r("el-container",[r("el-header",[e._v("\n      关键词查询:\n      "),r("el-input",{staticStyle:{width:"260px!important"},attrs:{placeholder:"输入关键字进行过滤"},model:{value:e.filterText,callback:function(t){e.filterText=t},expression:"filterText"}})],1),e._v(" "),r("el-main",{staticStyle:{height:"500px",border:"2px solid #eee"}},[r("el-tree",{ref:"tree",staticClass:"filter-tree",attrs:{data:e.MainData,props:e.defaultProps,"node-key":"id","show-checkbox":"",accordion:"","check-strictly":"","filter-node-method":e.filterNode}}),e._v(" "),r("el-dialog",{attrs:{title:"菜单信息",visible:e.dialogFormVisible},on:{"update:visible":function(t){e.dialogFormVisible=t}}},[r("el-form",{attrs:{autocomplete:"off"}},[r("el-form-item",{attrs:{label:"菜单名称","label-width":e.formLabelWidth}},[r("el-input",{on:{input:function(t){e.sForm.name=arguments[0]}},model:{value:e.Form.name,callback:function(t){e.$set(e.Form,"name",t)},expression:"Form.name"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"描述","label-width":e.formLabelWidth}},[r("el-input",{on:{input:function(t){e.sForm.description=arguments[0]}},model:{value:e.Form.description,callback:function(t){e.$set(e.Form,"description",t)},expression:"Form.description"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"排序","label-width":e.formLabelWidth}},[r("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.listorder=arguments[0]}},model:{value:e.Form.listorder,callback:function(t){e.$set(e.Form,"listorder",t)},expression:"Form.listorder"}})],1),e._v(" "),"edit"==e.sForm.type?r("el-form-item",{attrs:{label:"父级菜单","label-width":e.formLabelWidth}},[r("el-cascader",{attrs:{options:e.MainData,props:e.defaultProps,"change-on-select":"",clearable:""},on:{change:function(t){e.sForm.parentid=t[t.length-1]}},model:{value:e.Form.parentCategory_array,callback:function(t){e.$set(e.Form,"parentCategory_array",t)},expression:"Form.parentCategory_array"}})],1):e._e(),e._v(" "),r("el-form-item",{attrs:{label:"img图片上传"}},[r("imgup",{attrs:{imglist:e.Form.img,imglimit:e.imglimit},on:{imgchange:function(t){e.imgchange(t,"sForm","img")}}})],1),e._v(" "),r("el-button",{attrs:{type:"primary"},on:{click:function(t){e.onSubmit()}}},[e._v("确 定")])],1)],1)],1),e._v(" "),r("el-footer",{staticStyle:{"margin-top":"20px"}},[r("div",{staticClass:"buttons"},["chuncuiwangluo"==e.passKey?r("el-button",{on:{click:e.handleAddfirst}},[e._v("添加一级菜单")]):e._e(),e._v(" "),"chuncuiwangluo"==e.passKey?r("el-button",{on:{click:e.handleAddchild}},[e._v("添加子菜单")]):e._e(),e._v(" "),r("el-button",{on:{click:e.handleEdit}},[e._v("编辑")]),e._v(" "),"chuncuiwangluo"==e.passKey?r("el-button",{on:{click:e.deleteCategory}},[e._v("删除")]):e._e()],1)])],1)],1)},staticRenderFns:[]},f=r("VU/8")(u,p,!1,null,null,null);t.default=f.exports}});
//# sourceMappingURL=21.cee6fc5df871dcfb3000.js.map