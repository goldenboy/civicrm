;(function($) {

// Keep track of all header cells.
var cells = [];

// Attach to all headers.
$(document).ready(function() {
  $('table thead.sticky').each(function () {
    // Make all absolute positioned elements relative to the table.
    var height = $(this).parent('table').css('position', 'relative').height();
    
    // Find all header cells.
    $('th', this).each(function () {
      
      // Ensure each cell has an element in it.
      var html = $(this).html();
      if (html == ' ') {
	html = '&nbsp;';
      }
      if ($(this).children().size() == 0) {
	html = '<span>'+ html +'</span>';
      }
      
      // Clone and wrap cell contents in sticky wrapper that overlaps the cell's padding.
      $('<div class="sticky-header" style="position: fixed; display: none; top: 0px;">'+ html +'</div>').prependTo(this);
      var div = $('div.sticky-header', this).css({
	  'marginLeft': '-'+ $(this).css('paddingLeft'),
          'marginRight': '-'+ $(this).css('paddingRight'),
          'paddingLeft': $(this).css('paddingLeft'),
          'paddingTop': $(this).css('paddingTop'),
          'paddingRight': $(this).css('paddingRight'),
          'paddingBottom': $(this).css('paddingBottom')
	})[0];
      // Adjust width to fit cell and hide.
      $(div).css('paddingRight', parseInt($(div).css('paddingRight')) + $(this).width() - $(div).width() +'px');
      cells.push(div);
      
      // Get position.
      div.cell = this;
      div.table = $(this).parent('table')[0];
      div.stickyMax = height;
      div.stickyPosition = $(this).y();
    });
  });
});

// Track scrolling.
var scroll = function() {
  $(cells).each(function () {
    // Fetch scrolling position.
    var scroll = document.documentElement.scrollTop || document.body.scrollTop;
    var offset = scroll - this.stickyPosition - 4;
    if (offset > 0 && offset < this.stickyMax - 100) {
      $(this).css('display', 'block');
    }
    else {
      $(this).css('display', 'none');
    }
  });
};
$(window).scroll(scroll);
$(document.documentElement).scroll(scroll);

// Track resizing.
var resize = function () {
  $(cells).each(function () {
    // Get position.
    $(this).css({ 'position': 'relative', 'top': '0'});
    this.stickyPosition = $(this.cell).y();
    this.stickyMax = $(this.table).height();
  });
};
$(window).resize(resize);

// Track the element positions
$.fn.x = function(n) {
  var result = null;
  this.each(function() {
    var o = this;
    if (n === undefined) {
      var x = 0;
      if (o.offsetParent) {
	while (o.offsetParent) {
	  x += o.offsetLeft;
	  o = o.offsetParent;
	}
      }
      if (result === null) {
	result = x;
      } else {
	result = Math.min(result, x);
      }
    } else {
      o.style.left = n + 'px';
    }
  });
  return result;
};

$.fn.y = function(n) {
  var result = null;
  this.each(function() {
    var o = this;
    if (n === undefined) {
      var y = 0;
      if (o.offsetParent) {
	while (o.offsetParent) {
	  y += o.offsetTop;
	  o = o.offsetParent;
	}
      }
      if (result === null) {
	result = y;
      } else {
	result = Math.min(result, y);
      }
    } else {
      o.style.top = n + 'px';
    }
  });
  return result;
};

})(jQuery);