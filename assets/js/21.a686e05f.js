(window.webpackJsonp=window.webpackJsonp||[]).push([[21,24,37],{384:function(t,e,n){},385:function(t,e,n){"use strict";n.r(e);n(56),n(81),n(120);var s={name:"DataTable",props:["data"],computed:{headers:function(){return this.data&&this.data.length?Object.keys(this.data[0]).map((function(t){return(e=t).charAt(0).toUpperCase()+e.slice(1);var e})):[]}}},a=n(55),r=Object(a.a)(s,(function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("table",[n("tr",t._l(t.headers,(function(e){return n("th",{key:e},[t._v(t._s(e))])})),0),t._v(" "),t._l(t.data,(function(e,s){return n("tr",{key:"row-"+s},t._l(e,(function(e){return n("td",{key:e},[t._v(t._s(e))])})),0)}))],2)}),[],!1,null,null,null);e.default=r.exports},386:function(t,e,n){"use strict";n(384)},387:function(t,e,n){"use strict";n.r(e);var s={name:"Endpoint",components:{DataTable:n(385).default},props:{name:String,method:String,endpoint:String,body:{},response:{}},computed:{responseIsLink:function(){var t;return!(null===(t=this.response)||void 0===t||!t.route)},methodClass:function(){return String(this.method).toLowerCase()}}},a=(n(386),n(55)),r=Object(a.a)(s,(function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("h3",[t._v(t._s(t.name))]),t._v(" "),t.$slots.description?n("p",{staticStyle:{"margin-bottom":"20px"}},[t._t("description")],2):t._e(),t._v(" "),n("p",[t._v("\n    Method: "),n("code",{staticClass:"method",class:[t.methodClass]},[t._v(t._s(t.method))])]),t._v(" "),n("p",[t._v("\n    Endpoint: "),n("code",[t._v("/media-library/"+t._s(t.endpoint))])]),t._v(" "),t.body?[n("h4",[t._v("Body")]),t._v(" "),n("data-table",{attrs:{data:t.body}})]:t._e(),t._v(" "),n("h4",[t._v("Response")]),t._v(" "),t.responseIsLink?[n("router-link",{attrs:{to:t.response.route}},[t._v(t._s(t.response.name))])]:n("data-table",{attrs:{data:t.response}}),t._v(" "),t.$slots.default?n("div",[n("h4",[t._v("Example")]),t._v(" "),t._t("default")],2):t._e()],2)}),[],!1,null,null,null);e.default=r.exports},523:function(t,e,n){"use strict";n.r(e);var s={name:"EndpointStreamPublicFile",components:{Endpoint:n(387).default}},a=n(55),r=Object(a.a)(s,(function(){var t=this.$createElement;return(this._self._c||t)("endpoint",{attrs:{name:"Stream Public File",method:"GET",endpoint:"f/{file-id}/stream"}},[this._t("default")],2)}),[],!1,null,null,null);e.default=r.exports}}]);