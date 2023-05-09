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
