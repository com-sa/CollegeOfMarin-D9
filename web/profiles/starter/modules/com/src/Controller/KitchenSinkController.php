<?php
namespace Drupal\com\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class KitchenSinkController extends ControllerBase {

	/**
	 * Returns a simple page.
	 *
	 * @return array
	 *   A simple renderable array.
	*/
	public function build() {		
	    return array(
			'#title' => 'Kitchen Sink',
		    '#allowed_tags' => ['form', 'input', 'div', 'h2', 'label', 'select', 'option'],
			'#theme' => 'kitchen_sink',
			'#attached' => [ 'library' => [ 'com/kitchen_sink', ], ],
		);
	}
	
	
	public function importerpage() {		
	    return \Drupal::formBuilder()->getForm('Drupal\com\Form\RedirectImporterForm');
	}
	
	
}