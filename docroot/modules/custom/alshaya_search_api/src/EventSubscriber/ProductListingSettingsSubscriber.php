<?php

namespace Drupal\alshaya_search_api\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Cache\Cache;

/**
 * A subscriber to clear cache for product listing pages when config save.
 *
 * On saving alshaya_search_api.listing_settings settings.
 */
class ProductListingSettingsSubscriber implements EventSubscriberInterface {

  /**
   * Invalidate the cache tags whenever the settings are modified.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The Event to process.
   */
  public function onSave(ConfigCrudEvent $event) {
    if ($event->getConfig()->getName() === 'alshaya_search_api.listing_settings') {
      // Invalidate cache tags for plp/srp/promotion list pages.
      Cache::invalidateTags([
        'search_api_list:product',
        'search_api_list:acquia_search_index',
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onSave'];
    return $events;
  }

}
