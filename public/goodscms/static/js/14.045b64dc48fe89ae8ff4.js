webpackJsonp([14],{"SR+V":function(module,__webpack_exports__,__webpack_require__){"use strict";var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_regenerator__=__webpack_require__("Xxa5"),__WEBPACK_IMPORTED_MODULE_0_babel_runtime_regenerator___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_regenerator__),__WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_asyncToGenerator__=__webpack_require__("exGp"),__WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_asyncToGenerator___default=__webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_asyncToGenerator__),__WEBPACK_IMPORTED_MODULE_2__api_getData__=__webpack_require__("1h8J"),__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__=__webpack_require__("HBZn"),__WEBPACK_IMPORTED_MODULE_4__common_UploadImg__=__webpack_require__("dw4X"),__WEBPACK_IMPORTED_MODULE_5__common_VueEditor__=__webpack_require__("SQnW");__webpack_exports__.a={watch:{},created:function(){this.initCategoryData(),this.initMainData(),this.initThemeData()},components:{imgup:__WEBPACK_IMPORTED_MODULE_4__common_UploadImg__.a,editor:__WEBPACK_IMPORTED_MODULE_5__common_VueEditor__.a},data:function(){return{filterText:"",Categorydata:[],themeData:[],tableData:[],dialogFormVisible:!1,Form:{},standardForm:{name:"",content:"",listorder:"",mainImg:[],bannerImg:[],Category_array:[]},modelStandardForm:{mainImg:[],price:"",stock_num:"",name:"",onShelf:!0,listorder:"",content:""},sForm:{},imglimit:10,formLabelWidth:"120px",token:this.$store.getters.getUserInfo.token,searchItem:{},paginate:{count:0,currentPage:1,pagesize:10,is_page:!0},recStandardForm:{theme_array:[],mainImg:[],name:"",description:""},defaultProps:{children:"child",label:"name",value:"id"},dialogType:"main"}},methods:{initCategoryData:function(){var e=this;return __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_asyncToGenerator___default()(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_regenerator___default.a.mark(function t(){var a,o,_;return __WEBPACK_IMPORTED_MODULE_0_babel_runtime_regenerator___default.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return(o={}).token=(a=e).token,t.prev=3,t.next=6,Object(__WEBPACK_IMPORTED_MODULE_2__api_getData__._1)(o);case 6:_=t.sent,t.next=13;break;case 9:t.prev=9,t.t0=t.catch(3),console.log(t.t0),Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.f)("网络故障","error");case 13:_&&(a.Categorydata=_.data);case 16:case"end":return t.stop()}},t,e,[[3,9]])}))()},initThemeData:function(){var e=this;return __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_asyncToGenerator___default()(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_regenerator___default.a.mark(function t(){var a,o,_;return __WEBPACK_IMPORTED_MODULE_0_babel_runtime_regenerator___default.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return(o=(a=e).paginate).token=a.token,t.prev=3,t.next=6,Object(__WEBPACK_IMPORTED_MODULE_2__api_getData__.J)(o);case 6:_=t.sent,t.next=13;break;case 9:t.prev=9,t.t0=t.catch(3),console.log(t.t0),Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.f)("网络故障","error");case 13:_&&(a.themeData=_.data,console.log(a.themeData));case 16:case"end":return t.stop()}},t,e,[[3,9]])}))()},initMainData:function(){var e=this;return __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_asyncToGenerator___default()(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_regenerator___default.a.mark(function t(){var a,o,_;return __WEBPACK_IMPORTED_MODULE_0_babel_runtime_regenerator___default.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return(a=e).tableData=[],(o=e.paginate).searchItem=Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.a)(a.searchItem),o.token=a.token,t.prev=5,t.next=8,Object(__WEBPACK_IMPORTED_MODULE_2__api_getData__._12)(o);case 8:_=t.sent,t.next=15;break;case 11:t.prev=11,t.t0=t.catch(5),console.log(t.t0),Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.f)("网络故障","error");case 15:_&&(a.paginate.count=_.data.total,a.tableData=_.data.data);case 18:case"end":return t.stop()}},t,e,[[5,11]])}))()},handleAdd:function(){this.dialogType="main",this.Form=Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.a)(this.standardForm),this.sForm={},this.sForm.type="add",this.sForm.token=this.token,this.dialogFormVisible=!0},handleEdit:function(e){this.dialogType="main",this.Form=Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.a)(e),this.sForm={},this.sForm.id=e.id,this.sForm.token=this.token,this.sForm.type="edit";var t=[];Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.c)(this.Categorydata,this.Form.category_id,t),t.push(this.Form.category_id),this.Form.Category_array=t,this.dialogFormVisible=!0},handleDel:function(e){this.sForm={},this.sForm.id=e.id,this.sForm.token=this.token,this.sForm.type="del",this.onSubmit()},handleAddModel:function(e){this.dialogType="model",this.sForm={},this.Form=Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.a)(this.modelStandardForm),this.sForm.type="addModel",this.sForm.product_id=e.id,this.sForm.token=this.token,this.dialogFormVisible=!0},handleEditModel:function handleEditModel(row){var self=this;self.dialogType="model",self.sForm={},console.log(row),self.Form=Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.a)(row),self.Form.onShelf=eval(self.Form.onShelf.toLowerCase()),self.sForm.type="editModel",self.sForm.id=row.id,self.sForm.token=self.token,self.dialogFormVisible=!0},handleDelModel:function(e){this.sForm={},this.sForm.type="delModel",this.sForm.id=e.id,this.sForm.token=this.token,this.onSubmit()},handleRec:function(e){this.dialogType="rec",this.sForm={},this.Form=Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.a)(this.recStandardForm),this.sForm.relation_id=e.id,this.sForm.token=this.token,this.sForm.type="Rec",this.dialogFormVisible=!0},onSubmit:function(){var e=this;return __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_asyncToGenerator___default()(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_regenerator___default.a.mark(function t(){var a,o;return __WEBPACK_IMPORTED_MODULE_0_babel_runtime_regenerator___default.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if("edit"!=(a=e).sForm.type){t.next=17;break}return delete a.sForm.type,t.prev=3,t.next=6,Object(__WEBPACK_IMPORTED_MODULE_2__api_getData__._11)(a.sForm);case 6:o=t.sent,t.next=13;break;case 9:t.prev=9,t.t0=t.catch(3),console.log(t.t0),Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.f)("网络故障","error");case 13:a.sForm.type="edit",t.next=116;break;case 17:if("add"!=a.sForm.type){t.next=38;break}if(delete a.sForm.type,!("category_id"in a.sForm)){t.next=34;break}return t.prev=20,t.next=23,Object(__WEBPACK_IMPORTED_MODULE_2__api_getData__._9)(a.sForm);case 23:o=t.sent,t.next=30;break;case 26:t.prev=26,t.t1=t.catch(20),console.log(t.t1),Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.f)("网络故障","error");case 30:a.sForm.type="add",t.next=35;break;case 34:Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.f)("请选择类别","error");case 35:t.next=116;break;case 38:if("del"!=a.sForm.type){t.next=54;break}return delete a.sForm.type,t.prev=40,t.next=43,Object(__WEBPACK_IMPORTED_MODULE_2__api_getData__._10)(a.sForm);case 43:o=t.sent,t.next=50;break;case 46:t.prev=46,t.t2=t.catch(40),console.log(t.t2),Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.f)("网络故障","error");case 50:a.sForm.type="del",t.next=116;break;case 54:if("addModel"!=a.sForm.type){t.next=70;break}return delete a.sForm.type,t.prev=56,t.next=59,Object(__WEBPACK_IMPORTED_MODULE_2__api_getData__._13)(a.sForm);case 59:o=t.sent,t.next=66;break;case 62:t.prev=62,t.t3=t.catch(56),console.log(t.t3),Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.f)("网络故障","error");case 66:a.sForm.type="addModel",t.next=116;break;case 70:if("editModel"!=a.sForm.type){t.next=86;break}return delete a.sForm.type,t.prev=72,t.next=75,Object(__WEBPACK_IMPORTED_MODULE_2__api_getData__._15)(a.sForm);case 75:o=t.sent,t.next=82;break;case 78:t.prev=78,t.t4=t.catch(72),console.log(t.t4),Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.f)("网络故障","error");case 82:a.sForm.type="editModel",t.next=116;break;case 86:if("delModel"!=a.sForm.type){t.next=102;break}return delete a.sForm.type,t.prev=88,t.next=91,Object(__WEBPACK_IMPORTED_MODULE_2__api_getData__._14)(a.sForm);case 91:o=t.sent,t.next=98;break;case 94:t.prev=94,t.t5=t.catch(88),console.log(t.t5),Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.f)("网络故障","error");case 98:a.sForm.type="delModel",t.next=116;break;case 102:if("Rec"!=a.sForm.type){t.next=116;break}return delete a.sForm.type,t.prev=104,t.next=107,Object(__WEBPACK_IMPORTED_MODULE_2__api_getData__.D)(a.sForm);case 107:o=t.sent,t.next=114;break;case 110:t.prev=110,t.t6=t.catch(104),console.log(t.t6),Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.f)("网络故障","error");case 114:a.sForm.type="delModel";case 116:o&&"success"==Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.g)(o)&&(a.dialogFormVisible=!1,a.initMainData());case 119:case"end":return t.stop()}},t,e,[[3,9],[20,26],[40,46],[56,62],[72,78],[88,94],[104,110]])}))()},imgchange:function(e,t,a){return Object(__WEBPACK_IMPORTED_MODULE_3__api_commonFunc__.e)(this,e,t,a)}}}},gse9:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=a("SR+V"),_={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("el-container",[a("el-header",[a("div",[a("el-cascader",{attrs:{options:e.Categorydata,props:e.defaultProps,placeholder:"选择商品类别","change-on-select":"",clearable:""},on:{change:function(t){e.searchItem.category_id=t[t.length-1],e.initMainData()}}}),e._v(" "),a("el-input",{staticStyle:{width:"200px"},attrs:{placeholder:"请输入商品名称",clearable:""},on:{blur:function(t){t.target._value?e.searchItem.name=t.target._value:delete e.searchItem.name,e.initMainData()}}}),e._v(" "),a("el-input",{staticStyle:{width:"200px"},attrs:{placeholder:"请输入商品ID",clearable:""},on:{blur:function(t){t.target._value?e.searchItem.id=t.target._value:delete e.searchItem.id,e.initMainData()}}})],1)]),e._v(" "),a("el-main",[a("div",[a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.handleAdd()}}},[e._v("添加商品")])],1)]),e._v(" "),a("el-main",{staticStyle:{height:"600px",border:"2px solid #eee"}},[a("div",[a("el-table",{staticStyle:{width:"100%"},attrs:{data:e.tableData}},[a("el-table-column",{attrs:{type:"expand"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-table",{staticStyle:{width:"100%"},attrs:{data:t.row.product_model}},[a("el-table-column",{attrs:{label:"型号名称",prop:"name",width:"180"}}),e._v(" "),a("el-table-column",{attrs:{label:"库存",prop:"stock_num",width:"180"}}),e._v(" "),a("el-table-column",{attrs:{label:"单价",prop:"price",width:"180"}}),e._v(" "),a("el-table-column",{attrs:{label:"操作"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{size:"small"},on:{click:function(a){e.handleEditModel(t.row)}}},[e._v("编辑型号")]),e._v(" "),a("el-button",{attrs:{size:"small"},on:{click:function(a){e.handleDelModel(t.row)}}},[e._v("删除型号")]),e._v(" "),a("el-button",{attrs:{size:"small"},on:{click:function(a){e.handleRec(t.row)}}},[e._v("推送到")])]}}])})],1)]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"商品ID",prop:"id"}}),e._v(" "),a("el-table-column",{attrs:{label:"商品名称",prop:"name"}}),e._v(" "),a("el-table-column",{attrs:{label:"商品类别",prop:"category.name"}}),e._v(" "),a("el-table-column",{attrs:{label:"创建时间",prop:"create_time"}}),e._v(" "),a("el-table-column",{attrs:{label:"操作"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{size:"small"},on:{click:function(a){e.handleEdit(t.row)}}},[e._v("\n                编辑\n              ")]),e._v(" "),a("el-button",{attrs:{size:"small"},on:{click:function(a){e.handleDel(t.row)}}},[e._v("\n                删除\n              ")]),e._v(" "),a("el-button",{attrs:{size:"small"},on:{click:function(a){e.handleAddModel(t.row)}}},[e._v("\n                添加型号\n              ")])]}}])})],1)],1),e._v(" "),a("el-dialog",{attrs:{title:"商品信息",visible:e.dialogFormVisible},on:{"update:visible":function(t){e.dialogFormVisible=t}}},[a("el-form",{attrs:{autocomplete:"off"}},["main"==e.dialogType?a("div",[a("el-form-item",{attrs:{label:"商品名称","label-width":e.formLabelWidth}},[a("el-input",{on:{input:function(t){e.sForm.name=arguments[0]}},model:{value:e.Form.name,callback:function(t){e.$set(e.Form,"name",t)},expression:"Form.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"排序","label-width":e.formLabelWidth}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.listorder=arguments[0]}},model:{value:e.Form.listorder,callback:function(t){e.$set(e.Form,"listorder",t)},expression:"Form.listorder"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"所属分类","label-width":e.formLabelWidth}},[a("el-cascader",{attrs:{options:e.Categorydata,props:e.defaultProps,"change-on-select":"",clearable:""},on:{change:function(t){e.sForm.category_id=t[t.length-1]}},model:{value:e.Form.Category_array,callback:function(t){e.$set(e.Form,"Category_array",t)},expression:"Form.Category_array"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"主图片上传"}},[a("imgup",{attrs:{imglist:e.Form.mainImg,imglimit:e.imglimit},on:{imgchange:function(t){e.imgchange(t,"sForm","mainImg")}}})],1),e._v(" "),a("el-form-item",{attrs:{label:"内容多图上传"}},[a("imgup",{attrs:{imglist:e.Form.bannerImg,imglimit:e.imglimit},on:{imgchange:function(t){e.imgchange(t,"sForm","bannerImg")}}})],1),e._v(" "),a("el-form-item",{attrs:{label:"图文编辑"}},[a("editor",{attrs:{defaultcontent:e.Form.content},on:{contentsave:function(t){e.sForm.content=t,e.Form.content=t}}})],1),e._v(" "),a("el-form-item",{attrs:{label:"passage1","label-width":e.formLabelWidth}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.passage1=arguments[0]}},model:{value:e.Form.passage1,callback:function(t){e.$set(e.Form,"passage1",t)},expression:"Form.passage1"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"passage2","label-width":e.formLabelWidth}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.passage2=arguments[0]}},model:{value:e.Form.passage2,callback:function(t){e.$set(e.Form,"passage2",t)},expression:"Form.passage2"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"passage3","label-width":e.formLabelWidth}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.passage3=arguments[0]}},model:{value:e.Form.passage3,callback:function(t){e.$set(e.Form,"passage3",t)},expression:"Form.passage3"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"passage4","label-width":e.formLabelWidth}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.passage4=arguments[0]}},model:{value:e.Form.passage4,callback:function(t){e.$set(e.Form,"passage4",t)},expression:"Form.passage4"}})],1)],1):e._e(),e._v(" "),"model"==e.dialogType?a("div",[a("el-form-item",{attrs:{label:"型号名称","label-width":e.formLabelWidth}},[a("el-input",{on:{input:function(t){e.sForm.name=arguments[0]}},model:{value:e.Form.name,callback:function(t){e.$set(e.Form,"name",t)},expression:"Form.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"价格","label-width":e.formLabelWidth}},[a("el-input",{on:{input:function(t){e.sForm.price=arguments[0]}},model:{value:e.Form.price,callback:function(t){e.$set(e.Form,"price",t)},expression:"Form.price"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"库存","label-width":e.formLabelWidth}},[a("el-input",{on:{input:function(t){e.sForm.stock_num=arguments[0]}},model:{value:e.Form.stock_num,callback:function(t){e.$set(e.Form,"stock_num",t)},expression:"Form.stock_num"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"主图片上传"}},[a("imgup",{attrs:{imglist:e.Form.mainImg,imglimit:e.imglimit},on:{imgchange:function(t){e.imgchange(t,"sForm","mainImg")}}})],1),e._v(" "),a("el-form-item",{attrs:{label:"排序","label-width":e.formLabelWidth}},[a("el-input",{attrs:{"auto-complete":"off"},on:{input:function(t){e.sForm.listorder=arguments[0]}},model:{value:e.Form.listorder,callback:function(t){e.$set(e.Form,"listorder",t)},expression:"Form.listorder"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"图文编辑"}},[a("editor",{attrs:{defaultcontent:e.Form.content},on:{contentsave:function(t){e.sForm.content=t,e.Form.content=t}}})],1),e._v(" "),a("el-form-item",{attrs:{label:"排序","label-width":e.formLabelWidth}},[a("el-switch",{attrs:{"active-text":"上架","inactive-text":"下架"},on:{change:function(t){e.sForm.onShelf=t}},model:{value:e.Form.onShelf,callback:function(t){e.$set(e.Form,"onShelf",t)},expression:"Form.onShelf"}})],1)],1):e._e(),e._v(" "),"rec"==e.dialogType?a("div",[a("el-form-item",{attrs:{label:"名称","label-width":e.formLabelWidth}},[a("el-input",{on:{input:function(t){e.sForm.name=arguments[0]}},model:{value:e.Form.name,callback:function(t){e.$set(e.Form,"name",t)},expression:"Form.name"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"描述","label-width":e.formLabelWidth}},[a("el-input",{on:{input:function(t){e.sForm.description=arguments[0]}},model:{value:e.Form.description,callback:function(t){e.$set(e.Form,"description",t)},expression:"Form.description"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"主图片上传"}},[a("imgup",{attrs:{imglist:e.Form.mainImg,imglimit:e.imglimit},on:{imgchange:e.imgchange}})],1),e._v(" "),a("el-form-item",{attrs:{label:"推送推荐位名称","label-width":e.formLabelWidth}},[a("el-cascader",{attrs:{options:e.themeData,props:e.defaultProps,"change-on-select":"",clearable:""},on:{change:function(t){e.sForm.theme_id=t[t.length-1]}},model:{value:e.Form.theme_array,callback:function(t){e.$set(e.Form,"theme_array",t)},expression:"Form.theme_array"}})],1)],1):e._e(),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.onSubmit()}}},[e._v("确 定")])],1)],1)],1),e._v(" "),a("el-footer",{staticStyle:{"margin-top":"20px"}},[a("div",[a("el-pagination",{attrs:{"current-page":e.paginate.currentPage,"page-sizes":[10,50,70,100],"page-size":e.paginate.pagesize,layout:"total, sizes, prev, pager, next, jumper",total:e.paginate.count},on:{"size-change":function(t){e.paginate.pagesize=t,e.initMainData()},"current-change":function(t){e.paginate.currentPage=t,e.initMainData()}}})],1)])],1)],1)},staticRenderFns:[]},r=a("VU/8")(o.a,_,!1,null,null,null);t.default=r.exports}});
//# sourceMappingURL=14.045b64dc48fe89ae8ff4.js.map