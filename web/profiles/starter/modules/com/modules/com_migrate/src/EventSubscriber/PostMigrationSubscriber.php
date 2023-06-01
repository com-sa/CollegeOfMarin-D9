<?php

namespace Drupal\com_migrate\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PostMigrationSubscriber.
 *
 * Run our user flagging after the last node migration is run.
 *
 * @package Drupal\com_migrate
 */
class PostMigrationSubscriber implements EventSubscriberInterface {

	/**
	 * Get subscribed events.
	 *
	 * @inheritdoc
	 */
	public static function getSubscribedEvents() {
    	$events[MigrateEvents::POST_IMPORT][] = ['onMigratePostImport'];
		return $events;
	}

	/**
	 * Updates all default flags on our users.
	 */
	private function performMigrationCleanup() {
		$form_state = new \Drupal\Core\Form\FormState();
		\Drupal::formBuilder()->submitForm('\Drupal\com_migrate\Form\MigrationCleanupForm', $form_state);
	}

	/**
	 * Check for our specified last node migration and run our flagging mechanisms.
	 *
	 * @param \Drupal\migrate\Event\MigrateImportEvent $event
	 *   The import event object.
	 */
	public function onMigratePostImport(MigrateImportEvent $event) {
    	if ($event->getMigration()->getBaseId() == 'd7_menu_links') {
			$this->performMigrationCleanup();
		}
	}
}
