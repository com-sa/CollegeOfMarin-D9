<?php

namespace Drupal\com\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class WebformAutocompleteFix implements ContainerInjectionInterface {

  protected ClassResolverInterface $resolver;

  public function __construct(ClassResolverInterface $resolver) {
    $this->resolver = $resolver;
  }

  public static function create(ContainerInterface $container): self {
    return new self($container->get('class_resolver'));
  }

  public function handle(Request $request): JsonResponse {
    // 1) Call the original Webform controller to get the standard results.
    /** @var \Drupal\webform\Controller\WebformEntityController $orig */
    $orig = $this->resolver->getInstanceFromDefinition('\Drupal\webform\Controller\WebformEntityController');
    $origResponse = $orig->autocomplete($request);

    // 2) Decode, adjust only the "value" (keep label as-is for display).
    $data = json_decode($origResponse->getContent(), true) ?: [];

    foreach ($data as &$row) {
      if (!isset($row['label'], $row['value'])) {
        continue;
      }
      // Strip trailing " (webform_123)" from the value only.
      $row['value'] = preg_replace('/\s*\(webform_\d+\)\s*$/', '', $row['label']);
    }
    unset($row);

    // 3) Return adjusted payload; preserve cache headers if you like.
    $resp = new JsonResponse($data, $origResponse->getStatusCode(), $origResponse->headers->all());
    return $resp;
  }
}