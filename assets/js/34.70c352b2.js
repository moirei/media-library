(window.webpackJsonp=window.webpackJsonp||[]).push([[34,37],{385:function(t,e,a){"use strict";a.r(e);a(56),a(81),a(120);var n={name:"DataTable",props:["data"],computed:{headers:function(){return this.data&&this.data.length?Object.keys(this.data[0]).map((function(t){return(e=t).charAt(0).toUpperCase()+e.slice(1);var e})):[]}}},r=a(55),i=Object(r.a)(n,(function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("table",[a("tr",t._l(t.headers,(function(e){return a("th",{key:e},[t._v(t._s(e))])})),0),t._v(" "),t._l(t.data,(function(e,n){return a("tr",{key:"row-"+n},t._l(e,(function(e){return a("td",{key:e},[t._v(t._s(e))])})),0)}))],2)}),[],!1,null,null,null);e.default=i.exports},497:function(t,e,a){"use strict";a.r(e);var n=a(385),r=[{field:"id",type:"string",description:"The attachment ID"},{field:"alt",type:"string",description:"The attachment alt name"},{field:"url",type:"string",description:"The public url of the attachment"}],i={name:"AttachmentData",components:{DataTable:n.default},data:function(){return{data:r}}},l=a(55),s=Object(l.a)(i,(function(){var t=this.$createElement;return(this._self._c||t)("data-table",{attrs:{data:this.data}})}),[],!1,null,null,null);e.default=s.exports}}]);