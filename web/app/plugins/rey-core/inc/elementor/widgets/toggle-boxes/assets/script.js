!function(t){"use strict";var e=function(e){var s=this;this.init=function(){this.$scope=e,this.$boxesWrapper=t(".rey-toggleBoxes",this.$scope),this.$boxes=t(".rey-toggleBox",this.$boxesWrapper),this.mainIndex=0;var s=this.$boxes.filter(".--active");s.length&&(this.mainIndex=s.index()),this.settings=t.extend({target_type:"",tabs_target:!1,parent_trigger:"click",$target:!1,$targetContainer:document},JSON.parse(this.$boxesWrapper.attr("data-config")||"{}")),"hover"===this.settings.parent_trigger&&(this.settings.parent_trigger="mouseenter",rey.vars.is_desktop||(this.settings.parent_trigger=rey.vars.mobileClickEvent)),this.checkForFragments();var i=this.$scope.closest(".rey-mega-gs");i.length&&(this.settings.$targetContainer=i),this.connect_the_dots()},this.checkForFragments=function(){var e=window.location.href,s=this;this.$boxes.each((function(i,n){var r=t(n),a=r.attr("data-activate-on");a&&-1!==e.indexOf(a)&&(s.mainIndex=r.index(),r.trigger(s.settings.parent_trigger))}))},this.connect_the_dots=function(){var e=this;switch(this.settings.target_type){case"":e=this;this.$boxes.on("mouseenter",(function(s){e.$boxes.removeClass("--active"),t(this).addClass("--active")}));break;case"carousel":if(this.settings.carousel_target){if(this.settings.$target=t('.swiper-container[data-carousel-id="'+this.settings.carousel_target+'"]',this.settings.$targetContainer),!this.settings.$target.length||!this.$boxes.length)return;if("undefined"==typeof Swiper)return;var i=function(){var s=e.settings.$target[0].swiper;void 0!==s?(s.slideTo(e.mainIndex),e.$boxes.eq(e.mainIndex).addClass("--active"),e.$boxes.on(e.settings.parent_trigger,(function(i){e.$boxes.removeClass("--active"),t(this).addClass("--active"),s.slideTo(t(this).index())}))):setTimeout(i,500)};i()}break;case"reycarousel":case"slider":if(!this.settings.carousel_target)return;if(this.settings.$target=t(".splide"+this.getSelectorDot(this.settings.carousel_target),this.settings.$targetContainer),!this.settings.$target.length||!this.$boxes.length)return;rey.hooks.addAction("rey/slider",((s,i)=>{this.settings.$target[0].isEqualNode(s.element)&&(i.on("move",(function(t,s,i){e.$boxes.removeClass("--active"),e.$boxes.eq(t).addClass("--active")})),e.$boxes.on(e.settings.parent_trigger,(function(e){i.go(t(e.currentTarget).index())})))}));break;case"tabs":if(this.settings.tabs_target){var n=this.settings.tabs_target;if(!this.$boxes.length)return;if(this.$scope.closest(".rey-mainNavigation--mobile").length&&(n+="-mobile-mega"),!rey.components.tabs)return;if(void 0===rey.components.tabs[n])return;var r=rey.components.tabs[n];r.goTo(this.mainIndex),this.$boxes.eq(this.mainIndex).addClass("--active"),this.$boxes.on(this.settings.parent_trigger,(function(e){"touchstart"===s.settings.parent_trigger&&e.preventDefault();var i=t(this).index();r.goTo(i)})),rey.hooks.addAction("tabs/changed/"+n,(function(t){e.$boxes.removeClass("--active"),e.$boxes.eq(t).addClass("--active")}))}break;case"parent":if(this.settings.$target=this.$scope.closest("[data-settings*='rey_slideshow']").children(".rey-section-slideshow"),!this.settings.$target.length||!this.$boxes.length)return;rey.hooks.addAction("rey/slider",((s,i)=>{this.settings.$target[0].isEqualNode(s.element)&&(e.slider=i,e.slider_type=!1,i.on("move",(function(t,s,i){e.$boxes.removeClass("--active"),e.$boxes.eq(t).addClass("--active")})),e.$boxes.on(e.settings.parent_trigger,(function(e){i.go(t(e.currentTarget).index())})))}))}t(document).on("keydown",(function(s){if(9!==s.keyCode){var i=t(".rey-toggleBox:focus",e.$boxesWrapper);i.length&&-1!==[13,32].indexOf(s.keyCode)&&(s.preventDefault(),i.trigger(e.settings.parent_trigger))}}))},this.getSelectorDot=function(t){var e=".";return 0===t.indexOf(".")&&(e=""),e+t},this.init()};rey.hooks.addAction("elementor/init",(function(s){var i=function(t){new e(t)};t.each({"reycore-toggle-boxes.default":i,"reycore-toggle-boxes.stacks":i},(function(t,e){s.registerElement({name:t,cb:e})}))}))}(jQuery);