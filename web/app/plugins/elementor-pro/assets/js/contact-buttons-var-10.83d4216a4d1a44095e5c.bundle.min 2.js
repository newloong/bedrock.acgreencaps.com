/*! elementor-pro - v3.25.0 - 20-11-2024 */
"use strict";(self.webpackChunkelementor_pro=self.webpackChunkelementor_pro||[]).push([[61],{7263:(t,e,n)=>{var s=n(6784);Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=s(n(8562)),o=s(n(8243));class ContactButtonsv10Handler extends i.default{getDefaultSettings(){return{selectors:{main:".e-contact-buttons-var-10",links:".e-contact-buttons__contact-icon-link"},constants:{active:"active"}}}getDefaultElements(){const t=this.getSettings("selectors");return{main:this.$element[0].querySelector(t.main),links:this.$element[0].querySelectorAll(t.links)}}isMobileDevice(){return["mobile","mobile_extra"].includes(elementorFrontend.getCurrentDeviceMode())}handleLinkClick(t){t.preventDefault();const{active:e}=this.getSettings("constants");if(t.currentTarget.classList.contains(e)){const n=t.currentTarget.getAttribute("href"),s=t.currentTarget.getAttribute("target");s?window.open(n,s):n&&(window.location.href=n),t.currentTarget.classList.remove(e)}else this.closeAllLinks(),t.currentTarget.classList.add(e)}closeAllLinks(){const{active:t}=this.getSettings("constants");this.elements.links.forEach((e=>e.classList.remove(t)))}linksEventListeners(){this.elements.links.length&&this.isMobileDevice()&&(this.elements.links.forEach((t=>{t.addEventListener("click",(t=>{this.handleLinkClick(t)}))})),document.addEventListener("click",(t=>{this.elements.main.contains(t.target)||this.closeAllLinks()})))}bindEvents(){this.linksEventListeners()}setupInnerContainer(){this.elements.main.closest(".e-con-inner").classList.add("e-con-inner--floating-buttons")}onInit(){super.onInit(...arguments),this.clickTrackingHandler=new o.default({$element:this.$element}),this.setupInnerContainer()}}e.default=ContactButtonsv10Handler},8243:(t,e,n)=>{var s=n(6784);Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=s(n(5707)),o=s(n(8562));class ClickTrackingHandler extends o.default{constructor(){super(...arguments),(0,i.default)(this,"clicks",[])}getDefaultSettings(){return{selectors:{contentWrapper:".e-contact-buttons__content-wrapper",contentWrapperFloatingBars:".e-floating-bars",floatingBarCouponButton:".e-floating-bars__coupon-button",floatingBarsHeadline:".e-floating-bars__headline",contactButtonsVar4:".e-contact-buttons__contact-icon-link",contactButtonsVar5:".e-contact-buttons__chat-button",contactButtonsVar6:".e-contact-buttons-var-6",contactButtonsVar8:".e-contact-buttons-var-8",elementorWrapper:'[data-elementor-type="floating-buttons"]',contactButtonCore:".e-contact-buttons__send-button"}}}getDefaultElements(){const t=this.getSettings("selectors");return{contentWrapper:this.$element[0].querySelector(t.contentWrapper),contentWrapperFloatingBars:this.$element[0].querySelector(t.contentWrapperFloatingBars),contactButtonsVar5:this.$element[0].querySelector(t.contactButtonsVar5),contactButtonsVar6:this.$element[0].querySelector(t.contactButtonsVar6)}}bindEvents(){this.elements.contentWrapper&&this.elements.contentWrapper.addEventListener("click",this.onChatButtonTrackClick.bind(this)),this.elements.contactButtonsVar5&&this.elements.contactButtonsVar5.addEventListener("click",this.onChatButtonTrackClick.bind(this)),this.elements.contactButtonsVar6&&this.elements.contactButtonsVar6.addEventListener("click",this.onChatButtonTrackClick.bind(this)),this.elements.contentWrapperFloatingBars&&this.elements.contentWrapperFloatingBars.addEventListener("click",this.onChatButtonTrackClick.bind(this)),window.addEventListener("beforeunload",(()=>{this.clicks.length>0&&this.sendClicks()}))}onChatButtonTrackClick(t){const e=t.target||t.srcElement,n=this.getSettings("selectors"),s=[n.contactButtonsVar4,n.contactButtonsVar6,n.floatingBarCouponButton,n.floatingBarsHeadline,n.contactButtonCore];for(const t of s)(e.matches(t)||e.closest(t))&&this.getDocumentIdAndTrack(e,n);(e.matches(n.contactButtonsVar5)||e.closest(n.contactButtonsVar5))&&e.closest(".e-contact-buttons-var-5")&&this.getDocumentIdAndTrack(e,n)}getDocumentIdAndTrack(t,e){const n=t.closest(e.elementorWrapper).dataset.elementorId;this.trackClick(n)}trackClick(t){t&&(this.clicks.push(t),this.clicks.length>=10&&this.sendClicks())}sendClicks(){const t=new FormData;t.append("action","elementor_send_clicks"),t.append("_nonce",elementorFrontendConfig?.nonces?.floatingButtonsClickTracking),this.clicks.forEach((e=>t.append("clicks[]",e))),fetch(elementorFrontendConfig?.urls?.ajaxurl,{method:"POST",body:t}).then((()=>{this.clicks=[]}))}}e.default=ClickTrackingHandler},8562:t=>{t.exports=elementorModules.ViewModule.extend({$element:null,editorListeners:null,onElementChange:null,onEditSettingsChange:null,onPageSettingsChange:null,isEdit:null,__construct(t){this.isActive(t)&&(this.$element=t.$element,this.isEdit=this.$element.hasClass("elementor-element-edit-mode"),this.isEdit&&this.addEditorListeners())},isActive:()=>!0,isElementInTheCurrentDocument(){return!!elementorFrontend.isEditMode()&&elementor.documents.currentDocument.id.toString()===this.$element[0].closest(".elementor").dataset.elementorId},findElement(t){var e=this.$element;return e.find(t).filter((function(){return jQuery(this).parent().closest(".elementor-element").is(e)}))},getUniqueHandlerID(t,e){return t||(t=this.getModelCID()),e||(e=this.$element),t+e.attr("data-element_type")+this.getConstructorID()},initEditorListeners(){var t=this;if(t.editorListeners=[{event:"element:destroy",to:elementor.channels.data,callback(e){e.cid===t.getModelCID()&&t.onDestroy()}}],t.onElementChange){const e=t.getWidgetType()||t.getElementType();let n="change";"global"!==e&&(n+=":"+e),t.editorListeners.push({event:n,to:elementor.channels.editor,callback(e,n){t.getUniqueHandlerID(n.model.cid,n.$el)===t.getUniqueHandlerID()&&t.onElementChange(e.model.get("name"),e,n)}})}t.onEditSettingsChange&&t.editorListeners.push({event:"change:editSettings",to:elementor.channels.editor,callback(e,n){if(n.model.cid!==t.getModelCID())return;const s=Object.keys(e.changed)[0];t.onEditSettingsChange(s,e.changed[s])}}),["page"].forEach((function(e){var n="on"+e[0].toUpperCase()+e.slice(1)+"SettingsChange";t[n]&&t.editorListeners.push({event:"change",to:elementor.settings[e].model,callback(e){t[n](e.changed)}})}))},getEditorListeners(){return this.editorListeners||this.initEditorListeners(),this.editorListeners},addEditorListeners(){var t=this.getUniqueHandlerID();this.getEditorListeners().forEach((function(e){elementorFrontend.addListenerOnce(t,e.event,e.callback,e.to)}))},removeEditorListeners(){var t=this.getUniqueHandlerID();this.getEditorListeners().forEach((function(e){elementorFrontend.removeListeners(t,e.event,null,e.to)}))},getElementType(){return this.$element.data("element_type")},getWidgetType(){const t=this.$element.data("widget_type");if(t)return t.split(".")[0]},getID(){return this.$element.data("id")},getModelCID(){return this.$element.data("model-cid")},getElementSettings(t){let e={};const n=this.getModelCID();if(this.isEdit&&n){const t=elementorFrontend.config.elements.data[n],s=t.attributes;let i=s.widgetType||s.elType;s.isInner&&(i="inner-"+i);let o=elementorFrontend.config.elements.keys[i];o||(o=elementorFrontend.config.elements.keys[i]=[],jQuery.each(t.controls,((t,e)=>{(e.frontend_available||e.editor_available)&&o.push(t)}))),jQuery.each(t.getActiveControls(),(function(t){if(-1!==o.indexOf(t)){let n=s[t];n.toJSON&&(n=n.toJSON()),e[t]=n}}))}else e=this.$element.data("settings")||{};return this.getItems(e,t)},getEditSettings(t){var e={};return this.isEdit&&(e=elementorFrontend.config.elements.editSettings[this.getModelCID()].attributes),this.getItems(e,t)},getCurrentDeviceSetting(t){return elementorFrontend.getCurrentDeviceSetting(this.getElementSettings(),t)},onInit(){this.isActive(this.getSettings())&&elementorModules.ViewModule.prototype.onInit.apply(this,arguments)},onDestroy(){this.isEdit&&this.removeEditorListeners(),this.unbindEvents&&this.unbindEvents()}})}}]);