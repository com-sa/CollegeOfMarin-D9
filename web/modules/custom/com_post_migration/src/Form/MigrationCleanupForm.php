<?php

namespace Drupal\com_post_migration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\filter\Entity\FilterFormat;

class MigrationCleanupForm extends ConfigFormBase {

	/**
	 * {@inheritdoc}
	*/
	public function getFormId() {
    	return 'com_post_migration_migration_cleanup';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getEditableConfigNames() {
    	return ['com_post_migration.settings'];
	}

	public function buildForm(array $form, FormStateInterface $form_state) {

		$form['message'] = [
			'#markup' => '<h4>' . $this->t('Choose the elements you\'d liked cleaned up.') . '</h4>',
		];

		$form['checkbox_wrapper'] = [
			'#type' => 'container',
			'#attributes' => [ 'style' => 'display:flex; justify-content: space-between; margin-bottom: 1rem; max-width: 400px;' ],
		];

		$form['checkbox_wrapper']['images'] = [
			'#default_value' => TRUE,
			'#title' => $this->t('Image'),
      '#description' => $this->t('Migrate all images to media entities'),
			'#type' => 'checkbox',
		];

		$form['submit'] = [
			'#submit' => array([$this, 'submitForm']),
			'#type' => 'submit',
			'#value' => t('Migrate'),
		];

		return $form;
	}

	public function postMigrationCleanup($params = []) {
		/*if (empty($params) || in_array('fields', $params)) {
			$this->removeEntityFields();
		}

		if (empty($params) || in_array('entities', $params)) {
			$this->removeEntityTypes();
			$this->reorderEntityViewDisplays();
		}

		if (empty($params) || in_array('text_editors', $params)) {
			$this->updateTextEditor();
		}

		$this->updateRolesWeight();*/


    if (in_array('images', $params)) {
      // get all images in directory
      $uri = rtrim(\Drupal::service('file_url_generator')->generateString("public://"), '/');
      $files = \Drupal::service('file_system')->scanDirectory(DRUPAL_ROOT . $uri, '/.*\.(gif|jpe?g|png|webp|pdf|doc|txt)$/', ['recurse' => false,]);

      $batch = array(
        'title' => t('Updating Images to Media...'),
        'operations' => [ ['\Drupal\com_post_migration\ImagesToMedia::process', [$files] ], ],
        'finished' => '\Drupal\com_post_migration\ImagesToMedia::finishedCallback',
        'progress_message' => t('Processed @current out of @total.'),
        'init_message' => t('Batch is starting.'),
      );

      batch_set($batch);
    }
	}


	/**
	 * {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		//$defaults = ['fields', 'entities', 'text_editors'];
    $defaults = ['images'];
		$params = [];

		foreach($defaults as $default) {
			if ($form_state->getValue($default)) {
				$params[] = $default;
			}
		}

		$this->postMigrationCleanup($params);
	}
}
