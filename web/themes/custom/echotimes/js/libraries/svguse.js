!function(root, factory) {
    "function" == typeof define && define.amd ?
    define([], function() {
        return root.svguse = factory();
    }) : "object" == typeof module && module.exports ? 
    module.exports = factory() : root.svguse = factory();    
}(this, function() {
	function neesPolyfill() {
		var ua = navigator.userAgent,
			ie = (/\bTrident\/[567]\b|\bMSIE (?:9|10)\.0\b/).test(ua), 
			webkit = ua.match(/\bAppleWebKit\/(\d+)\b/), 
			oldEdge = ua.match(/\bEdge\/12\.(\d+)\b/), 
			edge = (/\bEdge\/.(\d+)\b/).test(navigator.userAgent), 
			inIframe = window.top !== window.self;
		return ie || (oldEdge||[])[1] < 10547 || (webkit||[])[1] < 537 || edge&&inIframe;
	}
	
	function svguse() {
		return neesPolyfill() && 
			[].slice.apply(document.querySelectorAll('svg use:not([svguse-updated])')).forEach(function(u) { new SVGUse(u); });
	}
	
	function SVGUse(u) {
		this.u = u;
		this.hrefAttr = u.getAttribute('href') ? 'href' : 'xlink:href';
		this.hrefParams = this.getUseHrefParams(u);

		if (this.hrefParams.url) { 
			if (this.store.indexOf(this.hrefParams.url) === -1) {
				this.store.push(this.hrefParams.url);
				this.getRemoteSVG();
			} else {
				this.updateUse();
			}
		}
	}
	
	SVGUse.prototype.store = [];
	
	SVGUse.prototype.getRemoteSVG = function() {
		var that = this;
		var wrapper = document.createElement('div');
		var xhttp = new XMLHttpRequest();
		
		wrapper.style.cssText = 'display: none;';
		wrapper.setAttribute('svguse-replacement','');

		// AJAX get SVG cntent		
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) { 
				wrapper.innerHTML = this.responseText;
				that.injectSVG(wrapper);
				that.updateUse();
			}
		};
		
		xhttp.open("GET", this.hrefParams.url, true);
		xhttp.send();
	}
	
	SVGUse.prototype.injectSVG = function(html) {
		document.body.appendChild(html)
	}
	
	SVGUse.prototype.updateUse = function() {
		this.u.setAttribute(this.hrefAttr,'#'+this.u.getAttribute(this.hrefAttr).split('#')[1]);
		this.u.setAttribute('svguse-updated','')
	}
	
	SVGUse.prototype.getUseHrefParams = function() {
		var uHref = this.u.getAttribute(this.hrefAttr);
		var a = document.createElement('a');
		a.href = uHref;				
		return { 'url': uHref.slice(0,1) === '#' ? null : ((a.origin ? a.origin : '') + a.pathname), 'hash': a.hash };
	}
	
	return svguse;
});