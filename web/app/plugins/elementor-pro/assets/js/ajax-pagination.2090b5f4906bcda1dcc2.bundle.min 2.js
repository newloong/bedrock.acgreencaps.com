/*! elementor-pro - v3.25.0 - 20-11-2024 */
"use strict";(self.webpackChunkelementor_pro=self.webpackChunkelementor_pro||[]).push([[550],{4734:(e,t,n)=>{var s=n(6784);Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=s(n(4921)),r=s(n(5012));class AjaxPagination extends elementorModules.frontend.handlers.Base{getDefaultSettings(){return{selectors:{links:"a.page-numbers:not(.current)",widgetContainer:".elementor-widget-container",postWrapperTag:".e-loop-item"}}}getDefaultElements(){const e=this.getSettings("selectors");return{links:this.$element[0].querySelectorAll(e.links),widgetContainer:this.$element[0].querySelector(e.widgetContainer)}}bindEvents(){super.bindEvents(),this.linksEventListeners()}linksEventListeners(){this.elements.links.length&&"ajax"===this.getElementSettings("pagination_load_type")&&this.elements.links.forEach((e=>{e.addEventListener("click",(e=>{this.handleLinkClick(e)}))}))}handleLinkClick(e){if(e.preventDefault(),this.isLoading)return;this.removeLinksListeners(),this.handleUiBeforeLoading();const t=e?.target.getAttribute("href");return this.updateURLQueryString(t),fetch(t).then((e=>e.text())).then((e=>{const t=(new DOMParser).parseFromString(e,"text/html");this.handleSuccessFetch(t)}))}removeLinksListeners(){this.elements.links.length&&this.elements.links.forEach((e=>{e.removeEventListener("click",this.handleLinkClick)}))}updateURLQueryString(e){const t=new URL(window.location.href),n=t.searchParams,s=new URL(e).searchParams;s.forEach(((e,t)=>{n.set(t,e)})),s.has("e-page-"+this.elementId)||n.delete("e-page-"+this.elementId),history.pushState(null,"",t.href)}handleUiBeforeLoading(){this.setLoading(!0),this.ajaxHelper.addLoadingAnimationOverlay(this.elementId),this.maybeScrollToTop()}setLoading(e){this.isLoading=e}maybeScrollToTop(){if("yes"!==this.getElementSettings("auto_scroll"))return;const e=document.querySelector(`.elementor-element-${this.elementId}`);e&&e.scrollIntoView({behavior:"smooth"})}handleUiAfterLoading(){this.setLoading(!1),this.ajaxHelper.removeLoadingAnimationOverlay(this.elementId)}handleSuccessFetch(e){this.handleUiAfterLoading();const t=this.getSettings("selectors"),n=e.querySelector(`[data-id="${this.elementId}"] ${t.widgetContainer}`),s=this.elements.widgetContainer;this.$element[0].replaceChild(n,s),this.afterInsertPosts()}afterInsertPosts(){const e=this.getSettings("selectors"),t=document.querySelectorAll(`[data-id="${this.elementId}"] ${e.postWrapperTag}`);elementorFrontend.elementsHandler.runReadyTrigger(this.$element[0]),(0,r.default)(t),ElementorProFrontendConfig.settings.lazy_load_background_images&&document.dispatchEvent(new Event("elementor/lazyload/observe"))}onInit(){super.onInit(),this.setLoading(!1),this.elementId=this.getID(),this.ajaxHelper=new i.default}}t.default=AjaxPagination}}]);