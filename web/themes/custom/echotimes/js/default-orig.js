/**
 * polyfills
 */
// Element.matches
if (!Element.prototype.matches) { Element.prototype.matches = Element.prototype.msMatchesSelector; }

// Element.closest
if (!Element.prototype.closest) {
	Element.prototype.closest = function(s) {
		let el = this;
		if (!document.documentElement.contains(el)) return null;
		do {
        	if (el.matches(s)) return el;
            el = el.parentElement || el.parentNode;
        } while (el !== null && el.nodeType === 1);
        return null;
    };
}


/**
 * default theme js
 */
(function () {

	let main = document.querySelector('#main');

	// init svg for older browsers
	svguse();

	// open external links in new window
	[].slice.apply((main ? main : document).querySelectorAll('a')).forEach(function(a) {
		if (a.href && a.host && a.host !== window.location.host && !a.matches(".exclude") ) {
			a.addEventListener("click", function(e) {
				e.preventDefault();
				let newWindow = window.open();
				newWindow.opener = null;
				newWindow.location = a.href;
			});
		}
	});
})();




// FOUNDATION STUFF
(function($) {
	document.addEventListener("DOMContentLoaded", function(event) {
		// initialize foundation
		$(document).foundation();


		let $orbitHero = $('#block-hero .orbit');
		let $slides = $orbitHero.find('.orbit-container > .orbit-slide');
		$orbitHero.on('beforeslidechange.zf.orbit', function(evt,curr,next) {
			if ($slides.index(next) === 0) {
				$orbitHero.attr("aria-live", "off");
			}
		});


	});
})(jQuery);

/* Foundaiton Backwards Compatability */
/* TABS */
(function($) {
	$('[data-tab]').each(function() {
		let tabsContent = this.nextElementSibling && this.nextElementSibling.className.indexOf('tabs-content') > -1 ? this.nextElementSibling : null;
		if (tabsContent) {
			if (!this.id ) {
				let id = 'tabs-' + btoa(this.innerHTML).substring(0,16)
				this.id = id;
				tabsContent.setAttribute('data-tabs-content', id);
			}

			[].slice.apply(this.children).forEach(function(el,i) {
				el.className = el.className.replace('tab-title','tabs-title').replace('active','is-active');
				tabsContent.children[i].className = tabsContent.children[i].className.replace('content','tabs-panel').replace('active','is-active');
			});

			this.setAttribute('data-tabs','');
			this.removeAttribute('data-tab');
		}
	});
})(jQuery);

/* Accordions */
(function($) {
	$('.accordion').each(function() {
		$(this).find('.accordion-navigation').each(function() {
			let $this = $(this);
			let $title = $this.children('a').first();
			let $content = $title.next('.content');

			$this.addClass('accordion-item').attr('data-accordion-item', '');
			if ($content.hasClass('active')) { $this.addClass('is-active'); }
			$title.addClass('accordion-title');
			$content.addClass('accordion-content').attr('data-tab-content', '');
		});
	});
})(jQuery);
// END FOUNDATION STUFF



/**
 * Navigation menu, Hamburger, Fixed Header
 */
(function() {

	const navigationRegion = document.querySelector('#region-navigation');

	if (navigationRegion) {

		let navFixed = false;

		const navigation = navigationRegion.querySelector('#navigation');
		//const subTray = navigationRegion.querySelector('.sub-nav');
		const header = document.querySelector('#header');
		const subtray = document.querySelector('#menu-subtray');
		const largeMQ = window.matchMedia('(min-width:1201px)');
		const headerMQ = window.matchMedia('(min-width:901px)');
		const fixedHeaderToggle = function() {
			let height = header ? header.clientHeight : 0;
			if (headerMQ.matches) {
				if (navFixed && window.scrollY<=height) {
					navFixed = false;
					navigationRegion.classList.remove('fixed');
				} else if (!navFixed && window.scrollY>height) {
					navFixed = true;
					navigationRegion.classList.add('fixed');
				}
			} else {
				navFixed = false;
				navigationRegion.classList.remove('fixed');
			}
		};

		const navOpen = function() {
			navigationRegion.classList.add("open");
			navigation.style.height = navigation.scrollHeight + 'px';
			return navigation;
		}

		const navClose = function(immediate) {
			navigationRegion.classList.remove("open");
			navigation.style.height = immediate ? null : (navigation.scrollHeight + 'px');
			if (!immediate) { setTimeout(function() { navigation.style.height = '0px'; }, 30); }
			return navigation;
		}

		navigation.addEventListener('transitionend', function(e) { console.log()
			if (e.target === this && e.propertyName === 'height' && !largeMQ.matches) {
				var height = parseFloat(navigation.style.height);
				setTimeout(function() { if (navigation.style.height !== 'auto') { navigation.style.height = height>0 ? 'auto' : null; } }, 30);
			}
		});

		[].slice.apply(navigation.querySelectorAll('.flyout')).forEach(function(flyout) {
			flyout.previousElementSibling.addEventListener('click', function(e) {
				if (!largeMQ.matches) {
					e.preventDefault();
					if (flyout.className.indexOf('active')>-1) {
						flyout.classList.remove('active');
						console.log(flyout.scrollHeight);
						flyout.style.height = flyout.scrollHeight + 'px';
						setTimeout(function() { flyout.style.height = 0; }, 30);
					} else {
						flyout.classList.add('active');
						flyout.style.height = flyout.scrollHeight + 'px';
					}
				}
			})

			flyout.addEventListener('transitionend', function(e) {
				if (!largeMQ.matches && e.target === flyout && e.propertyName === 'height') {
					var height = parseFloat(flyout.style.height);

					if (!largeMQ.matches) {
						setTimeout(function() { flyout.style.height = height>0 ? 'auto' : null; }, 30);
					}
				}
			});
		});


		// trigger menu on hamburger click
		document.querySelector('#hamburger').addEventListener('click', function() {
			return navigation.clientHeight === 0 ? navOpen() : navClose();
		});


		// trig ger bottom tray on spyglass click
		[].slice.apply(navigationRegion.querySelectorAll('.icon-spyglass')).forEach(function(item) {
			item.addEventListener('click', function() {
				subtray.classList.toggle('active');
			});
		})

		// sticky nav menu on scroll
		largeMQ.addListener(function() {
			if (largeMQ.matches) {
				navClose(true);

				[].slice.apply(navigation.querySelectorAll('.flyout.active')).forEach(function(flyout) {
					flyout.classList.remove('active');
					flyout.style.height = null;
				});
			}
		});
		headerMQ.addListener(fixedHeaderToggle);
		window.addEventListener("scroll", fixedHeaderToggle);


		navigation.addEventListener("click", function(e) {
			let target = e.target.matches('li') ? e.target.childNodes[0] : e.target;
			let subMenu = target.nextElementSibling;
			let hasSubMenu = subMenu && subMenu.matches('ul');

			if (hasSubMenu) {
				e.preventDefault();
				subMenu.style.height = subMenu.style.height ? null : (subMenu.scrollHeight + "px");
			}
		});


	} // if navigation
})();



/**
 * Move page tabs into administration menu.
 */
(function(window) {
	if (typeof window.drupalSettings !== "undefined" && typeof window.drupalSettings.toolbar !== "undefined") {
    	const toolbar = document.querySelector('.toolbar-menu-administration > ul');
    	[].slice.apply(document.querySelectorAll('#block-subtheme-local-tasks .button-group a')).forEach(function(a) {
	    	let li = document.createElement('li');
	    	li.classList.add("menu-item","menu-item--expanded");
	    	a.classList.remove("button", "secondary", "active");
	    	li.appendChild(a);
			toolbar.appendChild(li);
	    });
	}
})(window);

/**
 * Click to close alert boxes
 */
Drupal.behaviors.srjcAdmin = {
	attach: function (context, settings) {
		// close drupal messages boxes
		[].slice.apply(document.querySelectorAll('#region-messages .callout')).forEach(function(box) {
			box.querySelector('button').addEventListener('click', function(e) {
				e.preventDefault();
				this.parentNode.classList.add("fly-out");
			});
		});
	}
};




/*** ADD Google Custom Site Search script ***/
(function() {
	if (drupalSettings.srjc && drupalSettings.srjc.google_cse && drupalSettings.srjc.google_cse.cx !== '') {
		let cx = drupalSettings.srjc.google_cse.cx;
		var gcse = document.createElement('script');
		gcse.type = 'text/javascript';
		gcse.async = true;
		gcse.src = 'https://cse.google.com/cse.js?cx=' + cx;
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(gcse, s);
	}
})();