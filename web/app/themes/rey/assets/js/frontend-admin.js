!function(t){"use strict";var e=function(){this.init=function(){this.setupPlugins()},this.setupPlugins=function(){document.querySelectorAll("a[data-setup-plugin]").forEach((e=>{var n=e.getAttribute("data-setup-plugin");n&&e.addEventListener("click",(a=>{a.preventDefault(),a.currentTarget.classList.add("--loading"),t.ajax({url:reyFrontendAdminParams.ajax_url,data:{item:n,action:"rey_setup_plugin",security:reyFrontendAdminParams.ajax_nonce},success:function(t){t&&t.success?(t.data&&(e.textContent=t.data),setTimeout((function(){location.reload()}),1e3)):t.data&&(e.textContent=t.data,setTimeout((function(){location.reload()}),2e3))}})}))}))},this.init()};t(document).ready((function(t){new e}))}(jQuery);