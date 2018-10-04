function updateViewportDimensions() {
	var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],x=w.innerWidth||e.clientWidth||g.clientWidth,y=w.innerHeight||e.clientHeight||g.clientHeight;
	return { width:x,height:y }
}
// setting the viewport width
var viewport = updateViewportDimensions();


var waitForFinalEvent = (function () {
	var timers = {};
	return function (callback, ms, uniqueId) {
		if (!uniqueId) { uniqueId = "Don't call this twice without a uniqueId"; }
		if (timers[uniqueId]) { clearTimeout (timers[uniqueId]); }
		timers[uniqueId] = setTimeout(callback, ms);
	};
})();

// how long to wait before deciding the resize has stopped, in ms. Around 50-100 should work ok.
var timeToWaitForLast = 100;

function loadGravatars() {
  // set the viewport using the function above
  viewport = updateViewportDimensions();
  // if the viewport is tablet or larger, we load in the gravatars
  if (viewport.width >= 768) {
  jQuery('.comment img[data-gravatar]').each(function(){
    jQuery(this).attr('src',jQuery(this).attr('data-gravatar'));
  });
	}
} // end function

//Responsive Nav
jQuery(document).ready(function($) {
    jQuery('#responsive-nav').click(function() {
          // Set the effect type
  var $effect = 'slide';

    // Set the options for the $effect type chosen
    var $options = { direction:'up' };

    // Set the duration (default: 400 milliseconds)
    var $duration = 500;

  jQuery('#main-navigation').toggle($effect, $options, $duration);
               
    });
});

jQuery(document).ready(function() {

    jQuery(window).scroll(function() {
        if (jQuery(this).scrollTop() > 500) {
            jQuery('.scrollToTop').fadeIn();
        } else {
            jQuery('.scrollToTop').fadeOut();
        }
    });

    //Click event to scroll to top
    jQuery('.scrollToTop').click(function() {
        jQuery('html, body').animate({
            scrollTop: 0
        }, 800);
        return false;
    });

});

var callback = function() {
    var e;
    var t;
    if (typeof window.innerWidth != "undefined") {
        e = window.innerWidth, t = window.innerHeight
    } else if (typeof document.documentElement != "undefined" && typeof document.documentElement.clientWidth != "undefined" && document.documentElement.clientWidth != 0) {
        e = document.documentElement.clientWidth, t = document.documentElement.clientHeight
    } else {
        e = document.getElementsByTagName("body")[0].clientWidth, t = document.getElementsByTagName("body")[0].clientHeight
    }
    jQuery("#main-navigation").addClass("nav-show");
    if (e < 1040) {
        jQuery("#main-navigation").removeClass("nav-show")
    }
};
jQuery(document).ready(callback);
jQuery(window).resize(callback)


jQuery(document).ready(function($) {

  /*
   * Let's fire off the gravatar function
   * You can remove this if you don't need it
  */
  loadGravatars();


}); /* end of as page load scripts */

jQuery(document).ready(function() {
    jQuery('.related-hide,author-hide').remove();
    jQuery( ".single-format-video p iframe,.single-format-video p embed" ).parent("p").addClass("video-container");
});