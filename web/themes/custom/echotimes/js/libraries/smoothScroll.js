// ------------------------- SmoothScroll ----------------------//
(function(window, document) {
	var startTime = null,
		scrollObj = {},
		extend = function(dest, source) {
  			for (var prop in source) { dest[prop] = (typeof source[prop] === 'object') ? extend(dest[prop], source[prop]) : source[prop]; }
  			return dest;
		},
		defaults = {
			duration: 300,
			callback: null
		},
		getDestination = function(elem) {
			var destination = null;
			if ( typeof elem === "number" ) {
				destination	= elem;
			} else if ( typeof elem === "string" ) {
				elem = document.querySelector(elem);
				if ( elem ) { destination = elem.getBoundingClientRect().top + (typeof window.scrollY !== "undefined" ? window.scrollY : document.documentElement.scrollTop); }
				if ( destination < 0 ) { destination = 0; }
			} else if ( typeof elem.className === "string" ) {
				destination = elem.getBoundingClientRect().top + (typeof window.scrollY !== "undefined" ? window.scrollY : document.documentElement.scrollTop);
			}

			return destination;
		}
		scroll = function() {
			var now = Date.now(),
				distance = Math.abs(scrollObj.startPos - scrollObj.destination),
				percentage = (now - startTime) / scrollObj.settings.duration,
				be = (scrollObj.startPos < scrollObj.destination) ? scrollObj.startPos + (distance * percentage) : scrollObj.destination + (distance * (1-percentage));

			if (now - startTime > scrollObj.settings.duration) {
				window.scrollTo(window.scrollX, scrollObj.destination);
				if ( scrollObj.settings.callback ) { scrollObj.settings.callback.call(scrollObj.elem); }
				scrollObj = {};
				return;
			} else {
				window.scrollTo(window.scrollX, be);
				window.requestAnimationFrame(function() { scroll( scrollObj ); });
			}
		};

	window.smoothScroll = function(elem, options) { 
		var destination = getDestination(elem),
			header = document.querySelector("header.header");

		scrollObj.destination = destination;
		scrollObj.settings = defaults;

		if ( scrollObj.destination > -1 ) {
			startTime = Date.now();
			extend(scrollObj.settings, options);			
			scrollObj.elem = elem;
			scrollObj.startPos = (typeof window.scrollY !== "undefined" ? window.scrollY : document.documentElement.scrollTop);
			scroll();	
		}
	};
})(window, this.document);