<?php

namespace Drupal\alshaya_addressbook\EventSubscriber;

use CommerceGuys\Addressing\AddressFormat\AdministrativeAreaType;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\address\Event\AddressFormatEvent;
use Drupal\address\Event\AddressEvents;

/**
 * Class AddressFormatSubscriber.
 */
class AddressFormatSubscriber implements EventSubscriberInterface {

  /**
   * Address Book Manager.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  private $addressBookManager;

  /**
   * AddressFormatSubscriber constructor.
   *
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   Address Book Manager.
   */
  public function __construct(AlshayaAddressBookManager $address_book_manager) {
    $this->addressBookManager = $address_book_manager;
  }

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
    $definition['required_fields'][] = 'dependentLocality';
    $definition['required_fields'][] = 'administrativeArea';
    $definition['uppercase_fields'][] = 'addressLine2';

    $definition['format'] = "%givenName %familyName\n%organization\n%administrativeArea\n%locality\n%addressLine1\n%dependentLocality\n%addressLine2\n%sortingCode\n%additionalName\n%postalCode";

    // Hide all the fields which we couldn't map.
    foreach ($this->addressBookManager->getMagentoUnmappedFields() as $field_code) {
      $this->removeFieldFromDefinition($definition, $field_code);
    }

    // Hide all disabled fields.
    foreach ($this->addressBookManager->getMagentoDisabledFields() as $field_code => $magento_field_code) {
      $this->removeFieldFromDefinition($definition, $field_code);
    }

    $event->setDefinition($definition);
  }

  /**
   * Wrapper function to remove a field from definition if available.
   *
   * @param array $definition
   *   Definition to update.
   * @param string $field_code
   *   Field code to remove if available in definition.
   */
  private function removeFieldFromDefinition(array &$definition, string $field_code) {
    $field_code = lcfirst(str_replace('_', '', ucwords($field_code, '_')));

    $key = array_search($field_code, $definition['required_fields']);
    if ($key !== FALSE) {
      unset($definition['required_fields'][$key]);
    }

    $definition['hidden_fields'][] = $field_code;
    $field_code = '%' . $field_code;

    // Try to remove from format with PHP_EOL if it is the only field in line.
    $definition['format'] = str_replace(PHP_EOL . $field_code . PHP_EOL, PHP_EOL, $definition['format']);

    // Try to remove from format if it failed above.
    $definition['format'] = str_replace($field_code, '', $definition['format']);
  }

}
