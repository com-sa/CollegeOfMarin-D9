<?php
namespace Drupal\com_migrate\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class MigrationCleanupController extends ControllerBase {
	/**
	 * Returns a simple page.
	 *
	 * @return array
	 *   A simple renderable array.
	*/
	public function build() {
		return \Drupal::formBuilder()->getForm('Drupal\com_migrate\Form\MigrationCleanupForm');
	}
}
