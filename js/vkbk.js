/*
 
  Name: VKBK.js
  ================================================
  Base functions for all pages and page specific.
  
*/

// GLOBAL
// ===============================================

$(".tip").tooltip();	// tooltips

$.pjax.defaults.maxCacheLength = 0;
$(document).pjax('a[data-pjax]', '#pj-content', {timeout:5000});	// pJax navigaion
$(document).pjax('a[data-pjauth]', '#auth-col', {timeout:5000});

var ajxdir = 'ajax/';

var paginator_docs   = ajxdir+'docs-paginator.php';
var paginator_albums = ajxdir+'albums-paginator.php';
var paginator_video  = ajxdir+'videos-paginator.php';
var paginator_wall   = ajxdir+'wall-paginator.php';
var paginator_dialog = ajxdir+'dialog-paginator.php';
var queue_skip       = ajxdir+'queue-skipthis.php';
var music_grab       = ajxdir+'music-grab2queue.php';

var freewall_width = 300; // Default width, it replaced in albums with config value

// Fancybox v3
// =====================================

// close fancybox
function fbox_close(){
    $.fancybox.close();
}

// global - video container - maximize
// ===========================
function fbox_maximize(){
    $(".fbox-maximize").hide();
    $(".fbox-minimize").show();
    $('.fbox-controls').removeClass('fbox-minimized');
    $('.fancybox-slide--iframe .fancybox-content').css({"max-width":"80%","max-height":"80%","padding":"0","background-color":"#fff"});
    $('.fancybox-slide--iframe').css({"overflow":"hidden"});
    $('.fancybox-container').css({"width":"100%", "height":"100%", "top":"0", "bottom":"initial", "left":"0"});
    // For video we need scroll
    jQuery("html").addClass("fancybox-enabled");
    jQuery(".fancybox-container").addClass("fancybox-is-open");
    Cookies.set('gplayer_minimized','0',{ expires: 365 }); // Save state to cookies
}
// ===========================X

// global - video container - minimize
// ===========================
function fbox_minimize(){
    $(".fbox-maximize").show();
    $(".fbox-minimize").hide();
    $('.fancybox-slide--iframe .fancybox-content').css({"max-width":"100%","max-height":"100%","padding":"25px 5px 5px","background-color":"rgba(0,0,0,.9)"});
    $('.fancybox-slide--iframe').css({"overflow":"hidden"});
    $('.fancybox-container').css({"width":"430px", "height":"265px", "top":"initial", "bottom":"55px", "left":"60px"});
    $('.fbox-controls').addClass('fbox-minimized');
    // For video we need scroll
    jQuery("html").removeClass("fancybox-enabled");
    jQuery(".fancybox-container").removeClass("fancybox-is-open");
    Cookies.set('gplayer_minimized','1',{ expires: 365 }); // Save state to cookies
}
// ===========================X

// global - video container
// ===========================
function fbox_video_global( url, local ){
    var fbox_controls = '<div class="fbox-controls"><div class="fbox-close" onclick="javascript:fbox_close();"><i class="far fa-window-close fa-fw"></i></div><div class="fbox-minimize" onclick="javascript:fbox_minimize();"><i class="fa fa-compress fa-fw"></i></div><div class="fbox-maximize" onclick="javascript:fbox_maximize();"><i class="fa fa-expand fa-fw"></i></div></div>';
    var minimized = Cookies.get('gplayer_minimized'); // Restore state from cookies
    
    if(url != '' && local === 1){
	// Close any fancybox
	jQuery.fancybox.close();
	// Open fancybox container with URL
	jQuery.fancybox.open({
	    src			: url,
	    type		: 'iframe',
	    loop		: false,
	    keyboard		: false,
	    arrows		: false,
	    infobar		: false,
	    toolbar		: false,
	    animationEffect	: false,
	    transitionEffect	: false,
	    touch		: {
		vertical	: false,
	    },
	    hash		: false,
	    clickOutside	: false,
	    clickSlide		: false,
	    afterShow		: function(){
		// Add custom buttons
		jQuery(".fancybox-slide--iframe .fancybox-content").append(fbox_controls);
		if(minimized == 1){ fbox_minimize(); }
	    },
	});
    } else { return false; }
}
// ===========================X

// Modal: Create jQuery plugin
// ===========================

$.fn.fancyMorph = function( opts ) {

  var Morphing = function( $btn, opts ) {
    var self = this;

    self.opts = $.extend({
      type       : 'iframe',
      animationEffect : false,
      infobar    : false,
      buttons    : ['close'],
      smallBtn   : false,
      touch      : false,
      baseClass  : 'fancybox-morphing',
      afterClose : function() {
        self.close();
      }
    }, opts);

    self.init( $btn );
  };

  Morphing.prototype.init = function( $btn ) {
    var self = this;

    self.$btn = $btn.addClass('morphing-btn');

    self.$clone = $('<div class="morphing-btn-clone" />')
      .hide()
      .insertAfter( $btn );

    // Add wrapping element and set initial width used for positioning
    $btn.wrap( '<span class="morphing-btn-wrap"></span>' ).on('click', function(e) {
      e.preventDefault();

      self.start();
    });

  };

  Morphing.prototype.start = function() {
    var self = this;

    if ( self.$btn.hasClass('morphing-btn_circle') ) {
      return;
    }

    // Set initial width, because it is not possible to start CSS transition from "auto"
    self.$btn.width( self.$btn.width() ).parent().width( self.$btn.outerWidth() );

    self.$btn.off('.fm').on("transitionend.fm webkitTransitionEnd.fm oTransitionEnd.fm MSTransitionEnd.fm", function(e) {

      if ( e.originalEvent.propertyName === 'width' ) {
        self.$btn.off('.fm');

        self.animateBg();
      }

    }).addClass('morphing-btn_circle');

  };

  Morphing.prototype.animateBg = function() {
    var self = this;

    self.scaleBg();

    self.$clone.show();

    // Trigger repaint
    self.$clone[0].offsetHeight;

    self.$clone.off('.fm').on("transitionend.fm webkitTransitionEnd.fm oTransitionEnd.fm MSTransitionEnd.fm", function(e) {
      self.$clone.off('.fm');

      self.complete();

    }).addClass('morphing-btn-clone_visible');
  };

  Morphing.prototype.scaleBg = function() {
    var self = this;

    var $clone = self.$clone;
    var scale  = self.getScale();
    var $btn   = self.$btn;
    var pos    = $btn.offset();

    $clone.css({
      top       : pos.top  + $btn.outerHeight() * 0.5 - ( $btn.outerHeight() * scale * 0.5 ) - $(window).scrollTop(),
      left      : pos.left + $btn.outerWidth()  * 0.5 - ( $btn.outerWidth()  * scale * 0.5 ) - $(window).scrollLeft(),
      width     : $btn.outerWidth()  * scale,
      height    : $btn.outerHeight() * scale,
      transform : 'scale(' + ( 1 / scale ) + ')'
    });
  };

  Morphing.prototype.getScale = function() {
    var $btn    = this.$btn,
        radius  = $btn.outerWidth() * 0.5,
        left    = $btn.offset().left + radius - $(window).scrollLeft(),
        top     = $btn.offset().top  + radius - $(window).scrollTop(),
        windowW = $(window).width(),
        windowH = $(window).height();

    var maxDistHor  = ( left > windowW / 2 ) ? left : ( windowW - left ),
        maxDistVert = ( top > windowH / 2 )  ? top  : ( windowH - top );

    return Math.ceil(Math.sqrt( Math.pow( maxDistHor, 2 ) + Math.pow( maxDistVert, 2 ) ) / radius );
  };

  Morphing.prototype.complete = function() {
    var self = this;
    var $btn = self.$btn;

    $.fancybox.open({ src : $btn.data('src') || $btn.attr('href') }, self.opts);

    $(window).on('resize.fm', function() {
      //self.scaleBg();
    });
  };

  Morphing.prototype.close = function() {
    var self   = this;
    var $clone = self.$clone;

    self.scaleBg();

    $clone.one('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function(e) {
      $clone.hide();

      self.$btn.removeClass('morphing-btn_circle');
    });

    $clone.removeClass('morphing-btn-clone_visible');

    $(window).off('resize.fm');
  };

  // Init
  this.each(function() {
    var $this = $(this);

    if ( !$this.data("morphing") ) {
      $this.data( "morphing", new Morphing( $this, opts ) );
    }

  });

  return this;
};

$("[data-morphing]").fancyMorph({
  hash : 'morphing'
});
// ===========================X

// Fancybox v3 -- END
// =====================================X

// SETTINGS
// ===============================================
$(document).ready(function() {
    $('input[data-toggle]').bootstrapToggle();
    $('#auto-queue-photo').change(function() {
	$.get("ajax/settings-save-bool.php", { "option":"auto-queue-photo","v":$(this).prop('checked') } );
    });
    $('#auto-queue-audio').change(function() {
	$.get("ajax/settings-save-bool.php", { "option":"auto-queue-audio","v":$(this).prop('checked') } );
    });
    $('#play-local-video').change(function() {
	$.get("ajax/settings-save-bool.php", { "option":"play-local-video","v":$(this).prop('checked') } );
    });
    $('#start-local-video').change(function() {
	$.get("ajax/settings-save-bool.php", { "option":"start-local-video","v":$(this).prop('checked') } );
    });
});

// DOCUMENTS
// ===============================================

// Autoplay gif's on hover
function doc_gif(){
    $(".docs-gif").hover(function(){ $(this).attr("style","background-image:url('"+$(this).attr('data-src-local')+"')") }, function(){ $(this).attr("style","background-image:url('"+$(this).attr('data-pre-local')+"')") });
}

// Filter-box toggle
$(document).mouseup(function (e){
    var container = $(".docs-filter-box");
    if (!container.is(e.target) // if the target of the click isn't the container...
    && container.has(e.target).length === 0) // ... nor a descendant of the container
    {
	container.hide();
	container.unbind( 'click' );
    }
});

// AJAX PAGE RELOAD
// ===============================================

// Universal reload function for pages with jscroll and freewall
// In:
// apr_type - (string) type of page for callback functions [album|docs|video]
// apr_url - (string) page url and parameters
function ajax_page_reload(apr_type,apr_url){
    if(apr_type == 'album'){ var qurl = paginator_albums+apr_url; }
    if(apr_type == 'docs'){  var qurl = paginator_docs+apr_url; }
    if(apr_type == 'video'){ var qurl = paginator_video+apr_url; }
    if(apr_type == 'wall'){  var qurl = paginator_wall+apr_url; }
    if(apr_type == 'dialog'){  var qurl = paginator_dialog+apr_url; }
    
    if(apr_url.length > 0){
    jQuery.ajax({
	async : false,
	cache : false,
	method : "GET",
	url : qurl
    }).done( function(data){
	if(apr_type == 'album'){
	    window.history.pushState({}, "VKBK", "/albums.php?page=0&id="+album); // Push URL
	    apr_album_callback(data,apr_type);
	}
	if(apr_type == 'docs' ){ apr_docs_callback(data,apr_type); }
	if(apr_type == 'video' ){ apr_video_callback(data,apr_type); }
	if(apr_type == 'wall' ){ apr_wall_callback(data,apr_type); }
	if(apr_type == 'dialog' ){ apr_dialog_callback(data,apr_type); }
    });
    } else {
	console.log("APR: Skipped "+apr_type);
    }
}

// Callback function for album
// In:
// data - (object) Response data
// type - (string) [album]
function apr_album_callback(data,type){
    var list = jQuery("#freewall");
    list.html(data);
    freewill(new Freewall("#freewall"),true,'chalb');
    apr_jscroller(type,list);
}

// Callback function for documents
// In:
// data - (object) Response data
// type - (string) [docs]
function apr_docs_callback(data,type){
    var list = jQuery("#docs-list");
    list.html(data);
    apr_jscroller(type,list);
    doc_gif();
    $(".tip").tooltip();
}

// Callback function for video
// In:
// data - (object) Response data
// type - (string) [video]
function apr_video_callback(data,type){
    var list = jQuery("#video-list");
    list.html(data);
    apr_jscroller(type,list);
}

// Callback function for wall
// In:
// data - (object) Response data
// type - (string) [wall]
function apr_wall_callback(data,type){
    var list = jQuery("#wall-list");
    list.html(data);
    apr_jscroller(type,list);
}

// Callback function for dialog
// In:
// data - (object) Response data
// type - (string) [wall]
function apr_dialog_callback(data,type){
    var list = jQuery("#dialog-data");
    console.log('APR:Dialog - changed');
    jQuery("#dialog-default").css("height","0px");
    jQuery("#dialog-none").hide();
    list.html(data);
}

// jScroller init function
// In:
// type - (string) [album|docs|video|wall]
// selector - (object) jscroll block selector
function apr_jscroller(type,selector){
    selector.jscroll({
	debug: true,
	refresh: true,
	nextSelector: 'div.paginator-next > a:last',
	padding: 50,
	callback: function(){
	    if(type == 'album'){ apr_jscroller_album(); }
	    if(type == 'docs'){  apr_jscroller_docs(); }
	    if(type == 'video'){ apr_jscroller_video(); }
	    if(type == 'wall'){  apr_jscroller_wall(); }
	}
    });
    if(type == 'wall'){  apr_jscroller_wall(); }
}

// jScroller callback - album
function apr_jscroller_album(){
    var pval = jQuery("div.paginator-next:last .paginator-val").html();
    // (Re)Init freewall for the last page
    freewill(new Freewall("#freewall > .scroll-inner > .jscroll-added:last"),true,'page '+pval);
}

// jScroller callback - documents
function apr_jscroller_docs(){
    var pval = jQuery("div.paginator-next:last .paginator-val").html();
    if($.isNumeric(pval)){ urlCommands.urlPush({page:pval}); }
    doc_gif();
}

// jScroller callback - video
function apr_jscroller_video(){
    var pval = jQuery("div.paginator-next:last .paginator-val").html();
    if($.isNumeric(pval)){ urlCommands.urlPush({page:pval}); }
}

// jScroller callback - wall
function apr_jscroller_wall(){
    var pval = jQuery("div.paginator-next:last .paginator-val").html();
    
    // Set wall_block selector for page 1 and page N+
    if($(".jscroll-added").length){
	var wall_block = $(".jscroll-added").filter(":last");
    } else {
	var wall_block = $(".jscroll-inner");
    }
    console.log('JSC pval '+jQuery("div.paginator-next:last > a:last").attr("href"));
    if($.isNumeric(pval)){ urlCommands.urlPush({page:pval}); }
    wall_block.find(".free-wall").each(function(){	// For each image container
	var images = $(this).find('.brick.wpost');		// Get images
	if(images.length == 1){				// if solo image - call Freewall and set min-width
	    $(this).justifiedGallery({
		rowHeight: 500, maxRowHeight: 500, captions: false, margins: 5
	    });
	    $(this).justifiedGallery('norewind');	// Process only new items
	}
	if(images.length >= 2){				// if 2 or more images - call JfG
	    $(this).justifiedGallery({
		rowHeight: 200, maxRowHeight: 200, captions: false, margins: 5
	    });
	    $(this).justifiedGallery('norewind');	// Process only new items
	}
    });
}

// (Re)Initialize Freewall
function freewill(container,re,debug){
    container.reset({
	selector: '.brick',
	animate: false,
	cellW: freewall_width,
	cellH: 'auto',
	keepOrder: true,
	onResize: function() {
	    container.fitWidth();
	}
    });
    
    if(re === true){
	$(window).trigger("resize");
    }
}

// QUEUE
// ===============================================

// SkipIt
// u - url for ajax script, i - id of element to hide
function skipit(u,i){
    if(u.length > 0){
	jQuery.ajax({
	    async : false,
	    cache : false,
	    method : "GET",
	    url : queue_skip+"?"+u
	}).done( function(data){
	    // Hide item in list
	    jQuery("#"+i).hide();
	    jQuery(".hosterror").hide();
	});
    }
}

// MUSIC
// ===============================================

// GrabIt
// i - id of element
function grabit(i){
	jQuery.ajax({
	    async : false,
	    cache : false,
	    method : "POST",
	    data : {
		id : i,
		artist: jQuery("#"+i+"-artist").val(),
		title: jQuery("#"+i+"-title").val(),
		duration: jQuery("#"+i+"-duration").val(),
		uri: jQuery("#"+i+"-uri").val()
	    },
	    url : music_grab
	}).done( function(data){
	    var r = jQuery.parseJSON(data);
	    if(r.error == false){
		jQuery("#"+i+" td:last-child").append(r.msg);
		jQuery("#"+i+" td:last-child input[type=button]").hide();
	    } else {
		jQuery("#"+i+" td:last-child").append("<br/>"+r.msg);
	    }
	});
}