<?php

namespace Drupal\alshaya_addressbook\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\address\Event\AddressFormatEvent;
use Drupal\address\Event\AddressEvents;

/**
 * Class AddressFormatSubscriber.
 */
class AddressFormatSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AddressEvents::ADDRESS_FORMAT][] = ['onGetDefinition', 0];
    return $events;
  }

  /**
   * Event handler callback.
   *
   * @param \Drupal\address\Event\AddressFormatEvent $event
   *   Event object.
   */
  public function onGetDefinition(AddressFormatEvent $event) {
    $definition = $event->getDefinition();
    // Only for 'Kuwait'.
    if ($definition['country_code'] == 'KW') {
      $definition['format'] = "%givenName %familyName\n%locality\n%administrativeArea\n%organization\n%addressLine1\n%dependentLocality\n%addressLine2";
      $definition['required_fields'][] = 'dependentLocality';
      $definition['required_fields'][] = 'administrativeArea';
      $definition['required_fields'][] = 'organization';
      $definition['uppercase_fields'][] = 'addressLine2';
      $event->setDefinition($definition);
    }
  }

}
