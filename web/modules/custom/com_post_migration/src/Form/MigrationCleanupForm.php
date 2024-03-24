<?php

namespace Drupal\com_post_migration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\node\Entity\Node;

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
			//'#attributes' => [ 'style' => 'display:flex; justify-content: space-between; margin-bottom: 1rem; max-width: 400px;' ],
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
					//'#description' => $this->t('Migrate all pages'),
					'#type' => 'checkbox',
					'#disabled' => TRUE,
				];
			}

			if (in_array('article', $existing_node_types)) {
				$form['checkbox_wrapper']['nodes_wrapper']['articles'] = [
					'#default_value' => FALSE,
					'#title' => $this->t('Articles'),
					//'#description' => $this->t('Migrate all articles'),
					'#type' => 'checkbox',
					'#disabled' => TRUE,
				];
			}

			if (in_array('event', $existing_node_types)) {
				$form['checkbox_wrapper']['nodes_wrapper']['events'] = [
					'#default_value' => FALSE,
					'#title' => $this->t('Events'),
					//'#description' => $this->t('Migrate all events'),
					'#type' => 'checkbox',
					'#disabled' => TRUE,
				];
			}

			if (in_array('staff', $existing_node_types)) {
				$form['checkbox_wrapper']['nodes_wrapper']['staff'] = [
					'#default_value' => FALSE,
					'#title' => $this->t('Staff'),
					//'#description' => $this->t('Migrate all staff'),
					'#type' => 'checkbox',
					'#disabled' => TRUE,
				];
			}

			if (in_array('agenda_item', $existing_node_types)) {
				$form['checkbox_wrapper']['nodes_wrapper']['agenda_items'] = [
					'#default_value' => FALSE,
					'#title' => $this->t('Agenda Items'),
					//'#description' => $this->t('Migrate all agenda items'),
					'#type' => 'checkbox',
				];
			}

			$form['checkbox_wrapper']['nodes_wrapper']['node_updated_date'] = [
				'#default_value' => FALSE,
				'#title' => $this->t('Node Updated Date'),
				'#description' => $this->t('Sync Updated Date on all Nodes'),
				'#type' => 'checkbox',
			];
		}

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
		$tables = ['node__body', 'node_revision__body', 'block_content__body', 'block_content_revision__body'];
		foreach($tables as $table) {
			$connection->update($table)->fields(['body_format' => 'full_html',])->condition('body_format', 'wysiwyg')->execute();
		}

		/* Remove wysiwyg filter */
		$filter = FilterFormat::load('wysiwyg');
		if ($filter) {
			$filter->disable();
			$filter->save();		
		}

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

	private function getNodes($type) {
		$db = \Drupal\Core\Database\Database::getConnection('default','second');
		$fields = [
			'node.nid as ID',
			'node.title as title',
			'node.created as created',
			'node.changed as changed',
			'node.uid as uid',
		];

		switch ($type) {
			case 'agenda_item':
				$fields = array_merge($fields, [
					'ANY_VALUE(location.field_location_value) as location',
					'ANY_VALUE(date.field_meeting_date_value) as date', 
					'ANY_VALUE(organization_term.name) as organization',
					'ANY_VALUE(fiscal_year_term.name) as fiscal_year', 
					'ANY_VALUE(agenda_file.uri) as agenda', 
					'ANY_VALUE(minutes_file.uri) as minutes',
					'GROUP_CONCAT(additional_materials_file.uri) as additional_materials'
				]);

				$where = "WHERE node.type = 'agenda_item'";

				$joins = [
					'JOIN field_data_field_location as location ON node.nid = location.entity_id',
					'JOIN field_data_field_meeting_date as date ON node.nid = date.entity_id',
					'JOIN field_data_field_organization as organization ON node.nid = organization.entity_id',
					'JOIN taxonomy_term_data as organization_term ON organization.field_organization_tid = organization_term.tid',
					'JOIN field_data_field_fiscal_year as fiscal_year ON node.nid = fiscal_year.entity_id',
					'JOIN taxonomy_term_data as fiscal_year_term ON fiscal_year.field_fiscal_year_tid = fiscal_year_term.tid',
					'LEFT JOIN field_revision_field_agenda_pdf as agenda ON node.nid = agenda.entity_id',
					'LEFT JOIN file_managed as agenda_file ON agenda.field_agenda_pdf_fid = agenda_file.fid', 
					'LEFT JOIN field_revision_field_minutes_pdf as minutes ON node.nid = minutes.entity_id',
					'LEFT JOIN file_managed as minutes_file ON minutes.field_minutes_pdf_fid = minutes_file.fid',
					'LEFT JOIN field_data_field_additional_materials_pdf as additional_materials ON node.nid = additional_materials.entity_id',
					'LEFT JOIN file_managed as additional_materials_file ON additional_materials.field_additional_materials_pdf_fid = additional_materials_file.fid'
				];
				break;
			default:
				$where = '';
				$joins = [];
				//
		}

		$sql = 'SELECT ' . implode(', ', $fields) . ' FROM node as node ' . implode(' ', $joins) . ' ' . $where . ' GROUP BY node.nid ORDER BY node.nid ASC';
		return $db->query($sql)->fetchAll();
	}

	public function postMigrationCleanup($params = []) {
		if (in_array('roles', $params)) {
			$this->updateRolesWeight();	
		}
		
		if (in_array('text_editors', $params)) {
			$this->updateTextEditor();
		}		

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

		if (in_array('agenda_items', $params)) {
      $items = $this->getNodes('agenda_item');

			$tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', "organization")->execute();
			$tids = array_merge($tids, \Drupal::entityQuery('taxonomy_term')->condition('vid', "fiscal_year")->execute());
			$terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
			$terms = array_reduce($terms, function($acc, $term) {
				$acc[$term->getName()] = ['id' => $term->id(), 'vocab' => $term->bundle()];
				return $acc;
			}, []);

      $batch = array(
        'title' => t('Creating Nodes'),
        'operations' => [ ['\Drupal\com_post_migration\CreateNodes::process', [$items, $terms] ], ],
        'finished' => '\Drupal\com_post_migration\CreateNodes::finishedCallback',
        'progress_message' => t('Processed @current out of @total.'),
        'init_message' => t('Batch is starting.'),
      );

      batch_set($batch);
    }

		if (in_array('node_updated_date', $params)) {
			$db_7 = \Drupal\Core\Database\Database::getConnection('default','second');
			$db_9 = \Drupal::database();
			$nodes = $db_7->query("SELECT nid, changed FROM node")->fetchAll();
			foreach($nodes as $node) {
				$db_9->update('node_field_data')->fields(['changed' => $node->changed])->condition('nid', $node->nid)->execute();

				$vid = $db_9->query("SELECT vid FROM node_field_revision WHERE nid = :nid ORDER BY vid DESC LIMIT 0, 1", [':nid' => $node->nid])->fetchField();
				$db_9->update('node_field_revision')->fields(['changed' => $node->changed])->condition('nid', $node->nid)->condition('vid', $vid)->execute();
			}
		}
	}


	/**
	 * {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$defaults = ['roles', 'text_editors', 'images', 'agenda_items', 'node_updated_date'];
		$params = [];
		
		foreach($defaults as $default) {
			if ($form_state->getValue($default)) { 
				$params[] = $default;
			}
		}
				
		$this->postMigrationCleanup($params);
	}
}





/*
$db_7 = \Drupal\Core\Database\Database::getConnection('default','second');
$db_9 = \Drupal::database();

$original = $db_7->query('SELECT nid, title FROM node ORDER BY nid ASC')->fetchAll();
$original = array_reduce($original, fn($acc, $item) => $acc + [$item->nid => $item->title], []);
$new = $db_9->query('SELECT nid, title FROM node_field_data ORDER BY nid ASC')->fetchAll();
$new = array_reduce($new, fn($acc, $item) => $acc + [$item->nid => $item->title], []);
		
$original_diff = [];
foreach($original as $key => $item) {
	if (!isset($new[$key]) || $new[$key] != $item) {
		$original_diff[] = $key;
	}
}

$new_diff = [];
foreach($new as $key => $item) {
	if (!isset($original[$key]) || $original[$key] != $item) {
		$new_diff[] = $key;
	}
}

print_r($original_diff);
print_r($new_diff);
*/