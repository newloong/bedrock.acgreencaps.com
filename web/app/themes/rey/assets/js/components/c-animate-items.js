!function(){"use strict";var t=function(t){var e="is-animated-entry";var n,a=(n=[],void 0===t||!1===t?document.querySelectorAll("."+e):(rey.validation.isObject(t)&&0===t.length||rey.dom.getNodeListArray(t).forEach((t=>{t.classList.contains(e)&&n.push(t)})),n));a.length&&rey.frontend.inView({target:a,cb:function(t,e){t.target&&(t.target.classList.add("--animated-in"),t.target.style.transitionDelay=.04*e+"s")},once:!0})};document.addEventListener("rey-DOMContentLoaded",(function(){t()})),rey.hooks.addAction("animate_items",(function(e){t(e)})),rey.hooks.addAction("post/loaded",(function(e){e.length&&t(e)}))}();