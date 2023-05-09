<?php

namespace Drupal\com_animation\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'field_example_rgb' field type.
 *
 * @FieldType(
 *   id = "com_carousel_animation",
 *   label = @Translation("Carousel Animation"),
 *   module = "com",
 *   description = @Translation("Allows addition of a Carousel Animation"),
 *   default_widget = "com_carousel_animation_widget",
 *   default_formatter = "com_carousel_animation_formatter"
 * )
 */
class COMCarouselAnimation extends FieldItemBase {
	/**
     * {@inheritdoc}
     */
	public static function schema(FieldStorageDefinitionInterface $field_definition) {
    	return [
			'columns' => [
				'animinfromleft' => [
					'type' => 'varchar',
					'length' => 255,
					'not null' => FALSE,					
				],
				'animinfromright' => [
					'type' => 'varchar',
					'length' => 255,
					'not null' => FALSE,					
				],
				'animouttoleft' => [
					'type' => 'varchar',
					'length' => 255,
					'not null' => FALSE,					
				],
				'animouttoright' => [
					'type' => 'varchar',
					'length' => 255,
					'not null' => FALSE,					
				],
			],
		];
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function isEmpty() {
		$anim_in_from_left = $this->get('animinfromleft')->getValue();
		$anim_in_from_right = $this->get('animinfromright')->getValue();
		$anim_out_to_left = $this->get('animouttoleft')->getValue();
		$anim_out_to_right = $this->get('animouttoright')->getValue();
		return ($anim_in_from_left === NULL || $anim_in_from_left === '') && 
			   ($anim_in_from_right === NULL || $anim_in_from_right === '') && 
			   ($anim_out_to_left === NULL || $anim_out_to_left === '') && 
			   ($anim_out_to_right === NULL || $anim_out_to_right === '');
	}

	/**
	 * {@inheritdoc}
	 */
	public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
		$properties = [];
		$properties['animinfromleft'] = DataDefinition::create('string')->setLabel(t('Animation In From Left'));;
		$properties['animinfromright'] = DataDefinition::create('string')->setLabel(t('Animation In From Right'));;
		$properties['animouttoleft'] = DataDefinition::create('string')->setLabel(t('Animation Out To Left'));;
		$properties['animouttoright'] = DataDefinition::create('string')->setLabel(t('Animation Out To Right'));;
		
		return $properties;
	}

}
