<?php

namespace Drupal\thunder_testing_demo\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\default_content\Event\DefaultContentEvents;
use Drupal\default_content\Event\ImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber to make config changes after the content import.
 */
class DefaultContentImportSubscriber implements EventSubscriberInterface {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Subscriber to make config changes after the content import.
   *
   * @param \Drupal\default_content\Event\ImportEvent $event
   *   Import event.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function onContentImport(ImportEvent $event) {
    $config = $this->configFactory->getEditable('system.site');
    foreach ($event->getImportedEntities() as $entity) {
      if ($entity->uuid() === 'f3f1e924-d404-425e-8130-eeb554e36f7a') {
        $config->set('page.403', $entity->toUrl()->toString());
      }
      if ($entity->uuid() === 'a77baf6a-78f9-41f4-90ae-224763e803b9') {
        $config->set('page.404', $entity->toUrl()->toString());
      }
    }
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [DefaultContentEvents::IMPORT => 'onContentImport'];
  }

}
