<?php

namespace Drupal\com_migrate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\redirect\Entity\Redirect;

class RedirectImporterForm extends ConfigFormBase {

	/**
	 * {@inheritdoc}
	*/
	public function getFormId() {
    	return 'com_redirect_importer';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getEditableConfigNames() {
    	return ['com_migrate.settings'];
	}

	public function buildForm(array $form, FormStateInterface $form_state) {
		$form['redirects'] = [
			'#type' => 'textarea',
			'#description' => $this->t('Paste in a csv file of redirects to import.'),
			'#title' => $this->t('Redirects'),
		];

		$form['submit'] = [
			'#type' => 'submit',
			'#value' => t('Submit'),
			'#submit' => array([$this, 'submitForm']),
		];

		return $form;
	}


	/**
	 * {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
    	$redirects = explode("\n", $form_state->getValue('redirects'));
    	$i=0;
		foreach($redirects as $redirect) {
			if (strlen(trim($redirect)) > 0 && strpos($redirect, ',') > -1) {
				$redirect_parts = explode(",", $redirect);
				$source = $redirect_parts[0];
				$target = $redirect_parts[1];

				if ($source) {
					if (strlen(trim($target)) == 0) { $target = \Drupal::config('system.site')->get('page.front'); }

					Redirect::create([
						"redirect_source" => $source, "redirect_redirect" => "internal:".$target,
						"language" => "und", "status_code" => "301"
					])->save();

					$i++;
				}
			}
		}

		if ($i>0) {
			drupal_set_message($this->t('@count redirects have been created.', ['@count' => $i]), 'status');
		} else {
			drupal_set_message($this->t('There were no valid redirects input.'), 'status');
		}
	}
}
