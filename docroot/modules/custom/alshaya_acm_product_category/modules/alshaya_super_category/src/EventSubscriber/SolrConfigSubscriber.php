<?php

namespace Drupal\alshaya_super_category\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SolrConfigSubscriber.
 */
class SolrConfigSubscriber implements EventSubscriberInterface {

  /**
   * Config Storage service.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new SolrConfigSubscriber object.
   *
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   *   Config storage object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory object.
   */
  public function __construct(StorageInterface $configStorage, ConfigFactoryInterface $config_factory) {
    $this->configStorage = $configStorage;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE] = ['onConfigSave', -100];
    return $events;
  }

  /**
   * This method is called whenever the config.save event is fired.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   Response event Object.
   */
  public function onConfigSave(ConfigCrudEvent $event) {
    /** @var \Drupal\Core\Config\ImmutableConfig $config */
    $config = $event->getConfig();

    if ($config->getName() == 'search_api.index.acquia_search_index') {

      if (!$this->configFactory->get('alshaya_super_category.settings')->get('status')) {
        return;
      }

      if ($config->get('field_settings.field_category_parent')) {
        return;
      }

      $data = $config->getRawData();
      // Add super category pare term id to solr index.
      $data['field_settings']['field_category_parent'] = [
        'label' => 'Category Parent',
        'datasource_id' => 'entity:node',
        'property_path' => 'field_category',
        'type' => 'integer',
        'dependencies' => [
          'config' => [
            'field.storage.node.field_category',
          ],
        ],
      ];
      $this->configStorage->write($config->getName(), $data);
    }
  }

}
