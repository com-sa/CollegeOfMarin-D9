<?php

/**
 * Provides a 'Hero' Block
 *
 * @Block(
 *   id = "com_hero_block",
 *   admin_label = @Translation("Hero"),
 * )
 */

namespace Drupal\com_hero\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;

class COMHeroBlock extends BlockBase {
	/**
	* {@inheritdoc}
	*/


	private function getImageFromMedia($media_target) {
		$image = NULL;
		if (count($media_target)) {
			$media = Media::load($media_target[0]['target_id']);
			if ($media) {
				$media_field = $media->get('field_media_image')->first()->getValue();
				$file = File::load($media_field['target_id']);			
				$image = $file ? file_create_url($file->getFileUri()) : NULL;
			}
		}
		
		return $image;
	}


	public function build() {
		$theme = 'carousel';
		$language = \Drupal::languageManager()->getCurrentLanguage()->getId();		
		$node = \Drupal::request()->attributes->get('node');
		$field = $node->field_hero;

		$items = [];

		if ( $field ) {
			$hero_paragraphs = $field->getValue();
			foreach($hero_paragraphs as $hero_paragraph) {
				$hero_target = $hero_paragraph['target_id'];
				$hero = Paragraph::load($hero_target);
				$bundle = $hero->getType();
				if ($hero->isTranslatable()) { $hero = $hero->getTranslation($language); }

				
				if ($bundle == "carousel") {
				
					$carousel_paragraphs = $hero->field_item->getValue();
	
					if ($carousel_paragraphs) {
						$carousel_first_item = Paragraph::load($carousel_paragraphs[0]['target_id']);
	
						//if ($bundle == 'carousel_item') {
							
						$disable_animation = FALSE;
						$animation_values = $hero->field_animation->getValue();
						if (!count($animation_values)) {
							$animation = NULL;
							$disable_animation = TRUE;
						} else {
							$animation_value = isset($animation_values[0]) ? $animation_values[0] : NULL;
							$animation = array_combine(
								array_map(function($key){ return '#'.$key; }, array_keys($animation_value)),
								$animation_value
							);
						}
						
						$autoplay = $hero->field_autoplay->value;
						$arrows = $hero->field_arrows->value;
						$dots = $hero->field_dots->value;
						$infinte = $hero->field_infinite->value;
						$pause_on_hover = $hero->field_pause_on_hover->value;
						$timer = $hero->field_timer->value;
						$scroll_down = $hero->field_scroll_down->value;
						
	
						$carousel_meta = [
							'autoplay' => $autoplay ? "true" : "false",
							'arrows' => $arrows,
							'disable_animation' => !$disable_animation ? "true" : "false",
							'dots' => $dots,
							'infinte' => $infinte ? "true" : "false",
							'pause_on_hover' => $pause_on_hover ? "true" : "false",
							'scroll_down' => $scroll_down,
							'timer' => $timer,
							'animation' => $animation,
						];

						foreach($carousel_paragraphs as $carousel_paragraph) {
							$carousel_target = $carousel_paragraph['target_id'];
							$carousel = Paragraph::load($carousel_target);
							if ($carousel->isTranslatable()) {
								$carousel = $carousel->getTranslation($language);
							}
			
							$image = $this->getImageFromMedia( $carousel->get('field_image')->getValue() );
							if ($image) {
								$items[] = array(
									"body" => $carousel->get('field_body')->value,
									"image" => $image,
								);
							}
						}
					}
				} else if ($bundle == 'video_background') {
					//$carousel_target = $carousel_paragraph['target_id'];
					//$carousel = Paragraph::load($carousel_target);
					$video = $hero->field_video_url->uri;
					$image = $this->getImageFromMedia( $hero->get('field_image')->getValue() );
					$scroll_down = $hero->field_scroll_down->value;
					$items[] = ["image" => $image, "video" => $video,  ];
					$carousel_meta = [
						'scroll_down' => $scroll_down,
					];
				}
			}
		}

		if (count($items) >= 1) {
			$render = [
				'#theme' => $bundle, 
				'#items' => $items,
				'#cache' => ['max-age' => 0,]
			];
			
			if (isset($carousel_meta)) {
				$render['#meta'] = $carousel_meta;
			}
		} else {
			$render = [
				'#attributes' => ['class' => ['empty']],
				'#cache' => ['max-age' => 0,]
			];	
		}
	
		return $render;
	}
}
?>