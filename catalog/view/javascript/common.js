function getURLVar(key) {
	var value = [];

	var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	} else { 			// Изменения для seo_url от Русской сборки OpenCart 2x
		var query = String(document.location.pathname).split('/');
		if (query[query.length - 1] == 'cart') value['route'] = 'checkout/cart';
		if (query[query.length - 1] == 'checkout') value['route'] = 'checkout/checkout';

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

$(document).ready(function() {
	// Highlight any found errors
	$('.text-danger').each(function() {
		var element = $(this).parent().parent();

		if (element.hasClass('form-group')) {
			element.addClass('has-error');
		}
	});

	// Currency
	$('#form-currency .currency-select').on('click', function(e) {
		e.preventDefault();

		$('#form-currency input[name=\'code\']').val($(this).attr('name'));

		$('#form-currency').submit();
	});

	// Language
	$('#form-language .language-select').on('click', function(e) {
		e.preventDefault();

		$('#form-language input[name=\'code\']').val($(this).attr('name'));

		$('#form-language').submit();
	});

	/* Search */
	$('#search input[name=\'search\']').parent().find('input[name=\'submit-search\']').on('click', function(e) {
		var url = $('base').attr('href') + 'index.php?route=product/search';
		var value = $('header #search input[name=\'search\']').val();
		if (value) url += '&search=' + encodeURIComponent(value);
		location = url;
	});

	$('#search input[name=\'search\']').on('keydown', function(e) {
		if (e.keyCode == 13) {
			$('header #search input[name=\'search\']').parent().find('input[name=\'submit-search\']').trigger('click');
		}
	});

	// Menu
	$('#menu .dropdown-menu').each(function() {
		var menu = $('#menu').offset();
		var dropdown = $(this).parent().offset();

		var i = (dropdown.left + $(this).outerWidth()) - (menu.left + $('#menu').outerWidth());

		if (i > 0) {
			$(this).css('margin-left', '-' + (i + 10) + 'px');
		}
	});

	// Product List
	$('#list-view').click(function() {
		$('#content .product-grid > .clearfix').remove();

		$('#content .row > .product-grid').attr('class', 'product-layout product-list col-xs-12');
		$('#grid-view').removeClass('active');
		$('#list-view').addClass('active');

		localStorage.setItem('display', 'list');
	});

	// Product Grid
	$('#grid-view').click(function() {
		// What a shame bootstrap does not take into account dynamically loaded columns
		var cols = $('#column-right, #column-left').length;

		if (cols == 2) {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-6 col-md-6 col-sm-12 col-xs-12');
		} else if (cols == 1) {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-4 col-md-4 col-sm-6 col-xs-12');
		} else {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-3 col-md-3 col-sm-6 col-xs-12');
		}

		$('#list-view').removeClass('active');
		$('#grid-view').addClass('active');

		localStorage.setItem('display', 'grid');
	});

	if (localStorage.getItem('display') == 'list') {
		$('#list-view').trigger('click');
		$('#list-view').addClass('active');
	} else {
		$('#grid-view').trigger('click');
		$('#grid-view').addClass('active');
	}

	// Checkout
	$(document).on('keydown', '#collapse-checkout-option input[name=\'email\'], #collapse-checkout-option input[name=\'password\']', function(e) {
		if (e.keyCode == 13) {
			$('#collapse-checkout-option #button-login').trigger('click');
		}
	});

	// // tooltips on hover
	// $('[data-toggle=\'tooltip\']').tooltip({container: 'body'});

	// // Makes tooltips work on ajax generated content
	// $(document).ajaxStop(function() {
	// 	$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
	// });
});

// Cart add remove functions
var cart = {
	'add': function(product_id, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				$('.alert-dismissible, .text-danger').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					// Need to set timeout otherwise it wont update the total
					setTimeout(function () {
						$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
					}, 100);

					$('html, body').animate({ scrollTop: 0 }, 'slow');

					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'update': function(key, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/edit',
			type: 'post',
			data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var voucher = {
	'add': function() {

	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert-dismissible').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}

				$('#wishlist-total span').html(json['total']);
				$('#wishlist-total').attr('title', json['total']);

				$('html, body').animate({ scrollTop: 0 }, 'slow');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function() {

	}
}

var compare = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert-dismissible').remove();

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					$('#compare-total').html(json['total']);

					$('html, body').animate({ scrollTop: 0 }, 'slow');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function() {

	}
}

/* Agree to Terms */
$(document).delegate('.agree', 'click', function(e) {
	e.preventDefault();

	$('#modal-agree').remove();

	var element = this;

	$.ajax({
		url: $(element).attr('href'),
		type: 'get',
		dataType: 'html',
		success: function(data) {
			html  = '<div id="modal-agree" class="modal">';
			html += '  <div class="modal-dialog">';
			html += '    <div class="modal-content">';
			html += '      <div class="modal-header">';
			html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
			html += '        <h4 class="modal-title">' + $(element).text() + '</h4>';
			html += '      </div>';
			html += '      <div class="modal-body">' + data + '</div>';
			html += '    </div>';
			html += '  </div>';
			html += '</div>';

			$('body').append(html);

			$('#modal-agree').modal('show');
		}
	});
});

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			this.timer = null;
			this.items = new Array();

			$.extend(this, option);

			$(this).attr('autocomplete', 'off');

			// Focus
			$(this).on('focus', function() {
				this.request();
			});

			// Blur
			$(this).on('blur', function() {
				setTimeout(function(object) {
					object.hide();
				}, 200, this);
			});

			// Keydown
			$(this).on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			// Click
			this.click = function(event) {
				event.preventDefault();

				value = $(event.target).parent().attr('data-value');

				if (value && this.items[value]) {
					this.select(this.items[value]);
				}
			}

			// Show
			this.show = function() {
				var pos = $(this).position();

				$(this).siblings('ul.dropdown-menu').css({
					top: pos.top + $(this).outerHeight(),
					left: pos.left
				});

				$(this).siblings('ul.dropdown-menu').show();
			}

			// Hide
			this.hide = function() {
				$(this).siblings('ul.dropdown-menu').hide();
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}

			// Response
			this.response = function(json) {
				html = '';

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						this.items[json[i]['value']] = json[i];
					}

					for (i = 0; i < json.length; i++) {
						if (!json[i]['category']) {
							html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
						}
					}

					// Get all the ones with a categories
					var category = new Array();

					for (i = 0; i < json.length; i++) {
						if (json[i]['category']) {
							if (!category[json[i]['category']]) {
								category[json[i]['category']] = new Array();
								category[json[i]['category']]['name'] = json[i]['category'];
								category[json[i]['category']]['item'] = new Array();
							}

							category[json[i]['category']]['item'].push(json[i]);
						}
					}

					for (i in category) {
						html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

						for (j = 0; j < category[i]['item'].length; j++) {
							html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$(this).siblings('ul.dropdown-menu').html(html);
			}

			$(this).after('<ul class="dropdown-menu"></ul>');
			$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

		});
	}
})(window.jQuery);

/**
 * SimplePAVE
 */

jQuery(document).ready(function($){

	/**
	 * More Product
	 */

	$('#more-product').click(function(e){
		e.preventDefault();
	   var t = $(this);
	   var href = t.attr('href');

		$.ajax({
			url: href,
			dataType: 'json',
			beforeSend: function() {
				t.css({'filter': 'grayscale(100%) contrast(90%)'});
			},
			complete: function() {
				t.css({'filter': 'none'});
			},
			success: function(json) {
				if (json.success) {
					if (!json.next) t.hide();
					else t.attr('href', json.next.replace(/&amp;/g, "&"));

					$('#more-product-list').append(json.products);
			}},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

	/**
	 * More Articles
	 */

	$('#more-articles').click(function(e){
		e.preventDefault();
	   var t = $(this);
	   var href = t.attr('href');

		$.ajax({
			url: href,
			dataType: 'json',
			beforeSend: function() {
				t.css({'filter': 'grayscale(100%) contrast(90%)'});
			},
			complete: function() {
				t.css({'filter': 'none'});
			},
			success: function(json) {
				if (json.success) {
					if (!json.next) t.hide();
					else t.attr('href', json.next.replace(/&amp;/g, "&"));

					$('#articles ul').append(json.articles);
			}},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

	/**
	 * More Articles Related
	 */

	$('#more-articles-related').click(function(e){
		e.preventDefault();
	   var t = $(this);
	   var href = t.attr('href');

		$.ajax({
			url: href,
			dataType: 'json',
			beforeSend: function() {
				t.css({'filter': 'grayscale(100%) contrast(90%)'});
			},
			complete: function() {
				t.css({'filter': 'none'});
			},
			success: function(json) {
				if (json.success) {
					if (!json.next) t.hide();
					else t.attr('href', json.next.replace(/&amp;/g, "&"));

					$('#articles-related ul').append(json.articles);
			}},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

	/**
	 * Testimonials
	 */

	function testimonialsMore(t) {
	   var href = t.attr('href');

		$.ajax({
			url: href,
			dataType: 'json',
			beforeSend: function() {
				t.css({'filter': 'grayscale(100%) contrast(90%)'});
			},
			complete: function() {
				t.css({'filter': 'none'});
			},
			success: function(json) {
				if (json.success) {
					if (!json.next) t.hide();
					else t.attr('href', json.next.replace(/&amp;/g, "&"));

					$('#review').append(json.review);
			}},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}

	if ($('#review').length && $('#more-review').length)
		testimonialsMore($('#more-review'));

	$('#more-review').click(function(e){
		e.preventDefault();
	   testimonialsMore($(this));
	});

	$('#form-review').submit(function (e) {
		e.preventDefault();
		var t = $(this);

		$.ajax({
			url: t.attr('action'),
			type: 'post',
			dataType: 'json',
			data:  t.serialize(),
			success: function (json) {
				$('.text-success, .text-danger').remove();

				if (json['error']) {
					$('#block-button-review').before('<div class="text-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '</div>');
				}

				if (json['success']) {
					$('#block-button-review').before('<div class="text-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '</div>');

					$('#form-review select[name=\'product_id\']').prop('selectedIndex', 0).trigger('change');
					$('#form-review input[name=\'name\']').val('');
					$('#form-review input[name=\'email\']').val('');
					$('#form-review textarea[name=\'text\']').val('');
					$('#form-review input[name=\'rating\']:checked').prop('checked', false);

					setTimeout(function(){
						$('.text-success').remove();
					}, 10000);
				}
			}
		});
	});

	/**
	 * Rating
	 */

	function testimonialsRating(t) {
	   var href = t.attr('href');

		$.ajax({
			url: href,
			dataType: 'json',
			success: function(json) {
				if (json.success) $('#rating').html(json.rating);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}

	if ($('#rating').length && $('.ratings_crumbs').length)
		testimonialsRating($('.ratings_crumbs li:first a'));

	$('.ratings_crumbs li a').click(function(e){
		e.preventDefault();
	   testimonialsRating($(this));
	});

	/**
	 * More Enterprises Ratings
	 */

	$('#more-enterprises').click(function(e){
		e.preventDefault();
	   var t = $(this);
	   var href = t.attr('href');

		$.ajax({
			url: href,
			dataType: 'json',
			beforeSend: function() {
				t.css({'filter': 'grayscale(100%) contrast(90%)'});
			},
			complete: function() {
				t.css({'filter': 'none'});
			},
			success: function(json) {
				if (json.success) {
					if (!json.next) t.hide();
					else t.attr('href', json.next.replace(/&amp;/g, "&"));

					$('#enterprises > ul').append(json.enterprises);
			}},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

	// product-product

	if ($('#product-product').length)
		$('#more-enterprises').hide();

	/**
	 * Filter SP
	 */

	$('#filter-sp select[name=\'service\']').on('change', function() {
	 	var t = $(this), form = $('#filter-sp');

		$.ajax({
			url: form.attr('data-region'),
			type: 'post',
			dataType: 'json',
			data: form.serialize(),
			success: function (json) {
				if (json['success']) {
					var html = '', region = $('#filter-sp select[name=\'filter\']'), selected;

					$.each(json.regions, function(){
						selected = region.val() == this.region_id? ' selected="selected"': '';
						html += '<option value="' + this.region_id + '"' + selected + '>' + this.name + '</option>';
					});

					$.each(region.children('option'), function(){
						if ($(this).attr('value') != 0)
							$(this).remove();
					});

					region.append(html).trigger('refresh');
				}
			}
		});
	});

	if ($('#filter-sp').length){
		$('#filter-sp select').prop('selectedIndex', 0).trigger('refresh');

		var params = window.location.search.replace('?','').split('&')
			.reduce(function(p,e){
				var a = e.split('=');
				p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
				return p;
			},{});

		if (+params.filter)
			$('#filter-sp select[name=\'filter\'] option[value=\'' + +params.filter + '\']').prop('selected', true);

		if (+params.rating)
			$('#filter-sp select[name=\'rating\'] option[value=\'' + +params.rating + '\']').prop('selected', true);

		if (+params.cat)
			$('#filter-sp select[name=\'service\'] option[value=\'' + +params.cat + '\']').prop('selected', true);

		if ((+params.filter || +params.rating || +params.cat) && $('#more-product-list').length)
			$('html, body').animate({scrollTop: $('#more-product-list').offset().top }, 1100);
	}

	$('#filter-sp').submit(function (e) {
		e.preventDefault();
		var t = $(this), getParams = [];
		var service = +$('#filter-sp select[name=\'service\']').val();
		var categoryId = +$('#filter-sp input[name=\'category_id\']').val();

			var region = +$('#filter-sp select[name=\'filter\']').val();
			var rating = +$('#filter-sp select[name=\'rating\']').val();

			if (region) getParams.push('filter=' + region);
			if (rating) getParams.push('rating=' + rating);
			if (service) getParams.push('cat=' + service);
			// else getParams.push('cat=' + categoryId);

	 		var _url = $('#filter-sp select[name=\'service\'] option:selected')
	 			.attr('data-href').replace(/&amp;/g, "&");
	 		var sep = (~_url.indexOf('?'))? '&': '?';

	 		getParams = getParams.join('&');

	 		if (getParams)
	 			window.location = _url + sep + getParams;

		// if (service && service != categoryId) {
			//
		// } else {
		// 	$.ajax({
		// 		url: t.attr('action'),
		// 		dataType: 'json',
		// 		data:  t.serialize(),
		// 		success: function (json) {
		// 			if (json['success']) {
		// 				var more = $('#more-product');

		// 				if (!json.next) more.hide();
		// 				else more.show().attr('href', json.next.replace(/&amp;/g, "&"));

		// 				$('#more-product-list').html(json.products);
		// 				$('html, body').animate({scrollTop: $('#more-product-list').offset().top }, 1100);
		// 		}}
		// 	});
		// }
	});

	/**
	 * Call Out
	 */

	$('form.call_out').submit(function(e) {
		e.preventDefault();
      var t = $(this);
      var is = t.attr('class');

      $.ajax({
			url: t.attr('action'),
			type: 'post',
			data: t.serialize(),
			dataType: 'json',
			success: function(json) {// console.log(json);
				t.find('.text-success, .text-danger').remove();

				if (json.success) {
					// if (is == 'call_out')
					// 	t.hide().prev('h3').text(json.text_success);

					// if (is == 'question')
						t.find('input[type=\'submit\']').before('<div class="text-success">' + json.text_success + '</div>');
				}

				if (json.error) {
					t.find('input[type=\'submit\']').before('<div class="text-danger">' + json.error_enquiry + '</div>');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

});

/*menu*/
$(document).ready(function(){
	$.easing.def = 'easeInOutQuad';
	$('.children-trigger').on("click", function(e){
		var dropDown = $(this).next('ul');

		$('.category-children').not(dropDown).slideUp('slow');
		dropDown.slideToggle('slow');
		e.preventDefault();
	});
});