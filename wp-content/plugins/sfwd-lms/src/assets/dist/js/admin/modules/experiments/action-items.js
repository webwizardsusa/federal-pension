!function(){var t;(t=jQuery)(document).on("click",".button-action-items",(function(t){t.preventDefault()})),t(document).on("mouseover",".button-action-items, .action-items",(function(o){o.preventDefault();const e=t(this).closest("td, div"),i=e.find(".button-action-items"),n=i.position();e.css({position:"relative"}),e.find(".action-items").addClass("action-items--open").removeClass("action-items--closed").css({top:n.top+i.outerHeight()+12,right:e.outerWidth()/2-i.outerWidth()/2+2})})),t(document).on("mouseleave",".button-action-items, .action-items",(function(o){o.preventDefault(),setTimeout((function(o){0===t(".button-action-items:hover, .action-items:hover").length&&t(o).closest("td, div").find(".action-items").addClass("action-items--closed").removeClass("action-items--open")}),250,this)}))}();