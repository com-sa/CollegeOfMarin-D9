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

		$form['checkbox_wrapper']['roles'] = [
			'#default_value' => TRUE,			
			'#title' => $this->t('Roles'),
			'#type' => 'checkbox',
		];

		$form['checkbox_wrapper']['text_editors'] = [
			'#default_value' => TRUE,			
			'#title' => $this->t('Text Editors'),
			'#type' => 'checkbox',
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

	private function updateTextEditor() {	
		user_role_grant_permissions('contributor', array('use text format full_html'));
		user_role_grant_permissions('editor', array('use text format full_html'));
	
		/* update nodes and blocks using wysiwyg to use full_html */
		$connection = \Drupal::database();
		$connection->update('node__body')->fields(['body_format' => 'full_html',])->condition('body_format', 'wysiwyg')->execute();		
		$connection->update('block_content__body')->fields(['body_format' => 'full_html',])->condition('body_format', 'wysiwyg')->execute();
	
		/* Remove wysiwyg filter */
		$filter = FilterFormat::load('wysiwyg');
		$filter->disable();
		$filter->save();		

		\Drupal::messenger()->addMessage($this->t('Updated Role Permissions for Full HTML Filter'), \Drupal::messenger()::TYPE_STATUS);
	}

	private function updateRolesWeight() {
		$role_weight_mapping = [
			'anonymous' => 0,
			'authenticated' => 1,
			'contributor' => 2,
			'editor' => 3,
			'administrator' => 4,
		];
	
		$roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();

		foreach($roles as $rid => $role) {
			$role->setWeight($role_weight_mapping[$rid]);
			$role->save();
		}
	}

	public function postMigrationCleanup($params = []) {
		if (empty($params) || in_array('roles', $params)) {
			$this->updateRolesWeight();	
		}
		
		if (empty($params) || in_array('text_editors', $params)) {
			$this->updateTextEditor();
		}		

    if (empty($params) || in_array('images', $params)) {
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
		$defaults = ['roles', 'text_editors', 'images'];
		$params = [];
		
		foreach($defaults as $default) {
			if ($form_state->getValue($default)) { 
				$params[] = $default;
			}
		}
				
		$this->postMigrationCleanup($params);
	}
}
