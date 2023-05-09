<?php

namespace Drupal\com_animation\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'field_example_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "com_carousel_animation_formatter",
 *   module = "com",
 *   label = @Translation("COM Carousel Animation Formatter"),
 *   field_types = {
 *     "com_carousel_animation"
 *   }
 * )
 */
class COMCarouselAnimationFormatter extends FormatterBase {
	/**
	 * {@inheritdoc}
	 */
	public function settingsSummary() {
    	$summary = [];
		$summary[] = $this->t('Displays the animation properties.');
		return $summary;
	}	
	
	/**
     * {@inheritdoc}
	 */
	public function viewElements(FieldItemListInterface $items, $langcode) {
    	$elements = [];

		foreach ($items as $delta => $item) {
			$markup = 'In: ' . $item->animinfromleft . '|' . $item->animinfromright . '<br />';
			$markup .= 'Out: ' . $item->animouttoleft . '|' . $item->animouttoright;
			$elements[$delta] = [ '#markup' => $markup ];
		}

		return $elements;
	}
}
