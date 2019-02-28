<?php

namespace Drupal\alshaya_addressbook\EventSubscriber;

use CommerceGuys\Addressing\AddressFormat\AdministrativeAreaType;
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

    $definition['administrative_area_type'] = AdministrativeAreaType::AREA;
    $definition['subdivision_depth'] = 0;

    $definition['format'] = "%givenName %familyName\n%organization\n%administrativeArea\n%locality\n%addressLine1\n%dependentLocality\n%addressLine2\n%sortingCode\n%additionalName\n%postalCode";

    $definition['required_fields'][] = 'dependentLocality';
    $definition['required_fields'][] = 'administrativeArea';
    $definition['uppercase_fields'][] = 'addressLine2';

    $event->setDefinition($definition);
  }

}
