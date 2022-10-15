(window.webpackJsonp=window.webpackJsonp||[]).push([[50],{537:function(s,t,a){"use strict";a.r(t);var e=a(55),r=Object(e.a)({},(function(){var s=this,t=s.$createElement,a=s._self._c||t;return a("ContentSlotsDistributor",{attrs:{"slot-key":s.$parent.slotKey}},[a("h1",{attrs:{id:"creating-folders"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#creating-folders"}},[s._v("#")]),s._v(" Creating Folders")]),s._v(" "),a("p",[s._v("The below snippet creates a Folder named "),a("code",[s._v('"Chargers"')]),s._v(" located in "),a("code",[s._v("accessories")]),s._v(". All necessary folders, in this case a root folders named "),a("code",[s._v('"accessories"')]),s._v(", is also created.")]),s._v(" "),a("div",{staticClass:"language-php extra-class"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token variable"}},[s._v("$storage")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("=")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token class-name static-context"}},[s._v("MediaStorage")]),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("::")]),a("span",{pre:!0,attrs:{class:"token function"}},[s._v("active")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n\n"),a("span",{pre:!0,attrs:{class:"token variable"}},[s._v("$folder")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("=")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token variable"}},[s._v("$storage")]),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("->")]),a("span",{pre:!0,attrs:{class:"token function"}},[s._v("createFolder")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v("[")]),s._v("\n  "),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[s._v("'name'")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("=>")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[s._v("'Chargers'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(",")]),s._v("\n  "),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[s._v("'location'")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("=>")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[s._v("'accessories'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(",")]),s._v("\n  "),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[s._v("'description'")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("=>")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("...")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(",")]),s._v("\n  "),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[s._v("'private'")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("=>")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token constant boolean"}},[s._v("true")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(",")]),s._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v("]")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n\n"),a("span",{pre:!0,attrs:{class:"token comment"}},[s._v("// Or")]),s._v("\n"),a("span",{pre:!0,attrs:{class:"token variable"}},[s._v("$folder")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("=")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token variable"}},[s._v("$storage")]),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("->")]),a("span",{pre:!0,attrs:{class:"token function"}},[s._v("assertFolder")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[s._v("'accessories/Chargers'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n")])])]),a("p",[s._v("By default folders' privacy are set to that of the storage.")]),s._v(" "),a("h2",{attrs:{id:"retrieving-folders"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#retrieving-folders"}},[s._v("#")]),s._v(" Retrieving folders")]),s._v(" "),a("p",[s._v("Folders can be resolved from their storage using the "),a("code",[s._v("resolveFolder")]),s._v(" method if the "),a("code",[s._v("ID")]),s._v(" is unknown.")]),s._v(" "),a("div",{staticClass:"language-php extra-class"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token comment"}},[s._v("// Get folder if it exists")]),s._v("\n"),a("span",{pre:!0,attrs:{class:"token variable"}},[s._v("$folder")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("=")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token variable"}},[s._v("$storage")]),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("->")]),a("span",{pre:!0,attrs:{class:"token function"}},[s._v("resolveFolder")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{pre:!0,attrs:{class:"token string single-quoted-string"}},[s._v("'accessories/Chargers'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n")])])]),a("p",[s._v("If "),a("code",[s._v("ID")]),s._v(" is known, you may use the model class directly")]),s._v(" "),a("div",{staticClass:"language-php extra-class"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token variable"}},[s._v("$folder")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("=")]),s._v(" "),a("span",{pre:!0,attrs:{class:"token class-name static-context"}},[s._v("Folder")]),a("span",{pre:!0,attrs:{class:"token operator"}},[s._v("::")]),a("span",{pre:!0,attrs:{class:"token function"}},[s._v("find")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{pre:!0,attrs:{class:"token variable"}},[s._v("$folderId")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n")])])])])}),[],!1,null,null,null);t.default=r.exports}}]);