<?php

namespace Drupal\alshaya_stores_finder\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\StorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlshayaStoresFinderConfigSubscriber.php.
 */
class AlshayaStoresFinderConfigSubscriber implements EventSubscriberInterface {

  /**
   * Config Storage service.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * Constructs a new AlshayaStoresFinderConfigSubscriber object.
   *
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   *   Config storage object.
   */
  public function __construct(StorageInterface $configStorage) {
    $this->configStorage = $configStorage;
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

    if ($config->getName() == 'views.view.stores_finder') {
      $country_code = strtolower(_alshaya_custom_get_site_level_country_code());

      $data = $config->getRawData();

      $data['display']['attachment_1']['display_options']['filters']['field_latitude_longitude_proximity']['expose']['geocoder_plugin_settings']['settings']['component_restrictions']['country'] = $country_code;
      $data['display']['page_1']['display_options']['filters']['field_latitude_longitude_proximity']['expose']['geocoder_plugin_settings']['settings']['component_restrictions']['country'] = $country_code;
      $data['display']['page_3']['display_options']['filters']['field_latitude_longitude_proximity']['expose']['geocoder_plugin_settings']['settings']['component_restrictions']['country'] = $country_code;

      // Re-write the config to make sure the overrides are not lost.
      $this->configStorage->write($config->getName(), $data);
    }
  }

}
