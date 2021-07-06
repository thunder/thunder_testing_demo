<?php

namespace Drupal\thunder_testing_demo\PathProcessor;

use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a path processor to transpose entity UUID paths to serialize IDs.
 *
 * Incoming paths matching /edit/{entity_type_id}/{uuid} are routed to their correct
 * path.
 *
 * @see https://drupal.org/i/2353611
 */
class PathProcessorEntityUuid implements InboundPathProcessorInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new PathProcessorEntityUuid object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    $matches = [];
    if (preg_match('/^\/edit\/([a-z_]+)\/(' . Uuid::VALID_PATTERN . ')$/i', $path, $matches)) {
      $entity_type_id = $matches[1];
      $uuid = $matches[2];
      if ($this->entityTypeManager->hasDefinition($entity_type_id)) {
        $storage = $this->entityTypeManager->getStorage($entity_type_id);
        $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
        if ($entity_type->hasLinkTemplate('canonical') && $entities = $storage->loadByProperties(['uuid' => $uuid])) {
          /* @var \Drupal\Core\Entity\EntityInterface $entity */
          $entity = reset($entities);
          $path = '/' . ltrim($entity->toUrl('edit-form')->getInternalPath(), '/');
        }
      }
    }
    return $path;
  }

}
