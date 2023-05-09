<?php

namespace Drupal\com_animation\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_example_text' widget.
 *
 * @FieldWidget(
 *   id = "com_carousel_animation_widget",
 *   module = "com",
 *   label = @Translation("Carousel Animation Widget"),
 *   field_types = {
 *     "com_carousel_animation"
 *   }
 * )
 */
class COMCarouselAnimationWidget extends WidgetBase {

	public function __construct($plugin_id, $plugin_definition, \Drupal\Core\Field\FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings) {
    	parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
 	}

 	/**
	 * {@inheritdoc}
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {;
		
		$element['animinfromleft'] = [
			'#type' => 'select',
			'#options' => com_animation_get_mui_animatable_classes(),
			'#weight' => 0,
			'#title' => t('Animation In From Left'),
			'#default_value' => isset($items[$delta]->animinfromleft) ? $items[$delta]->animinfromleft : NULL,
			'#attributes' => [
				'data-cbid' => $delta,
			],
		];
		
		$element['animinfromright'] = [
			'#type' => 'select',
			'#options' => com_animation_get_mui_animatable_classes(),
			'#weight' => 0,
			'#title' => t('Animation In From Right'),
			'#default_value' => isset($items[$delta]->animinfromright) ? $items[$delta]->animinfromright : NULL,
			'#attributes' => [
				'data-cbid' => $delta,
			],
		];
		
		$element['animouttoleft'] = [
			'#type' => 'select',
			'#options' => com_animation_get_mui_animatable_classes(),
			'#weight' => 0,
			'#title' => t('Animation Out To Left'),
			'#default_value' => isset($items[$delta]->animouttoleft) ? $items[$delta]->animouttoleft : NULL,
			'#attributes' => [
				'data-cbid' => $delta,
			],
		];
		
		$element['animouttoright'] = [
			'#type' => 'select',
			'#options' => com_animation_get_mui_animatable_classes(),
			'#weight' => 0,
			'#title' => t('Animation Out To Right'),
			'#default_value' => isset($items[$delta]->animouttoright) ? $items[$delta]->animouttoright : NULL,
			'#attributes' => [
				'data-cbid' => $delta,
			],
		];						

		return $element;
	}
}
