<?php

namespace Drupal\com_post_migration;

use Drupal\media\Entity\Media;
use Drupal\Core\File\FileSystemInterface;

class ImagesToMedia {

  public static function process($files, &$context){
    if (!isset($context['sandbox']['current'])) {
      $context['sandbox']['current'] = 0;
      $context['sandbox']['max'] = count($files);
      $context['sandbox']['limit'] = 10;
    }

    $rows = array_slice($files, $context['sandbox']['current'], $context['sandbox']['limit']);
    foreach ($rows as $row) {
      ImagesToMedia::migrateImageToMedia($row);
      $context['results'][] = $row->uri;
      $context['sandbox']['current']++;
      $context['message'] = 'Processing Row: ' . $row->uri;
    }

    if ($context['sandbox']['current'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['current'] / $context['sandbox']['max'];
    }

  }

  public static function migrateImageToMedia($item) {
    /*(
      [uri] => /Users/ronmarcelle/Sites/srjc.local/docroot/sites/default/files/blank.pdf
      [filename] => blank.pdf
      [name] => blank
    )*/
    $file_repository = \Drupal::service('file.repository');
		$file = $file_repository->loadByUri("public://".str_replace(DRUPAL_ROOT . '/sites/default/files/', '', $item->uri));
    $is_image = preg_match('/^.*\.(jpg|jpeg|png|gif|webp)$/i', $item->filename);

    if (!$file || get_class($file) !== 'Drupal\file\Entity\File') {
      $file_data = file_get_contents($item->uri);
      $file = $file_repository->writeData($file_data, "public://".$item->filename, FileSystemInterface::EXISTS_REPLACE);
    }

    $opts = [
      'name' => $item->name,
      'uid' => 1,
      'langcode' => 'en',
      'status' => 1,
    ];

    if ($is_image) {
      $opts['bundle'] = 'image';
      $opts['field_media_image'] = [ 'target_id' => $file->id(), 'alt' => $item->name, ];
     } else {
      $opts['bundle'] = 'document';
      $opts['field_media_file'] = [ 'target_id' => $file->id() ];
     }

    $media = Media::create($opts);
    $media->save();

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
