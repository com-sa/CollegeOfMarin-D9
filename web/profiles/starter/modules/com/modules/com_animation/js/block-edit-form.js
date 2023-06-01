(function() {
	function handleCarouselInput(context) {
		let carouselInput = context.querySelector('input[name="carousel"]');
		let carouselToShowWrapper = context.querySelector('.form-item-carousel-toshow');
				
		if (carouselInput && carouselToShowWrapper) {
			let isCarousel = carouselInput.checked;
			
			if (!isCarousel)	{
				carouselToShowWrapper.classList.add('hidden');
			}
			
			carouselInput.addEventListener('change', function() {
				if (carouselInput.checked) {
					carouselToShowWrapper.classList.remove('hidden');
				} else {
					carouselToShowWrapper.classList.add('hidden');
				}
			});
		}
	}

	handleCarouselInput(document);

	Drupal.behaviors.com_animation_block_edit_form = {
		attach: function (context, settings) {
			console.log(context.tagName);
			if (context.tagName !== "undefined" && context.tagName.toLowerCase() === "form") {
				handleCarouselInput(context);
			}
		}	
	};
})();