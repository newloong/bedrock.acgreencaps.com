/*! elementor-pro - v3.25.0 - 20-11-2024 */
"use strict";(self.webpackChunkelementor_pro=self.webpackChunkelementor_pro||[]).push([[4],{3046:(e,t)=>{Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;class Base extends elementorModules.frontend.handlers.Base{getDefaultSettings(){return{selectors:{stickyRightColumn:".e-sticky-right-column"},classes:{stickyRightColumnActive:"e-sticky-right-column--active"}}}getDefaultElements(){const e=this.getSettings("selectors");return{$stickyRightColumn:this.$element.find(e.stickyRightColumn)}}bindEvents(){elementorFrontend.elements.$document.on("select2:open",(e=>{this.addSelect2Wrapper(e)}))}addSelect2Wrapper(e){const t=jQuery(e.target).data("select2");t.$dropdown&&t.$dropdown.addClass("e-woo-select2-wrapper")}isStickyRightColumnActive(){const e=this.getSettings("classes");return this.elements.$stickyRightColumn.hasClass(e.stickyRightColumnActive)}activateStickyRightColumn(){const e=this.getElementSettings(),t=elementorFrontend.elements.$wpAdminBar,n=this.getSettings("classes");let s=e.sticky_right_column_offset||0;t.length&&"fixed"===t.css("position")&&(s+=t.height()),"yes"===this.getElementSettings("sticky_right_column")&&(this.elements.$stickyRightColumn.addClass(n.stickyRightColumnActive),this.elements.$stickyRightColumn.css("top",s+"px"))}deactivateStickyRightColumn(){if(!this.isStickyRightColumnActive())return;const e=this.getSettings("classes");this.elements.$stickyRightColumn.removeClass(e.stickyRightColumnActive)}toggleStickyRightColumn(){this.getElementSettings("sticky_right_column")?this.isStickyRightColumnActive()||this.activateStickyRightColumn():this.deactivateStickyRightColumn()}equalizeElementHeight(e){if(e.length){e.removeAttr("style");let t=0;e.each(((e,n)=>{t=Math.max(t,n.offsetHeight)})),0<t&&e.css({height:t+"px"})}}removePaddingBetweenPurchaseNote(e){e&&e.each(((e,t)=>{jQuery(t).prev().children("td").addClass("product-purchase-note-is-below")}))}updateWpReferers(){const e=this.getSettings("selectors"),t=this.$element.find(e.wpHttpRefererInputs),n=new URL(document.location);n.searchParams.set("elementorPageId",elementorFrontend.config.post.id),n.searchParams.set("elementorWidgetId",this.getID()),t.attr("value",n)}}t.default=Base},2937:(e,t,n)=>{var s=n(6784);Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=s(n(3046));class Cart extends i.default{getDefaultSettings(){const e=super.getDefaultSettings(...arguments);return{selectors:{...e.selectors,shippingForm:".shipping-calculator-form",quantityInput:".qty",updateCartButton:"button[name=update_cart]",wpHttpRefererInputs:"[name=_wp_http_referer]",hiddenInput:"input[type=hidden]",productRemove:".product-remove a"},classes:e.classes,ajaxUrl:elementorProFrontend.config.ajaxurl}}getDefaultElements(){const e=this.getSettings("selectors");return{...super.getDefaultElements(...arguments),$shippingForm:this.$element.find(e.shippingForm),$stickyColumn:this.$element.find(e.stickyColumn),$hiddenInput:this.$element.find(e.hiddenInput)}}bindEvents(){super.bindEvents();const e=this.getSettings("selectors");elementorFrontend.elements.$body.on("wc_fragments_refreshed",(()=>this.applyButtonsHoverAnimation())),"yes"===this.getElementSettings("update_cart_automatically")&&this.$element.on("input",e.quantityInput,(()=>this.updateCart())),elementorFrontend.elements.$body.on("wc_fragments_loaded wc_fragments_refreshed",(()=>{this.updateWpReferers(),(elementorFrontend.isEditMode()||elementorFrontend.isWPPreviewMode())&&this.disableActions()})),elementorFrontend.elements.$body.on("added_to_cart",(function(e,t){if(t.e_manually_triggered)return!1}))}onInit(){super.onInit(...arguments),this.toggleStickyRightColumn(),this.hideHiddenInputsParentElements(),elementorFrontend.isEditMode()&&this.elements.$shippingForm.show(),this.applyButtonsHoverAnimation(),this.updateWpReferers(),(elementorFrontend.isEditMode()||elementorFrontend.isWPPreviewMode())&&this.disableActions()}disableActions(){const e=this.getSettings("selectors");this.$element.find(e.updateCartButton).attr({disabled:"disabled","aria-disabled":"true"}),elementorFrontend.isEditMode()&&(this.$element.find(e.quantityInput).attr("disabled","disabled"),this.$element.find(e.productRemove).css("pointer-events","none"))}onElementChange(e){"sticky_right_column"===e&&this.toggleStickyRightColumn(),"additional_template_select"===e&&elementorPro.modules.woocommerce.onTemplateIdChange("additional_template_select")}onDestroy(){super.onDestroy(...arguments),this.deactivateStickyRightColumn()}updateCart(){const e=this.getSettings("selectors");clearTimeout(this._debounce),this._debounce=setTimeout((()=>{this.$element.find(e.updateCartButton).trigger("click")}),1500)}applyButtonsHoverAnimation(){const e=this.getElementSettings();e.checkout_button_hover_animation&&jQuery(".checkout-button").addClass("elementor-animation-"+e.checkout_button_hover_animation),e.forms_buttons_hover_animation&&jQuery(".shop_table .button").addClass("elementor-animation-"+e.forms_buttons_hover_animation)}hideHiddenInputsParentElements(){this.isEdit&&this.elements.$hiddenInput&&this.elements.$hiddenInput.parent(".form-row").addClass("elementor-hidden")}}t.default=Cart}}]);