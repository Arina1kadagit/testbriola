

$(function() {
	var Accordion = function(el, multiple) {
		this.el = el || {};
		this.multiple = multiple || false;

		// Variables privadas
		var links = this.el.find('.link');
		// Evento
		links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
	}

	Accordion.prototype.dropdown = function(e) {
		var $el = e.data.el;
			$this = $(this),
			$next = $this.next();

		$next.slideToggle();
		$this.parent().toggleClass('open');

		if (!e.data.multiple) {
			$el.find('.submenu').not($next).slideUp().parent().removeClass('open');
			
		};

	}	

	var accordion = new Accordion($('#accordion'), false);
});

$(function() {
	var Accordion = function(el, multiple) {
		this.el = el || {};
		this.multiple = multiple || false;

		// Variables privadas
		var links = this.el.find('.link2');
		// Evento
		links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
	}

	Accordion.prototype.dropdown = function(e) {
		var $el = e.data.el;
			$this = $(this),
			$next = $this.next();

		$next.slideToggle();
		$this.parent().toggleClass('open');

		if (!e.data.multiple) {
			$el.find('.submenu2').not($next).slideUp().parent().removeClass('open');
			
		};

	}	

	var accordion = new Accordion($('#accordion2'), false);
});

$(document).ready(function() {
	$('.header_burger').click(function(event){
		$('.header_burger,.header_menu').toggleClass('active');
		$('#graphic').toggleClass('active');

		if ($(".header_burger").hasClass("active") ) {
		document.getElementsByTagName('html')[0].style.overflow = 'hidden';
		}
		else {
			document.getElementsByTagName('html')[0].style.overflow = 'scroll';
		}	

	});

	
});








	



