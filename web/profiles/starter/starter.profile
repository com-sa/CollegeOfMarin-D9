<?php
/**
 * @file
 * Enables modules and site configuration for a standard site installation.
 */

use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Form\FormStateInterface;



/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function starter_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
	//$form['#submit'][] = 'starter_form_install_configure_submit';
	
	// setting default values 
	$form['site_information']['site_mail']['#default_value'] = 'noreply@marin.edu';
	$form['admin_account']['account']['name']['#default_value'] = 'admin';
	$form['admin_account']['account']['mail']['#default_value'] = $form['site_information']['site_mail']['#default_value'];
	$form['regional_settings']['site_default_country']['#default_value'] = 'US';
	$form['regional_settings']['date_default_timezone']['#default_value'] = 'America/Los_Angeles';
	$form['update_notifications']['enable_update_status_module']['#default_value'] = 1;
	$form['update_notifications']['enable_update_status_emails']['#default_value'] = 0;
	
	// hiding unecessary fields
	/*
	hide($form['site_information']['site_mail']);
	hide($form['admin_account']['account']['name']);
	hide($form['admin_account']['account']['mail']);
	hide($form['regional_settings']);
	hide($form['update_notifications']);
	*/
		
	// hiding unecessary descriptions 
	unset($form['site_information']['site_mail']['#description']);
}

/**
 * Submission handler to sync the contact.form.feedback recipient.
 */
/*function starter_form_install_configure_submit($form, FormStateInterface $form_state) {
  $site_mail = $form_state->getValue('site_mail');
  ContactForm::load('feedback')->setRecipients([$site_mail])->trustData()->save();
}*/


/**
 * Implements hook_form_FORM_ID_alter() for install_settings_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function starter_form_install_settings_form_alter(&$form, FormStateInterface $form_state) {
	$form['settings']['mysql']['username']['#default_value'] = 'root';
}