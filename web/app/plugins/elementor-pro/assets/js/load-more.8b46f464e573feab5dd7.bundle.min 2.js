/*! elementor-pro - v3.25.0 - 20-11-2024 */
"use strict";(self.webpackChunkelementor_pro=self.webpackChunkelementor_pro||[]).push([[535],{2245:(e,t,s)=>{var o=s(6784);Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=o(s(2078)),r=o(s(5012));class LoopLoadMore extends n.default{getDefaultSettings(){const e=super.getDefaultSettings();return e.selectors.postsContainer=".elementor-loop-container",e.selectors.postWrapperTag=".e-loop-item",e.selectors.loadMoreButton=".e-loop__load-more .elementor-button",e.selectors.dynamicStyleElement='style[id^="loop-dynamic"]',e}afterInsertPosts(e,t){super.afterInsertPosts(e),ElementorProFrontendConfig.settings.lazy_load_background_images&&document.dispatchEvent(new Event("elementor/lazyload/observe")),this.handleDynamicStyleElements(t),(0,r.default)(e),elementorFrontend.elements.$window.trigger("elementor-pro/loop-builder/after-insert-posts")}handleDynamicStyleElements(e){const t=this.getSettings("selectors"),s=e.querySelectorAll(`[data-id="${this.elementId}"] ${t.dynamicStyleElement}`);this.$element.append(s)}}t.default=LoopLoadMore},2078:(e,t)=>{Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;class LoadMore extends elementorModules.frontend.handlers.Base{getDefaultSettings(){return{selectors:{postsContainer:".elementor-posts-container",postWrapperTag:"article",loadMoreButton:".elementor-button",loadMoreSpinnerWrapper:".e-load-more-spinner",loadMoreSpinner:".e-load-more-spinner i, .e-load-more-spinner svg",loadMoreAnchor:".e-load-more-anchor"},classes:{loadMoreSpin:"eicon-animation-spin",loadMoreIsLoading:"e-load-more-pagination-loading",loadMorePaginationEnd:"e-load-more-pagination-end",loadMoreNoSpinner:"e-load-more-no-spinner"}}}getDefaultElements(){const e=this.getSettings("selectors");return{postsWidgetWrapper:this.$element[0],postsContainer:this.$element[0].querySelector(e.postsContainer),loadMoreButton:this.$element[0].querySelector(e.loadMoreButton),loadMoreSpinnerWrapper:this.$element[0].querySelector(e.loadMoreSpinnerWrapper),loadMoreSpinner:this.$element[0].querySelector(e.loadMoreSpinner),loadMoreAnchor:this.$element[0].querySelector(e.loadMoreAnchor)}}bindEvents(){super.bindEvents(),this.elements.loadMoreButton&&this.elements.loadMoreButton.addEventListener("click",(e=>{this.isLoading||(e.preventDefault(),this.handlePostsQuery())}))}onInit(){super.onInit(),this.classes=this.getSettings("classes"),this.isLoading=!1;const e=this.getElementSettings("pagination_type");"load_more_on_click"!==e&&"load_more_infinite_scroll"!==e||(this.isInfinteScroll="load_more_infinite_scroll"===e,this.isSpinnerAvailable=this.getElementSettings("load_more_spinner").value,this.isSpinnerAvailable||this.elements.postsWidgetWrapper.classList.add(this.classes.loadMoreNoSpinner),this.isInfinteScroll?this.handleInfiniteScroll():this.elements.loadMoreSpinnerWrapper&&this.elements.loadMoreButton&&this.elements.loadMoreButton.insertAdjacentElement("beforeEnd",this.elements.loadMoreSpinnerWrapper),this.elementId=this.getID(),this.postId=elementorFrontendConfig.post.id,this.elements.loadMoreAnchor&&(this.currentPage=parseInt(this.elements.loadMoreAnchor.getAttribute("data-page")),this.maxPage=parseInt(this.elements.loadMoreAnchor.getAttribute("data-max-page")),this.currentPage!==this.maxPage&&this.currentPage||this.handleUiWhenNoPosts()))}handleInfiniteScroll(){this.isEdit||(this.observer=elementorModules.utils.Scroll.scrollObserver({callback:e=>{e.isInViewport&&!this.isLoading&&(this.observer.unobserve(this.elements.loadMoreAnchor),this.handlePostsQuery().then((()=>{this.currentPage!==this.maxPage&&this.observer.observe(this.elements.loadMoreAnchor)})))}}),this.observer.observe(this.elements.loadMoreAnchor))}handleUiBeforeLoading(){this.isLoading=!0,this.elements.loadMoreSpinner&&this.elements.loadMoreSpinner.classList.add(this.classes.loadMoreSpin),this.elements.postsWidgetWrapper.classList.add(this.classes.loadMoreIsLoading)}handleUiAfterLoading(){this.isLoading=!1,this.elements.loadMoreSpinner&&this.elements.loadMoreSpinner.classList.remove(this.classes.loadMoreSpin),this.isInfinteScroll&&this.elements.loadMoreSpinnerWrapper&&this.elements.loadMoreAnchor&&this.elements.loadMoreAnchor.insertAdjacentElement("afterend",this.elements.loadMoreSpinnerWrapper),this.elements.postsWidgetWrapper.classList.remove(this.classes.loadMoreIsLoading)}handleUiWhenNoPosts(){this.elements.postsWidgetWrapper.classList.add(this.classes.loadMorePaginationEnd)}afterInsertPosts(){}handleSuccessFetch(e){this.handleUiAfterLoading();const t=this.getSettings("selectors"),s=e.querySelectorAll(`[data-id="${this.elementId}"] ${t.postsContainer} > ${t.postWrapperTag}`),o=e.querySelector(`[data-id="${this.elementId}"] .e-load-more-anchor`).getAttribute("data-next-page");s.forEach((e=>this.elements.postsContainer.append(e))),this.elements.loadMoreAnchor.setAttribute("data-page",this.currentPage),this.elements.loadMoreAnchor.setAttribute("data-next-page",o),this.currentPage===this.maxPage&&this.handleUiWhenNoPosts(),this.afterInsertPosts(s,e)}handlePostsQuery(){this.handleUiBeforeLoading(),this.currentPage++;const e=this.elements.loadMoreAnchor.getAttribute("data-next-page");return fetch(e).then((e=>e.text())).then((e=>{const t=(new DOMParser).parseFromString(e,"text/html");this.handleSuccessFetch(t)}))}}t.default=LoadMore}}]);