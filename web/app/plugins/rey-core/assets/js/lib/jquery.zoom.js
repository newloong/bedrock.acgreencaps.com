/*!
	Zoom 1.7.21
	license: MIT
	http://www.jacklmoore.com/zoom
*/
!function(o){var t={url:!1,callback:!1,target:!1,duration:120,on:"mouseover",touch:!0,onZoomIn:!1,onZoomOut:!1,magnify:1};o.zoom=function(t,e,n,i){var u,c,a,r,l,m,s,f=o(t),h=f.css("position"),d=o(e);return t.style.position=/(absolute|fixed)/.test(h)?h:"relative",t.style.overflow="hidden",n.style.width=n.style.height="",o(n).addClass("zoomImg").css({position:"absolute",top:0,left:0,opacity:0,width:n.width*i,height:n.height*i,border:"none",maxWidth:"none",maxHeight:"none"}).appendTo(t),{init:function(){c=f.outerWidth(),u=f.outerHeight(),e===t?(r=c,a=u):(r=d.outerWidth(),a=d.outerHeight()),l=(n.width-c)/r,m=(n.height-u)/a,s=d.offset()},move:function(o){var t=o.pageX-s.left,e=o.pageY-s.top;e=Math.max(Math.min(e,a),0),t=Math.max(Math.min(t,r),0),n.style.left=t*-l+"px",n.style.top=e*-m+"px"}}},o.fn.zoom=function(e){return this.each((function(){var n=o.extend({},t,e||{}),i=n.target&&o(n.target)[0]||this,u=this,c=o(u),a=document.createElement("img"),r=o(a),l="mousemove.zoom",m=!1,s=!1;if(!n.url){var f=u.querySelector("img");if(f&&(n.url=f.getAttribute("data-src")||f.currentSrc||f.src,n.alt=f.getAttribute("data-alt")||f.alt),!n.url)return}c.one("zoom.destroy",function(o,t){c.off(".zoom"),i.style.position=o,i.style.overflow=t,a.onload=null,r.remove()}.bind(this,i.style.position,i.style.overflow)),a.onload=function(){var t=o.zoom(i,u,a,n.magnify);function e(o){t.init(),t.move(o),r.css("opacity",1)}function f(){r.css("opacity",0)}if("grab"===n.on)c.on("mousedown.zoom",(function(n){1===n.which&&(o(document).one("mouseup.zoom",(function(){f(),o(document).off(l,t.move)})),e(n),o(document).on(l,t.move),n.preventDefault())}));else if("click"===n.on)c.on("click.zoom",(function(n){return m?void 0:(m=!0,e(n),o(document).on(l,t.move),o(document).one("click.zoom",(function(){f(),m=!1,o(document).off(l,t.move)})),!1)}));else if("toggle"===n.on)c.on("click.zoom",(function(o){m?f():e(o),m=!m}));else if("mouseover"===n.on){var h;t.init(),c.on("mouseenter.zoom",(function(o){h=setTimeout((function(){e(o)}),350)})).on("mouseleave.zoom",(function(){clearTimeout(h),f()})).on(l,t.move)}n.touch&&c.on("touchstart.zoom",(function(o){o.preventDefault(),s?(s=!1,f()):(s=!0,e(o.originalEvent.touches[0]||o.originalEvent.changedTouches[0]))})).on("touchmove.zoom",(function(o){o.preventDefault(),t.move(o.originalEvent.touches[0]||o.originalEvent.changedTouches[0])})).on("touchend.zoom",(function(o){o.preventDefault(),s&&(s=!1,f())})),"function"==typeof n.callback&&n.callback.call(a)},a.setAttribute("role","presentation"),a.alt=n.alt||"",a.src=n.url}))},o.fn.zoom.defaults=t}(window.jQuery);