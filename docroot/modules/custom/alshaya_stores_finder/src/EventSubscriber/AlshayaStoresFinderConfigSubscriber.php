<?php

namespace Drupal\alshaya_stores_finder\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\StorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

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
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new AlshayaStoresFinderConfigSubscriber object.
   *
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   *   Config storage object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
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
   * The stores_finder views are generic so we must apply the brand overrides
   * when config is changed.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   Response event Object.
   */
  public function onConfigSave(ConfigCrudEvent $event) {
    /** @var \Drupal\Core\Config\ImmutableConfig $config */
    $config = $event->getConfig();

    if ($config->getName() == 'views.view.stores_finder') {
      $country_code = strtolower(_alshaya_custom_get_site_level_country_code(TRUE));
      $marker_path = $this->configFactory->get('alshaya_stores_finder.settings')->get('marker.url');

      $data = $config->getRawData();
      $view_displays = ['attachment_1', 'page_3', 'page_2'];
      foreach ($view_displays as $view_display) {
        $data['display'][$view_display]['display_options']['filters']['field_latitude_longitude_proximity']['expose']['geocoder_plugin_settings']['settings']['component_restrictions']['country'] = $country_code;
        $data['display'][$view_display]['display_options']['style']['options']['google_map_settings']['marker_icon_path'] = $marker_path;
      }

      // Re-write the config to make sure the overrides are not lost.
      $this->configStorage->write($config->getName(), $data);
    }
  }

}
