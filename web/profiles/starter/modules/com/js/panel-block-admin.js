(function() {
	let grid = document.querySelector('#edit-field-grid-value');
	let gridSizeWrapper = document.querySelector('#edit-field-grid-size-wrapper');
	let handleChange = function() {console.log("pp")
		if (grid.checked) {
			gridSizeWrapper.classList.remove('visually-hidden');
		} else {
			gridSizeWrapper.classList.add('visually-hidden');
		}
	};
	
	grid.addEventListener('change', handleChange);
	handleChange();

})();