!function(a){"use strict";var t=function(){setTimeout((function(){rey.hooks.doAction("minicart/open")}),500)};a(document.body).on("added_to_cart",(function(e,r,o,d){if("yes"!==wc_add_to_cart_params.cart_redirect_after_add&&"popup"===reyParams.after_add_to_cart){if(void 0!==d)if(d.closest(".product").is("li")&&!1===reyParams.after_add_to_cart_popup_config.show_in_loop||d.closest(".product").hasClass("--prevent-aatc"))return void t();!function(e){if(e||(e=a(".single_add_to_cart_button:not(.disabled)")),e.hasClass("--prevent-aatc"))t();else{var r=e.attr("data-product_id")||e.val()||e.closest("form.cart").find('input[name="product_id"]').val()||"";r?"undefined"!=typeof ReyModal&&new ReyModal({linkAttributeName:!1,catchFocus:!1,closeOnEsc:!0,backscroll:!0,beforeOpen:function(t){t._exists||rey.ajax.request("after_add_to_cart_popup",{data:{product_id:r},cb:function(e){var r=a(".rey-acPopup-content",t.$content);if(a(".reymodal__loader",t.$content).fadeOut("fast"),e.data){var o=r.html(e.data),d=a("ul.products",o),p=a("li.product",o),_=parseInt(o.attr("data-cols"))||4;if(rey.vars.is_tablet?_=reyParams.after_add_to_cart_popup_config.tablet_per_page||3:rey.vars.is_mobile&&(_=reyParams.after_add_to_cart_popup_config.mobile_per_page||2),d.length)if(rey.hooks.doAction("product/loaded",d[0].querySelectorAll("li.product")),a(".add_to_cart_button",o).addClass("--prevent-aatc"),d.hasClass("--carousel")&&p.length>_){var c=rey.vars.is_rtl?{left:reyParams.after_add_to_cart_popup_config.side_padding||50}:{right:reyParams.after_add_to_cart_popup_config.side_padding||50},n={type:"slide",autoplay:reyParams.after_add_to_cart_popup_config.autoplay,interval:parseInt(reyParams.after_add_to_cart_popup_config.autoplay_speed),perPage:_,rewind:!0,arrows:!1,pagination:!1,padding:c,breakpoints:{1024:{perPage:reyParams.after_add_to_cart_popup_config.tablet_per_page||3},767:{perPage:reyParams.after_add_to_cart_popup_config.mobile_per_page||2}}};rey.components.slider({element:d[0],config:n,createArrows:{navSelector:"rey-acPopup-nav",appendTo:a(".rey-acPopup-header",t.$content)},markup:{selector:"acPopup-carousel"}})}else p.each((function(t,e){setTimeout((function(){a(e).addClass("--animated-in")}),30)}))}else r.html("Couldn't retrieve data!")}})}}).init().open({width:1200,content:".rey-acPopup",wrapperClass:"rey-acPopup-modal",id:"aatc-"+r}):t()}}(d)}}))}(jQuery);