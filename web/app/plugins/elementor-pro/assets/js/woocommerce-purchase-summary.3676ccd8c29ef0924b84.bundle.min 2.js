/*! elementor-pro - v3.25.0 - 20-11-2024 */
"use strict";(self.webpackChunkelementor_pro=self.webpackChunkelementor_pro||[]).push([[80],{3046:(e,t)=>{Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;class Base extends elementorModules.frontend.handlers.Base{getDefaultSettings(){return{selectors:{stickyRightColumn:".e-sticky-right-column"},classes:{stickyRightColumnActive:"e-sticky-right-column--active"}}}getDefaultElements(){const e=this.getSettings("selectors");return{$stickyRightColumn:this.$element.find(e.stickyRightColumn)}}bindEvents(){elementorFrontend.elements.$document.on("select2:open",(e=>{this.addSelect2Wrapper(e)}))}addSelect2Wrapper(e){const t=jQuery(e.target).data("select2");t.$dropdown&&t.$dropdown.addClass("e-woo-select2-wrapper")}isStickyRightColumnActive(){const e=this.getSettings("classes");return this.elements.$stickyRightColumn.hasClass(e.stickyRightColumnActive)}activateStickyRightColumn(){const e=this.getElementSettings(),t=elementorFrontend.elements.$wpAdminBar,s=this.getSettings("classes");let n=e.sticky_right_column_offset||0;t.length&&"fixed"===t.css("position")&&(n+=t.height()),"yes"===this.getElementSettings("sticky_right_column")&&(this.elements.$stickyRightColumn.addClass(s.stickyRightColumnActive),this.elements.$stickyRightColumn.css("top",n+"px"))}deactivateStickyRightColumn(){if(!this.isStickyRightColumnActive())return;const e=this.getSettings("classes");this.elements.$stickyRightColumn.removeClass(e.stickyRightColumnActive)}toggleStickyRightColumn(){this.getElementSettings("sticky_right_column")?this.isStickyRightColumnActive()||this.activateStickyRightColumn():this.deactivateStickyRightColumn()}equalizeElementHeight(e){if(e.length){e.removeAttr("style");let t=0;e.each(((e,s)=>{t=Math.max(t,s.offsetHeight)})),0<t&&e.css({height:t+"px"})}}removePaddingBetweenPurchaseNote(e){e&&e.each(((e,t)=>{jQuery(t).prev().children("td").addClass("product-purchase-note-is-below")}))}updateWpReferers(){const e=this.getSettings("selectors"),t=this.$element.find(e.wpHttpRefererInputs),s=new URL(document.location);s.searchParams.set("elementorPageId",elementorFrontend.config.post.id),s.searchParams.set("elementorWidgetId",this.getID()),t.attr("value",s)}}t.default=Base},193:(e,t,s)=>{var n=s(6784);Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=n(s(3046));class PurchaseSummaryHandler extends i.default{getDefaultSettings(){return{selectors:{container:".elementor-widget-woocommerce-purchase-summary",address:"address",purchasenote:".product-purchase-note"}}}getDefaultElements(){const e=this.getSettings("selectors");return{$container:this.$element.find(e.container),$address:this.$element.find(e.address),$purchasenote:this.$element.find(e.purchasenote)}}onElementChange(e){const t=["general_text_typography","sections_padding","sections_border_width"];for(const s of t)e.startsWith(s)&&this.equalizeElementHeight(this.elements.$address);e.startsWith("order_details_rows_gap")&&this.removePaddingBetweenPurchaseNote(this.elements.$purchasenote)}applyButtonsHoverAnimation(){const e=this.getElementSettings();e.order_details_button_hover_animation&&this.$element.find(".order-again .button, td .button").addClass("elementor-animation-"+e.order_details_button_hover_animation)}onInit(){super.onInit(...arguments),this.equalizeElementHeight(this.elements.$address),this.removePaddingBetweenPurchaseNote(this.elements.$purchasenote),this.applyButtonsHoverAnimation()}}t.default=PurchaseSummaryHandler}}]);