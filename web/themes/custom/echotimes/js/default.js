/**
 * polyfills
 */
// Element.matches
if (!Element.prototype.matches) Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;

// Element.closest
if (!Element.prototype.closest) { Element.prototype.closest = function(s) { var el = this; if (!document.documentElement.contains(el)) return null; do { if (el.matches(s)) return el; el = el.parentElement || el.parentNode; } while (el !== null && el.nodeType === 1); return null; }}


// Array.from
Array.from||(Array.from=function(){var r=Object.prototype.toString,n=function(n){return"function"==typeof n||"[object Function]"===r.call(n)},t=Math.pow(2,53)-1,e=function(r){var n=function(r){var n=Number(r);return isNaN(n)?0:0!==n&&isFinite(n)?(n>0?1:-1)*Math.floor(Math.abs(n)):n}(r);return Math.min(Math.max(n,0),t)};return function(r){var t=Object(r);if(null==r)throw new TypeError("Array.from requires an array-like object - not null or undefined");var o,a=arguments.length>1?arguments[1]:void 0;if(void 0!==a){if(!n(a))throw new TypeError("Array.from: when provided, the second argument must be a function");arguments.length>2&&(o=arguments[2])}for(var i,u=e(t.length),f=n(this)?Object(new this(u)):new Array(u),c=0;c<u;)i=t[c],f[c]=a?void 0===o?a(i,c):a.call(o,i,c):i,c+=1;return f.length=u,f}}());


//* smooth scroll *//
/*!function(t,n){var o=null,l={},e=function(t,n){for(var o in n)t[o]="object"==typeof n[o]?e(t[o],n[o]):n[o];return t},i={duration:500,callback:null},s=function(o){var l=null;return"number"==typeof o?l=o:"string"==typeof o?(o=n.querySelector(o),o&&(l=o.getBoundingClientRect().top+t.scrollY)):"string"==typeof o.className&&(l=o.getBoundingClientRect().top+t.scrollY),l};scroll=function(){var n=Date.now(),e=Math.ceil((n-o)/l.settings.duration*l.destination);e<l.destination?(t.scrollTo(t.scrollX,e),t.requestAnimationFrame(function(){scroll(l)})):(t.scrollTo(t.scrollX,l.destination),l.settings.callback&&l.settings.callback.call(l.elem),l={})},t.smoothScroll=function(t,n){l.destination=s(t),l.settings=i,l.destination&&(o=Date.now(),e(l.settings,n),l.elem=t,scroll())}}(window,this.document);*/
//* smooth scroll *//




// soon after page load
(function() {
	
	let page = document.querySelector('#page');
	let header = document.querySelector("#header");	
	let togglePaddingTop = function() {
		if (Foundation.MediaQuery.atLeast('xlarge')) {
			page.style.paddingTop = header.offsetHeight + "px";
		} else {
			page.style.removeProperty("padding-top");
		}		
	}
	let animatables = Array.from(document.querySelectorAll('.animatable'));
	let animatablesObserver = new IntersectionObserver(function(entries) {
		entries.forEach(function(entry) { if (entry.isIntersecting) { requestAnimationFrame(function() { entry.target.classList.add("animated"); }); } });
	}, { rootMargin: '0px 0px' });

	//init foundation
	jQuery(document).foundation();

	// ability to popup modal on page load
	//jQuery('.modal-on-load').foundation('reveal', 'open');

	// init svg for older browsers
	svguse();

	/* animated when scrolled into view (animate.css) */
	animatables.forEach(function(animatable) { animatablesObserver.observe(animatable); });
	
	
	// add padding to top of page to account for fixed header
	jQuery(window).on('changed.zf.mediaquery', togglePaddingTop);
	togglePaddingTop();
	
	// enable CSS custom properties for IE 11
	cssVars({ onlyLegacy: true, });
})();



/****
	header js
	hover and clicks
****/
(function(window, document, $) {
	let body = document.body;
	let page = document.querySelector('#page');
	let header = document.querySelector("#header");
	let schoolNav = document.querySelector("#school-nav");
	let topNav = document.querySelector("#top-nav");
	let $topNav = $("#top-nav");
	let subNav = document.querySelector("#sub-nav");
	let $subNav = $("#sub-nav");
	let hamburger = document.querySelector('.hamburger-menu');
	let touchNavBack = document.querySelector("#touch-nav-back")
	let isTouchDevice = !!('ontouchstart' in window);
	let scrollWatch = (function() {
		let hasClassMinimal = header.className.indexOf("minimal") > -1;
		if ( window.scrollY > (document.body.clientHeight / 6) ) {
			if ( !hasClassMinimal ) { header.classList.add("minimal"); }
		} else {
			if ( hasClassMinimal ) { header.classList.remove("minimal"); }
		}
	});
	let navHoverState = false;
	let subNavHoverState = false;


	if (topNav) {
		Array.from(topNav.querySelectorAll("a")).forEach(function(el) {
			el.addEventListener("mouseover", function() {
				let li = this.closest('li');
				let i = Array.from(li.parentNode.children).indexOf(li);
				Array.from(subNav.querySelectorAll("ul"))[i].classList.add("over");	
			});
			
			el.addEventListener("mouseout", function() {
				$subNav.find(".over").removeClass("over");
			});
			
			el.addEventListener("click", function(evt) {
				if ( document.documentElement.clientWidth <= 1008 ) {
					evt.preventDefault();
					var $li = $(this).parent(),
						i = $li.parent().children().index( $li );
	
					$subNav.find("ul").eq(i).addClass("active")
				}
	
			});
		});
	
		Array.from(topNav.querySelectorAll("ul")).forEach(function(ul) {
			ul.addEventListener("mouseover", function() {
				navHoverState = true;
				setTimeout(function() {
					if (navHoverState) {
						schoolNav.classList.add("over");
					}
				}, 200);
			});
	
			ul.addEventListener("mouseout", function() {
				navHoverState = false;
				
				requestAnimationFrame(function() {
					if (!navHoverState && !subNavHoverState) {
						schoolNav.classList.remove("over");
					}
				});
			});
		});
	}
	
	
	if (subNav) {
		subNav.addEventListener("mouseover", function() {
			subNavHoverState = true;
		});
		
		subNav.addEventListener("mouseout", function() {
			subNavHoverState = false;
			
			requestAnimationFrame(function() {
				if (!navHoverState && !subNavHoverState) {
					schoolNav.classList.remove("over");
				}
			});
		});	
		
		Array.from(subNav.querySelectorAll("ul")).forEach(function(el) {
			el.addEventListener("mouseover", function() {
				var i = Array.from(this.parentNode.children).indexOf(this);
				Array.from(topNav.querySelectorAll("li"))[i].classList.add("over");
			});
	
			el.addEventListener("mouseout", function() {
				let active = topNav.querySelector("li.over");
				if (active) {
					active.classList.remove("over");
				}
			});
		});
	}

	if (hamburger) {
		hamburger.addEventListener("click", function() { body.classList.toggle('menu-slide-in');  });
	}

	// close mobile menu when click outside
	page.addEventListener("click", function(evt) {
		if (body.className.indexOf('menu-slide-in') > -1 && evt.target === page) {
			body.classList.remove('menu-slide-in');
		}
	});

	if (touchNavBack) {
		touchNavBack.addEventListener("click", function(evt) {
			evt.preventDefault();
			$(this).siblings(".active").removeClass("active");
		});
	}

	if ( !isTouchDevice ) {
		header.classList.add("fixed")
		//header.nextElementSibling.style.paddingTop = header.outerHeight + "px";

		let threshold = window.matchMedia("(min-width: 1201px)");
		let scrollWatchWindow = (function (mql) {
			if (mql.matches) {
				window.addEventListener("scroll", scrollWatch);
				header.classList.add("fixed");
				//header.nextElementSibling.style.paddingTop = header.outerHeight + "px";
				scrollWatch();
			} else {
				header.classList.remove("minimal", "fixed");
				//header.nextElementSibling.style.removeProperty("padding-top");
				window.removeEventListener("scroll", scrollWatch);
			}
		});

		threshold.addListener(scrollWatchWindow);
		scrollWatchWindow(threshold);
	}
})(window, document, jQuery);


// misc
(function() {

	let alertBoxes = Array.from(document.querySelectorAll('.alert-box'));
	let scrollElements = Array.from(document.querySelectorAll('[data-scroll]'));

	// close alert boxes
	alertBoxes.forEach(function(el) {
		el.addEventListener("click", ".close", function() {
			this.parentNode.classList.add("fadeOutRight", "animate");
		});
	});

	// open external links in new window
	Array.from((main ? main : document).querySelectorAll('a')).forEach(function(a) {
		if (a.href && a.host && a.host !== window.location.host && !a.matches(".exclude") ) {
			a.addEventListener("click", function(e) {
				e.preventDefault();
				let newWindow = window.open();
				newWindow.opener = null;
				newWindow.location = a.href;
			});
		}
	});

	// ensure parent of .pull-up is at least that tall
	var fitPullUp = (function(clear) {
		let pullups = Array.from(document.querySelectorAll(".pull-up"));
		if (pullups.length) {
			pullups.forEach(function(el) {
				el.parentNode.style.minHeight = clear ? '' : (el.outerHeight(true) + "px");
			});
		}
	});

	if ( window.matchMedia ) {
		let mql = window.matchMedia("(min-width: 1201px)");
		let handleOrientationChange = function(mql) {
			fitPullUp(mql.matches ? null : true);
		};

		mql.addListener(handleOrientationChange);

		window.requestAnimationFrame(fitPullUp);
	} else {
		window.requestAnimationFrame(fitPullUp);
	}

	// illuminate obfuscated email addresses
	Array.from( document.querySelectorAll("a[data-email]") ).forEach(function(emailLink) {
		var emailAddress = emailLink.getAttribute("href").replace("/", "@");
		emailLink.href = "mailto:" + emailAddress;
		emailLink.innerHTML = emailAddress;
	});
	
	

	Array.from(document.querySelectorAll('.content-carousel')).forEach(function(carousel) {
		let wrapper = carousel.querySelector('.views-row').parentNode;
		let slidesToShow = carousel.hasAttribute('data-carousel-toshow') && parseFloat(carousel.getAttribute('data-carousel-toshow'));
		jQuery(wrapper).slick({ 	'slide': '.views-row', 'slidesToShow':  (slidesToShow > 0 ? slidesToShow : 4) });
	});
	

	/* CTA click trigger */
	Array.from(document.querySelectorAll('.block-type-cta > .field__item')).forEach(function(item) {
		item.addEventListener("click", function() {
			let a = item.querySelector('a');
			if (a) {
				a.click();
			}
		});
	});
})();



// default js
(function() {
	let loader = document.createElement('span');
	loader.classList.add("loader");
	loader.innerHTML = "Loading...";
	
	// events filtering
	document.addEventListener("click", function(evt) {
		if (evt.target.matches(".view .tab-controller li:not(.active)")) {
			let sheet = (function() {
				let style = document.createElement("style");
				style.appendChild(document.createTextNode(""));
				document.head.appendChild(style);
				style.className = "temporary";
				return style.sheet;
			})();
			let choice = evt.target.getAttribute('data-trigger');		
			let view = evt.target.closest('.view');
			let exposedForm = view.querySelector('.views-exposed-form');
			let select = exposedForm.querySelector("select");
			let button = exposedForm.querySelector("input[type='submit']");
	
			evt.target.appendChild(loader);
			sheet.insertRule(".ajax-progress { display: none !important; }", 0);
	
			select.value = choice;
			button.click();
		}
	});
})();



Drupal.behaviors.comAdmin = {
	attach: function (context, settings) {
		
		let header = document.querySelector('#header');
		let scrollElements = Array.from(context.querySelectorAll('[data-scroll]'));
		
		//init foundation
		jQuery(context).foundation();

		
		/**
		 * Click to close alert boxes
		 */
		Array.from(document.querySelectorAll('#region-messages .callout')).forEach(function(box) {
			box.querySelector('button').addEventListener('click', function(e) {
				e.preventDefault();
				this.parentNode.classList.add("fly-out");
			});
		});

		/**
		 * Move page tabs into administration menu.
		 */
		if (typeof window.drupalSettings !== "undefined" && typeof window.drupalSettings.toolbar !== "undefined") {
    		const toolbar = document.querySelector('.toolbar-menu-administration > ul');
			Array.from(document.querySelectorAll('#block-subtheme-local-tasks .button-group a')).forEach(function(a) {
		    	let li = document.createElement('li');
				li.classList.add("menu-item","menu-item--expanded");
				a.classList.remove("button", "secondary", "active");
				li.appendChild(a);
				toolbar.appendChild(li);
			});
		}
		
		
		
		
		if (context.tagName && context.matches('.view')) {
			let view = context;
			let tabController = view.querySelector('.tab-controller');
			let exposedForm = view.querySelector('.views-exposed-form');
			
			if (tabController && exposedForm) {
				let sheet = document.head.children[document.head.children.length-1];
				let loader = tabController.querySelector('.loader');
				let currentChoice = exposedForm.querySelector('select').value;
				
				Array.from(tabController.querySelectorAll('li')).forEach(function(li) {
					let choice = li.getAttribute('data-trigger');
					if (choice === currentChoice) {
						li.classList.add('active');
					} else {
						li.classList.remove('active');
					}
				});
				
				if (loader) {
					loader.parentNode.removeChild(loader);
				}
				
				if (sheet.matches('style.temporary')) {
					document.head.removeChild(sheet);
				}
			}
		}
		
		
		
		// enable scroll on click
		scrollElements.forEach(function(el) {
			el.addEventListener("click", function(evt) {
				let headerIsFixed = window.getComputedStyle(header).position.toLowerCase() === 'fixed';
				let selector = el.hasAttribute("data-scroll") ? el.getAttribute("data-scroll") : (el.href ? el.href : null);
				let destination = selector ? document.querySelector(selector) : null;
				if (destination) {
					evt.preventDefault();
					
					if (headerIsFixed) {
						destination = destination.getBoundingClientRect().top + window.pageYOffset - header.offsetHeight;
					}
					
					smoothScroll(destination, { duration: 150 });
				}
			});
		});
		
		
		
		// parallax items as they come into view
		Array.from(context.querySelectorAll('[data-parallax],[data-parallax-background]')).forEach(function (el) {
			new Parallax(el);
		});	

	}
};

(function() {
	/*** ADD Google Custom Site Search script ***/
	if (drupalSettings.com) {
		if (drupalSettings.com.google_cse && drupalSettings.com.google_cse.cx !== '') {
			let cx = drupalSettings.com.google_cse.cx;
			var gcse = document.createElement('script');
			gcse.type = 'text/javascript';
			gcse.async = true;
			gcse.src = 'https://cse.google.com/cse.js?cx=' + cx;
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(gcse, s);
		}
	}
})();
