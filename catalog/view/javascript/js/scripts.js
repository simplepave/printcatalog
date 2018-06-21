$(document).ready(function(){

	$('select').styler({ selectSearch: true });
	
	$("h1").fitText(1.1, { minFontSize: '18px', maxFontSize: '70px' });
	
	$("div.main_title p").fitText(1.2, { minFontSize: '14px', maxFontSize: '36px' });
		
});

$(document).ready(function() {
	var owl = $('.reviews_slider');
    	owl.owlCarousel({
		margin:110,
		nav: true,
		loop: true,
		smartSpeed:500,
		responsive: {
			0: {
			items: 1,
			margin:0
			},
			760: {
			items: 2
			}
		}
	})
})

$(document).ready(function() {
	var owl = $('.slider_reviews');
    	owl.owlCarousel({
		margin:30,
		nav: true,
		loop: true,
		smartSpeed:500,
		responsive: {
			0: {
			items: 1,
			margin:0
			},
			700: {
			items: 2
			},
			1040: {
			items: 3
			}
		}
	})
})

$(document).ready(function(){
	
    var accordion = $(".accordion");
    if(accordion.length){
      accordion.each(function(){
        var all_panels = $(this).find('.ac_inner').hide();
        var all_titles = $(this).find('.ac_title');
        $(this).find('.ac_inner.active').slideDown();

        all_titles.on("click", function() {
          var acc_title = $(this);
          var acc_inner =  acc_title.next();

          if(!acc_inner.hasClass('active')){
             all_panels.removeClass('active').slideUp();
             acc_inner.addClass('active').slideDown();
             all_titles.removeClass('active');
             acc_title.addClass('active');
          } else {
            all_panels.removeClass('active').slideUp();
            all_titles.removeClass('active');
          }
        });
      });
    }
		
});


$(function(){
	$('input[placeholder], textarea[placeholder]').placeholder();
});

(function( $ ){
  $.fn.fitText = function( kompressor, options ) {
    var compressor = kompressor || 1,
        settings = $.extend({
          'minFontSize' : Number.NEGATIVE_INFINITY,
          'maxFontSize' : Number.POSITIVE_INFINITY
        }, options);
    return this.each(function(){
      var $this = $(this);
      var resizer = function () {
        $this.css('font-size', Math.max(Math.min($this.width() / (compressor*10), parseFloat(settings.maxFontSize)), parseFloat(settings.minFontSize)));
      };
      resizer();
      $(window).on('resize.fittext orientationchange.fittext', resizer);
    });
  };
})( jQuery );
