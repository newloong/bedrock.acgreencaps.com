/*! elementor-pro - v3.25.0 - 20-11-2024 */
"use strict";(self.webpackChunkelementor_pro=self.webpackChunkelementor_pro||[]).push([[932],{7992:(e,t,r)=>{var s=r(2470).__,n=r(6784);Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var l=n(r(5012));class LoopCarousel extends elementorModules.frontend.handlers.CarouselBase{getDefaultSettings(){const e=super.getDefaultSettings();return e.selectors.carousel=".elementor-loop-container",e}getSwiperSettings(){const e=super.getSwiperSettings(),t=this.getElementSettings(),r=elementorFrontend.config.is_rtl,s=`.elementor-element-${this.getID()}`;return"yes"===t.arrows&&(e.navigation={prevEl:r?`${s} .elementor-swiper-button-next`:`${s} .elementor-swiper-button-prev`,nextEl:r?`${s} .elementor-swiper-button-prev`:`${s} .elementor-swiper-button-next`}),e.on.beforeInit=()=>{this.a11ySetSlidesAriaLabels()},e}async onInit(){super.onInit(...arguments),this.ranElementHandlers=!1}handleElementHandlers(){if(this.ranElementHandlers||!this.swiper)return;const e=Array.from(this.swiper.slides).slice(this.swiper.activeIndex-1,this.swiper.slides.length);(0,l.default)(e),this.ranElementHandlers=!0}a11ySetSlidesAriaLabels(){const e=Array.from(this.elements.$slides);e.forEach(((t,r)=>{t.setAttribute("aria-label",`${parseInt(r+1)} ${s("of","elementor-pro")} ${e.length}`)}))}}t.default=LoopCarousel}}]);