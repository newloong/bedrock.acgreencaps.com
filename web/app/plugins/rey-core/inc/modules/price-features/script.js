!function(t){"use strict";var i=function(i){this.init=function(){this.decimal=reyParams.price_decimal_separator,this.thousand=reyParams.price_thousand_separator,this.precision=reyParams.price_decimal_precision,this.$mainPrice=t(i),this.$mainPrice.length&&(this.mainPriceHtml=this.$mainPrice.html(),this.productId=rey.vars.page_id,this.productId&&(this.$variationForm=t('form.variations_form[data-product_id="'+this.productId+'"]'),this.$variationForm.length?this.$qtyContainer=this.$variationForm:this.$qtyContainer=t('.single_add_to_cart_button[value="'+this.productId+'"]').closest("form.cart"),this.changeMainPrice(),this.changePriceWithTotal(),this.changeInstalments()))},this.changeMainPrice=function(){if(reycorePriceFeaturesParams.variation_price_to_main){var i=this;this.$variationForm.on("found_variation",(function(e,a){if(a.price_html){var r=t(a.price_html);r.is("span.price")&&(r=r.html()),i.$mainPrice.empty().html(r)}})).on("reset_data",(function(){i.$mainPrice.empty().html(i.mainPriceHtml)}))}},this.changePriceWithTotal=function(){if(reycorePriceFeaturesParams.price_show_total){var i=this;this.$variationForm.on("found_variation",(function(t,e){i.changeTotal()})).on("reset_data",(function(){i.changeTotal()})),this.$qtyContainer.on("change",".quantity .qty",(function(e){var a=t(this);a.hasClass("--disabled")||a.closest(".product.product-type-grouped").length||i.changeTotal()}))}},this.changeTotal=function(){if(this.$qtyContainer.length){t(".rey-totalPrice").remove();var i=t(".quantity .qty",this.$qtyContainer);if(1!==(h=parseFloat(i.val())||1)){var e=this.$mainPrice,a=t(".woocommerce-variation .woocommerce-variation-price span.price",this.$variationForm);!reycorePriceFeaturesParams.variation_price_to_main&&a.length&&(e=a);var r=t("ins",e);r.length>1&&t(r[0]).closest("del")&&(r=t(r[1]));var n=r.length?r:e,s=wNumb({mark:this.decimal,thousand:this.thousand,decimals:this.precision}),o=this.cleanText(n),c=s.from(o);if(c&&!isNaN(c)){var h=i.val(),m=rey.hooks.applyFilters("reycore/price_features/total_price",c*h,c,h,this,r.length),l=reyParams.price_format.replace("{{price}}",s.to(m));t('<p class="rey-totalPrice"><span class="__total-price-text">'+reyParams.total_text+'</span> <span class="woocommerce-Price-amount">'+l+"</span></p>").insertAfter(this.$mainPrice)}}}},this.changeInstalments=function(){if(reycorePriceFeaturesParams.price_instalments){var i=this;this.$variationForm.length||this.addInstalments(),this.$variationForm.on("found_variation",(function(t,e){i.addInstalments()})).on("reset_data",(function(){i.addInstalments()})),this.$qtyContainer.on("input",".quantity .qty",(function(e){var a=t(this);a.hasClass("--disabled")||a.closest(".product.product-type-grouped").length||i.addInstalments()}))}},this.addInstalments=function(){t(".rey-instalmentsPrice").remove();var i=t(".quantity .qty",this.$qtyContainer),e=parseFloat(i.val())||1,a=this.$mainPrice,r=t(".woocommerce-variation .woocommerce-variation-price span.price",this.$variationForm);!reycorePriceFeaturesParams.variation_price_to_main&&r.length&&(a=r);var n=t("ins",a);n.length>=2&&(n=a.children("ins"));var s=n.length?n:a,o=wNumb({mark:this.decimal,thousand:this.thousand,decimals:this.precision}),c=this.cleanText(s),h=o.from(c);if(h&&!isNaN(h)){var m=parseInt(reycorePriceFeaturesParams.price_instalments_number),l=rey.hooks.applyFilters("reycore/price_features/installments/calculation",h/m*e,h,m,e),p=o.to(l),u="<strong>"+reyParams.price_format.replace("{{price}}",p)+"</strong>",d="<strong>"+m+"</strong>",v=reycorePriceFeaturesParams.price_instalments_text.replace("%p",u).replace("%i",d);t('<p class="rey-instalmentsPrice">'+v+"</p>").insertAfter(this.$mainPrice)}},this.cleanText=function(i){var e=i.clone();t(".rey-discount, .rey-priceText",e).remove();var a=t(".woocommerce-Price-amount",e);return a.length>1?a.first().text():e.text()},this.init()};document.addEventListener("rey-DOMContentLoaded",(function(e){t("p.price").each((function(t,e){e.closest("li.product")||new i(e)}))}))}(jQuery);