!function(e){"use strict";var a=e('<datalist id="rey_menu_classes" />').insertBefore("#menu-management");e.each({"--separated":"Add separator before","--badge-green":"Green Badge","--badge-red":"Red Badge","--badge-orange":"Orange Badge","--badge-blue":"Blue Badge","--badge-accent":"Accent Color Badge","--highlight":"Accent Colored","--bold":"Bold","--mobile-only":"Mobile Only","--desktop-only":"Desktop Only","--animate-cols":"Animate Columns","--scrollto":"Navigate to anchor"},(function(n,t){e('<option value="'+n+'">'+t+"</option>").appendTo(a)})),e("#menu-management input.edit-menu-item-classes").attr({list:"rey_menu_classes"}),e(document).on("menu-item-added",(function(a,n){e("input.edit-menu-item-classes",n).attr({list:"rey_menu_classes"})}))}(jQuery);