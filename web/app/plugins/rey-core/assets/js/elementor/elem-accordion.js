!function(e){"use strict";var t=function(e){var t=e[0];if(t.classList.contains("rey-accClosed-yes")&&"undefined"!=typeof elementorFrontend&&elementorFrontend.elementsHandler){var l={accordion:"close",toggle:"open"};Object.keys(l).forEach((e=>{var n=e+".default";n===t.getAttribute("data-widget_type")&&(0,elementorFrontend.elementsHandler.elementsHandlers[n])().then(setTimeout((()=>{var n=t.querySelectorAll(".elementor-tab-title"),o=t.querySelectorAll(".elementor-tab-content");if("close"===l[e])n.forEach((e=>{e.classList.remove("elementor-active")})),o.forEach((e=>{e.classList.remove("elementor-active"),e.style.display=""}));else if("open"===l[e]){var a=t.classList.contains("all");n.forEach(((e,t)=>{(a||0===t)&&e.classList.add("elementor-active")})),o.forEach(((e,t)=>{(a||0===t)&&(e.classList.add("elementor-active"),e.style.display="block")}))}}),400))}))}};rey.hooks.addAction("elementor/init",(function(e){e.registerElement({name:"accordion.default",cb:t}),e.registerElement({name:"toggle.default",cb:t})}))}(jQuery);