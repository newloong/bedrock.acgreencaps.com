!function(t){"use strict";var i=function(){this.variationPrice="",this.activePrice="",this.qty=1,this.removePrice=!1,this.maybeUpdateRegularPrice=!0,this.timeoutTimer=5,this.activeVariation=null,this.init=function(){this.btn=document.querySelector("button.single_add_to_cart_button[data-price-atc-val]"),this.btn&&(this.numberFormat=wNumb({mark:reyParams.price_decimal_separator,thousand:reyParams.price_thousand_separator,decimals:reyParams.price_decimal_precision,edit:function(t){var i="";for(let t=0;t<parseInt(reyParams.price_decimal_precision);t++)i+="0";return t.replace(reyParams.price_decimal_separator+i,"")}}),this.mainPrice=this.btn.getAttribute("data-price-atc-val"),this.form=this.btn.closest("form.cart"),this.isVariableProduct=this.form.classList.contains("variations_form"),this.events(),document.dispatchEvent(new CustomEvent("reycore/price-atc/init",{detail:{instance:this}})))},this.events=function(){t(this.form).on("found_variation",((t,i)=>{this.activeVariation=i,setTimeout((()=>{this.maybeUpdateRegularPrice&&i.display_price&&(this.activePrice=i.display_price,this.setPriceAttr())}),this.timeoutTimer)})),t(this.form).on("reset_data",(()=>{setTimeout((()=>{this.maybeUpdateRegularPrice||this.removePriceAttr()}),this.timeoutTimer)})),t(document).ajaxSuccess(((i,e,r)=>{if("rightpress_product_price_live_update"===new URLSearchParams(r.data).get("action")){var a=t(".rightpress_product_price_live_update .price");if(a.length){var s=this.numberFormat.from(a.text());s&&(this.activePrice=s,this.setPriceAttr())}}})),rey.dom.delegate(document,"change",".quantity .qty",(t=>{t.initiator.classList.contains("--disabled")||t.initiator.closest(".product.product-type-grouped")||this.isVariableProduct&&this.form.querySelector(".wc-variation-selection-needed")||(this.qty=parseFloat(t.initiator.value)||1,this.setPriceAttr())}))},this.removePriceAttr=function(){this.btn.setAttribute("data-price-atc-val","")},this.setPriceAttr=function(t){var i;this.removePrice?this.btn.setAttribute("data-price-atc-val",""):(i=t||(this.activePrice?this.activePrice:this.numberFormat.from(this.mainPrice)))&&this.btn.setAttribute("data-price-atc-val",reyParams.price_format.replace("{{price}}",this.numberFormat.to(i*this.qty)))},this.init()};document.addEventListener("rey-DOMContentLoaded",(function(t){new i}));class e{isSubscription=!1;app=null;sattData=null;constructor(i){this.app=i,this.sattData=t(i.form).data("satt_script"),this.isSubscription=this.checkIfSubscription(),this.app.maybeUpdateRegularPrice=!this.isSubscription,this.updatePrice(),rey.dom.delegate(i.form,"change","input[name=subscribe-to-action-input]",(t=>{this.isSubscription=this.checkIfSubscription(),this.app.maybeUpdateRegularPrice=!this.isSubscription,this.isSubscription?this.updatePrice():(this.app.activeVariation&&this.app.activeVariation.display_price?this.app.activePrice=this.app.activeVariation.display_price:this.app.activePrice=this.app.numberFormat.from(this.app.mainPrice),this.app.setPriceAttr())})),t(i.form).on("found_variation",((t,i)=>{this.updatePrice()})),t(i.form).on("reset_data",(()=>{this.app.removePriceAttr()}))}updatePrice(){setTimeout((()=>{if(this.checkIfSubscription()){var t=this.getSubscriptionPrice();t&&(this.app.activePrice=t,this.app.setPriceAttr())}}),this.app.timeoutTimer)}checkIfSubscription(){return!!this.app.form.querySelector('input[name=subscribe-to-action-input][value="yes"]:checked')}getSubscriptionPrice(){var t=this.sattData.schemes_view.$el_options.find("option[selected]").text();if(!t)return!1;var i=t.match(/\$(\d+(\.\d{1,2})?)/),e=!!i&&i[0];return!!e&&this.app.numberFormat.from(e)}}document.addEventListener("reycore/price-atc/init",(function(t){new e(t.detail.instance)}))}(jQuery);