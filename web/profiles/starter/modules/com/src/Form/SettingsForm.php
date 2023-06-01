<?php

namespace Drupal\com\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class SettingsForm extends ConfigFormBase {

	/**
	 * {@inheritdoc}
	*/
	public function getFormId() {
    	return 'com_admin_settings';
	}

	/**
	 * {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
    	$this->config('com.settings')
			->set('facebook', $form_state->getValue('facebook'))
			->set('twitter', $form_state->getValue('twitter'))
			->set('youtube', $form_state->getValue('youtube'))
			->set('instagram', $form_state->getValue('instagram'))
			->set('linkedin', $form_state->getValue('linkedin'))
			->set('google_cse_cx', $form_state->getValue('google_cse_cx'))
			->set('disabled_layouts', $form_state->getValue('disabled_layouts'))
			->set('header_active_menu', $form_state->getValue('header_active_menu'))
			->set('footer_active_menu', $form_state->getValue('footer_active_menu'))			
			->save();

		parent::submitForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getEditableConfigNames() {
    	return ['com.settings'];
	}
	
	
	/**
	 * {@inheritdoc}
	 */
	protected function getTemplateFilesByName($key) {
		$files[''] = "None";
		$themes = ['echotimes','subtheme'];
		foreach($themes as $theme) {
			$scanned = \Drupal::service('file_system')->scanDirectory(drupal_get_path('theme', $theme).'/templates/includes/menus/'.$key, '/.*\.twig$/');
			foreach($scanned as $path => $scan) { $files[$path] = $theme . ' - ' . $scan->filename;	}
		}

		return $files;
	}

	public function buildForm(array $form, FormStateInterface $form_state) {
    	$config = $this->config('com.settings');
		$settings = $config->get('com.settings');


		/* SOCIAL */
		$form['social_media_links'] = [
			'#type' => 'details',
			'#open' => TRUE,
			'#title' => $this->t('Social Media Links'),
		];

		$form['social_media_links']['facebook'] = [
			'#type' => 'url',
			'#title' => $this->t('Facebook'),
			'#default_value' => $config->get('facebook'),
		];

		$form['social_media_links']['twitter'] = [
			'#type' => 'url',
			'#title' => $this->t('Twitter'),
			'#default_value' => $config->get('twitter'),
		];

		$form['social_media_links']['youtube'] = [
			'#type' => 'url',
			'#title' => $this->t('Youtube'),
			'#default_value' => $config->get('youtube'),
		];

		$form['social_media_links']['instagram'] = [
			'#type' => 'url',
			'#title' => $this->t('Instagram'),
			'#default_value' => $config->get('instagram'),
		];

		$form['social_media_links']['linkedin'] = [
			'#type' => 'url',
			'#title' => $this->t('Linkedin'),
			'#default_value' => $config->get('linkedin'),
		];




		/* GOOGLE */

		$form['google'] = [
			'#type' => 'details',
			'#open' => TRUE,
			'#title' => $this->t('Google CSE'),
		];

		$form['google']['google_cse_cx'] = [
			'#type' => 'textfield',
			'#title' => $this->t('CX'),
			'#default_value' => $config->get('google_cse_cx'),
		];


		$form['layouts'] = [
			'#type' => 'details',
			'#open' => TRUE,
			'#title' => $this->t('Layouts'),
		];

		$form['layouts']['disabled_layouts'] = [
			'#type' => 'checkboxes',
			'#title' => $this->t('Disable layouts'),
			'#description' => $this->t('Select the layouts you wish to disable.'),
			//'#default_value' => !empty($settings['disabled_layouts']) ? $settings['disabled_layouts'] : [],
			'#default_value' => $config->get('disabled_layouts'),
			'#options' => ['layout_' => 'Default', 'foundation_' => 'Foundation'],
		];



		$form['menus'] = [
			'#type' => 'details',
			'#open' => TRUE,
			'#title' => $this->t('Navigation'),
		];



		
		$header_files = $this->getTemplateFilesByName('header');
		$header_default_if_unset = array_search('default.html.twig', $header_files);
		$header_current = $config->get('header_active_menu');
		
		$form['menus']['header_active_menu'] = [
			'#type' => 'select',
			'#options' => $header_files,
			'#title' => t('Header Active Menu'),
			'#default_value' => !is_null($header_current) ? $header_current : $header_default_if_unset,
		];

		
		
		$footer_files = $this->getTemplateFilesByName('footer');
		$footer_default_if_unset = array_search('default.html.twig', $footer_files);
		$footer_current = $config->get('footer_active_menu');
		
		$form['menus']['footer_active_menu'] = [
			'#type' => 'select',
			'#options' => $footer_files,
			'#title' => t('Footer Active Menu'),
			'#default_value' => !is_null($footer_current) ? $footer_current : $footer_default_if_unset,
		];		


		return parent::buildForm($form, $form_state);
	}
}
