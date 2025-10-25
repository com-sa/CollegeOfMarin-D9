<?php

namespace Drupal\com\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class MigrationPrepForm extends ConfigFormBase {

	/**
	 * {@inheritdoc}
	*/
	public function getFormId() {
    	return 'com_migration_migration_prep';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getEditableConfigNames() {
    	return ['com_migration_prep.settings'];
	}

	public function buildForm(array $form, FormStateInterface $form_state) {

		$form['message'] = [
			'#markup' => '<h4>' . $this->t('Choose the element you\'d liked cleaned up.') . '</h4>',
		];

		$form['prep_action'] = [
			'#type' => 'radios',
			'#title' => $this->t('Action'),
			'#default_value' => 'permissions',
			'#options' => [
				'permissions' => $this->t('Revoke Stale Permissions'),
				'thing2' => $this->t('Thing 2'),
				'thing3' => $this->t('Thing 3'),
			],
		];






		/*$form['checkbox_wrapper'] = [
			'#type' => 'container',
		];

		$form['checkbox_wrapper']['roles'] = [
			'#default_value' => FALSE,			
			'#title' => $this->t('Roles'),
			'#type' => 'checkbox',
		];

		$form['checkbox_wrapper']['text_editors'] = [
			'#default_value' => FALSE,			
			'#title' => $this->t('Text Editors'),
			'#type' => 'checkbox',
		];

		$form['checkbox_wrapper']['images'] = [
			'#default_value' => FALSE,
			'#title' => $this->t('Image'),
      '#description' => $this->t('Migrate all images to media entities'),
			'#type' => 'checkbox',
		];

		if (!isset($_ENV['PANTHEON_ENVIRONMENT'])) {
			$existing_node_types = array_keys(\Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple());

			$form['checkbox_wrapper']['nodes_wrapper'] = [
				'#type' => 'fieldgroup',
				'#title' => $this->t('Nodes'),
				'#attributes' => [  ],
			];

			if (in_array('page', $existing_node_types)) {
				$form['checkbox_wrapper']['nodes_wrapper']['pages'] = [
					'#default_value' => FALSE,
					'#title' => $this->t('Pages'),
					'#type' => 'checkbox',
					'#disabled' => TRUE,
				];
			}

			if (in_array('article', $existing_node_types)) {
				$form['checkbox_wrapper']['nodes_wrapper']['articles'] = [
					'#default_value' => FALSE,
					'#title' => $this->t('Articles'),
					'#type' => 'checkbox',
					'#disabled' => TRUE,
				];
			}

			if (in_array('event', $existing_node_types)) {
				$form['checkbox_wrapper']['nodes_wrapper']['events'] = [
					'#default_value' => FALSE,
					'#title' => $this->t('Events'),
					'#type' => 'checkbox',
					'#disabled' => TRUE,
				];
			}

			if (in_array('staff', $existing_node_types)) {
				$form['checkbox_wrapper']['nodes_wrapper']['staff'] = [
					'#default_value' => FALSE,
					'#title' => $this->t('Staff'),
					'#type' => 'checkbox',
					'#disabled' => TRUE,
				];
			}

			if (in_array('agenda_item', $existing_node_types)) {
				$form['checkbox_wrapper']['nodes_wrapper']['agenda_items'] = [
					'#default_value' => FALSE,
					'#title' => $this->t('Agenda Items'),
					'#type' => 'checkbox',
				];
			}

			$form['checkbox_wrapper']['nodes_wrapper']['node_updated_date'] = [
				'#default_value' => FALSE,
				'#title' => $this->t('Node Updated Date'),
				'#description' => $this->t('Sync Updated Date on all Nodes'),
				'#type' => 'checkbox',
			];
		}*/

		$form['submit'] = [
			'#submit' => array([$this, 'submitForm']),
			'#type' => 'submit',
			'#value' => t('Save'),
		];

		return $form;
	}


	/*public function doMigrationPrep($params = []) {
		//if (in_array('roles', $params)) {}
	}*/

	private function removeStalePermissions() {
		$entity_type_manager = \Drupal::entityTypeManager();
		$permissions = array_keys(\Drupal::service('user.permissions')->getPermissions());
		$roles = $entity_type_manager->getStorage('user_role')->loadMultiple();
		foreach ($roles as $role) {
  		$role_permissions = $role->getPermissions();
  		$differences = array_diff($role_permissions, $permissions);
  		if ($differences) {
    		foreach ($differences as $permission) {	$role->revokePermission($permission); }
    		$role->save();
				\Drupal::logger('com:migration_prep')->notice('Stale permissions removed for %role role.', ['%role' => $role->label(),]);
			}
  	}
	}


	/**
	 * {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		/*$defaults = ['roles', 'text_editors', 'images', 'agenda_items', 'node_updated_date'];
		$params = [];
		
		foreach($defaults as $default) {
			if ($form_state->getValue($default)) { 
				$params[] = $default;
			}
		}
				
		$this->doMigrationPrep($params);*/


		$prep_action = $form_state->getValue('prep_action');
		
		if ($prep_action == 'permissions') {
			$this->removeStalePermissions();
  	}
	}
}