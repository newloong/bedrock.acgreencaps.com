!function(e){"use strict";var t=!1,a=[];e(document).on("click",".reyajfilter-dp-btn",(function(a){a.preventDefault();var i=e(this);e(".reyajfilter-dp-btn").not(i).removeClass("--active"),i.hasClass("--active")?(i.removeClass("--active"),t=!1):(i.addClass("--active"),t=!0)})),e(document).on("click",(function(i){e(i.target).closest(".reyajfilter-dp").length||t&&(e(".reyajfilter-dp-btn").removeClass("--active"),t=!1,a=[])})),e(document).on("keydown",(function(i){if(27===i.which){if(!t)return;e(".reyajfilter-dp-btn").removeClass("--active"),t=!1,a=[]}})),rey.hooks.addAction("ajaxfilters/started",(function(){a=[],e('.reyajfilter-dp-btn[data-keep-active="1"].--active').each((function(t,i){a.push(e(i).closest(".widget").attr("id"))}))})),rey.hooks.addAction("ajaxfilters/finished",(function(){e.each(a,(function(t,a){e("#"+a+" .reyajfilter-dp-btn").addClass("--active")}))}))}(jQuery);