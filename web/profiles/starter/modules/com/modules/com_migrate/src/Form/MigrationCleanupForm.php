<?php

namespace Drupal\com_migrate\Form;

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
    	return 'com_migration_cleanup';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getEditableConfigNames() {
    	return ['com_migrate.settings'];
	}

	public function buildForm(array $form, FormStateInterface $form_state) {

		$form['message'] = [
			'#markup' => '<h4>' . $this->t('Choose the elements you\'d liked cleaned up.') . '</h4>',
		];

		$form['checkbox_wrapper'] = [
			'#type' => 'container',
			'#attributes' => [ 'style' => 'display:flex; justify-content: space-between; margin-bottom: 1rem; max-width: 400px;' ],
		];

		$form['checkbox_wrapper']['fields'] = [
			'#default_value' => TRUE,
			'#title' => $this->t('Fields'),
			'#type' => 'checkbox',
		];

		$form['checkbox_wrapper']['entities'] = [
			'#default_value' => TRUE,
			'#title' => $this->t('Entities'),
			'#type' => 'checkbox',
		];

		$form['checkbox_wrapper']['text_editors'] = [
			'#default_value' => TRUE,
			'#title' => $this->t('Text Editors'),
			'#type' => 'checkbox',
		];

		$form['submit'] = [
			'#submit' => array([$this, 'submitForm']),
			'#type' => 'submit',
			'#value' => t('Clean Up'),
		];

		return $form;
	}

	private function getBundleFields() {
		return [
			'page' => [
				'field_carousel_image',
				'field_carousel_text_position',
			],
			'article' => [
				'field_carousel_image',
				'field_carousel_text_position',
				'field_header_image',
				'field_blog_tags',
			],
			'event' => [
				'field_carousel_image',
				'field_carousel_text_position',
			],
			'staff' => [
				'field_staff_image',
			]
		];
	}

	private function removeEntityFields() {
		$bundles = $this->getBundleFields();
		$entityFieldManager = \Drupal::service('entity_field.manager');

		foreach($bundles as $bundle => $fields) {
			$field_manager = $entityFieldManager->getFieldDefinitions('node', $bundle);
			foreach($fields as $field) {
				if (isset($field_manager[$field])) {
					$field_manager[$field]->delete();
				}
			}

			drupal_set_message($this->t('Fields @fields deleted on @bundle node.', ['@fields' => implode(", ", $fields), '@bundle' => $bundle]), 'status');
		}
	}

	private function getUnnecessaryEntityTypes() {
		return ['webform'];
	}

	private function removeEntityTypes() {

		$entity_types = $this->getUnnecessaryEntityTypes();

		foreach($entity_types as $entity_id) {
			$content_type = \Drupal::entityManager()->getStorage('node_type')->load($entity_id);
			if ($content_type) {
				$content_type->delete();
				drupal_set_message($this->t('Deleted entity type @id', ['@id' => $entity_id]), 'status');
			}
		}
	}

	private function getOrderedEntities() {
		return [
			'page' => [
				'teaser' => [
					'body' => [
						'label' => 'hidden',
						'type' => 'text_summary_or_trimmed',
						'settings' => ['trim_length' => 180],
						'weight' => 1,
					]
				]
			],
			'staff' => [
				'teaser' => [
					'field_position' => [ 'weight' => 1 ],
					'field_office' => FALSE,
					'body' => FALSE
				],
				'default' => [
					'field_position' =>  [ 'weight' => 1 ],
					'field_office' =>  [ 'weight' => 2 ],
				]
			]
		];
	}

	private function reorderEntityViewDisplays() {
		$entities = $this->getOrderedEntities();
		foreach($entities as $entity_id => $entity) {
			foreach($entity as $view_mode => $fields) {
				$view_mode = \Drupal::entityTypeManager()->getStorage('entity_view_display')->load('node.'.$entity_id.'.'.$view_mode);
				foreach($fields as $field_id => $setting) {
					if (is_bool($setting) && $setting == FALSE) {
						// hide field
						$view_mode->removeComponent($field_id);
					} else {
						// update field weight
						$view_mode->setComponent($field_id, $setting);
					}
				}

				$view_mode->save();
			}
		}
	}

	private function updateTextEditor() {
		user_role_grant_permissions('content_administrator', array('use text format full_html'));
		user_role_grant_permissions('content_editor', array('use text format full_html'));

		/* Remove wysiwyg filter */
		$filter = FilterFormat::load('wysiwyg');
		$filter->disable();
		$filter->save();
		/* update nodes and blocks using wysiwyg to use full_html */
		$connection = \Drupal::database();
		$connection->update('node__body')->fields(['body_format' => 'full_html',])->condition('body_format', 'wysiwyg')->execute();
		$connection->update('block_content__body')->fields(['body_format' => 'full_html',])->condition('body_format', 'wysiwyg')->execute();

		drupal_set_message($this->t('Updated Role Permissions for Full HTML Filter'), 'status');
	}

	private function updateRolesWeight() {
		$role_weight_mapping = [
			'anonymous' => -10,
			'authenticated' => -9,
			'content_editor' => -8,
			'content_administrator' => -7,
			'administrator' => -6,
		];

		$roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();

		foreach($roles as $rid => $role) {
			$role->setWeight($role_weight_mapping[$rid]);
			$role->save();
		}
	}

	public function postMigrationCleanup($params = []) {
		if (empty($params) || in_array('fields', $params)) {
			$this->removeEntityFields();
		}

		if (empty($params) || in_array('entities', $params)) {
			$this->removeEntityTypes();
			$this->reorderEntityViewDisplays();
		}

		if (empty($params) || in_array('text_editors', $params)) {
			$this->updateTextEditor();
		}

		$this->updateRolesWeight();
	}

	/**
	 * {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$defaults = ['fields', 'entities', 'text_editors'];
		$params = [];

		foreach($defaults as $default) {
			if ($form_state->getValue($default)) {
				$params[] = $default;
			}
		}

		$this->postMigrationCleanup($params);
	}
}
