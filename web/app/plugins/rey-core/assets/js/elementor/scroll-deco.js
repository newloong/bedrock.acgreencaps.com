!function(){"use strict";var e=function(){document.querySelectorAll(".rey-scrollDeco").forEach((function(e){e.addEventListener("click",(function(e){e.preventDefault(),function(e){var t=e.getAttribute("data-target"),o=0;if(t){if("next"==t){var n=e.closest(".elementor-section.elementor-top-section");if(n||(n=e.closest(".e-con-top")),!n)return;var r=n.nextElementSibling;r&&(o=rey.dom.offset(r).top)}window.scroll({top:o,behavior:"smooth"})}else window.scroll({top:o,behavior:"smooth"})}(this)}))}))};document.addEventListener("rey-DOMContentLoaded",(function(t){e()}))}();