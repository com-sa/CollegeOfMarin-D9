<?php

namespace Drupal\com_post_migration;

use Drupal\media\Entity\Media;
use Drupal\Core\File\FileSystemInterface;
use Drupal\node\Entity\Node;

class CreateNodes {

  public static function process($files, $terms, &$context){
    if (!isset($context['sandbox']['current'])) {
      $context['sandbox']['current'] = 0;
      $context['sandbox']['max'] = count($files);
      $context['sandbox']['limit'] = 10;
    }

    $rows = array_slice($files, $context['sandbox']['current'], $context['sandbox']['limit']);
    foreach ($rows as $row) {
      CreateNodes::makeNode($row, $terms);
      $context['results'][] = $row->uri;
      $context['sandbox']['current']++;
      $context['message'] = 'Processing Row: ' . $row->uri;
    }

    if ($context['sandbox']['current'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['current'] / $context['sandbox']['max'];
    }

  }

  public static function makeNode($item, $terms) {
    
    /*
    stdClass Object(
      [ID] => 221
      [title] => CC Meeting 9-15-2016
      [created] => 1480719289
      [changed] => 1519407612
      [uid] => 1
      [location] => Academic Center, Room 303
      [date] => 2016-09-15 00:00:00
      [organization] => CC
      [fiscal_year] => 2016-2017
      [agenda] => CC Agenda 09-15-2016
      [minutes] => CC Minutes 09-15-2016
      [additional_materials] => Agenda and Materials
    )*/
      

    $node = Node::load($item->ID);

    if ($node) {
      if ($item->organization) {
			  $node->set('field_organization', $terms[$item->organization]['id']);
		  }

		  if ($item->fiscal_year) {
			  $node->set('field_fiscal_year', $terms[$item->fiscal_year]['id']);
		  }

      if ($item->agenda && $node->get('field_agenda')->isEmpty()) {
			  $name_array = explode('.',$item->agenda);
			  array_pop($name_array);
			  $name = implode('.',$name_array);
        $name = str_replace('public://', '', $name);
			  $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $name]);
			  if (count($media)) {
				  $node->set('field_agenda', reset($media)->id());
			  }
		  }

		  if ($item->minutes && $node->get('field_minutes')->isEmpty()) {
			  $name_array = explode('.',$item->minutes);
			  array_pop($name_array);
			  $name = implode('.',$name_array);
        $name = str_replace('public://', '', $name);
			  $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $name]);
			  if (count($media)) {
				  $node->set('field_minutes', reset($media)->id());
			  }
		  }

		  if ($item->additional_materials && $node->get('field_additional_materials')->isEmpty()) {
			  $additional_materials_array = explode(',',$item->additional_materials);
			  $additional_materials_items = [];
			  foreach($additional_materials_array as $additional_materials_item) {
				  $name_array = explode('.',$additional_materials_item);
				  array_pop($name_array);
				  $name = implode('.',$name_array);
          $name = str_replace('public://', '', $name);
				  $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $name]);
				  if (count($media)) {
					  $additional_materials_items[] = reset($media)->id();
				  }
			  }

			  if ($additional_materials_items) {
				  $node->set('field_additional_materials', $additional_materials_items);
			  }
		  }
    } else {
      $node = Node::create([
			  'type' => 'agenda_item',
			  'created' => $item->created,
			  'changed' => $item->changed,
			  'uid' => $item->uid,
			  'title' => $item->title,
			  'field_location' => $item->location,
		  ]);
		
		  if ($item->date) {
			  $meeting_date_full = explode(" ", $item->date);
			  $node->set('field_meeting_date', array_shift($meeting_date_full));
		  }

		  if ($item->organization) {
			  $node->set('field_organization', $terms[$item->organization]['id']);
		  }

		  if ($item->fiscal_year) {
			  $node->set('field_fiscal_year', $terms[$item->fiscal_year]['id']);
		  }

		  if ($item->agenda) {
			  $name_array = explode('.',$item->agenda);
			  array_pop($name_array);
			  $name = implode('.',$name_array);
        $name = str_replace('public://', '', $name);
			  $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $name]);
			  if (count($media)) {
				  $node->set('field_agenda', reset($media)->id());
			  }
		  }

		  if ($item->minutes) {
			  $name_array = explode('.',$item->minutes);
			  array_pop($name_array);
			  $name = implode('.',$name_array);
        $name = str_replace('public://', '', $name);
			  $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $name]);
			  if (count($media)) {
				  $node->set('field_minutes', reset($media)->id());
			  }
		  }

		  if ($item->additional_materials) {
			  $additional_materials_array = explode(',',$item->additional_materials);
			  $additional_materials_items = [];
			  foreach($additional_materials_array as $additional_materials_item) {
				  $name_array = explode('.',$additional_materials_item);
				  array_pop($name_array);
				  $name = implode('.',$name_array);
          $name = str_replace('public://', '', $name);
				  $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $name]);
				  if (count($media)) {
					  $additional_materials_items[] = reset($media)->id();
				  }
			  }

			  if ($additional_materials_items) {
				  $node->set('field_additional_materials', $additional_materials_items);
			  }
		  }
		
      $node->enforceIsNew();
    }

    $node->save();

  }

  public function finishedCallback($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(count($results), 'One post processed.', '@count posts processed.' );
    } else {
      $message = t('Finished with an error.');
    }

    drupal_set_message($message);
  }
}
