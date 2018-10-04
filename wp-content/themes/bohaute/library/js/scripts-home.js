
var container = document.querySelector('.container');
var masonry;
imagesLoaded( container, function() {
masonry = new Masonry( container, {
// options
itemSelector: '.item',
gutter: ".gutter-sizer",
stamp: ".widget-area-wrap"
});
});


audiojs.events.ready(function() {
    audiojs.createAll();
});

var ias = jQuery.ias({
  container: ".container",
  item: ".item",
  pagination: ".pagination",
  next: ".pagination a"
});

ias.on('render', function(items) {
	jQuery(items).css({ opacity: 0 });
});

ias.on('rendered', function(items) {
	audiojs.events.ready(function() {
	    audiojs.createAll();
	});

	imagesLoaded( container, function() {
		masonry.appended(items);
	});
});

ias.extension(new IASSpinnerExtension());
ias.extension(new IASTriggerExtension({text: 'Load more posts',offset: 1}));
ias.extension(new IASNoneLeftExtension({text: '<span class="no-more-post">There are no more posts.</span>'}));
ias.extension(new IASPagingExtension());