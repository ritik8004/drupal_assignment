<?php

namespace Drupal\alshaya_kz_transac_lite\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Ticket booking details entity.
 *
 * The Ticket entity is used to maintain the ticket details, the ticket details
 * may persist in the database as an archive record.
 *
 * @ContentEntityType(
 *   id = "ticket",
 *   label = @Translation("Ticket"),
 *   base_table = "ticket",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "sales_number" = "sales_number",
 *   },
 *    handlers = {
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 * )
 */
class Ticket extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of ticket entity.'))
      ->setReadOnly(TRUE);

    // Standard field, used as unique.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of ticket entity.'))
      ->setReadOnly(TRUE);

    $fields['sales_number'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Sales number'))
      ->setDescription(t('The sales number of ticket entity.'))
      ->setRequired(TRUE);

    $fields['email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email'))
      ->setDescription(t('The email of ticket entity.'))
      ->setRequired(TRUE);

    $fields['telephone'] = BaseFieldDefinition::create('telephone')
      ->setLabel(t('Phone number'))
      ->setDescription(t('The phone number of ticket entity.'))
      ->setDefaultValue('')
      ->setRequired(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of ticket entity.'))
      ->setRequired(TRUE);

    $fields['visitor_types'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Visitor types'))
      ->setDescription(t('The visitor types of ticket entity.'))
      ->setRequired(TRUE);

    $fields['visit_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Visit date'))
      ->setDescription(t('The visit date of ticket entity.'))
      ->setSetting('datetime_type', 'date')
      ->setRequired(TRUE);

    $fields['booking_status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Booking status'))
      ->setDescription(t('The Booking status of the ticket entity.'))
      ->setDefaultValue('inactive')
      ->setSettings([
        'allowed_values' => [
          'active' => 'Active',
          'inactive' => 'Inactive',
        ],
      ]);

    $fields['payment_status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Payment status'))
      ->setDescription(t('The Payment status of the ticket entity.'))
      ->setDefaultValue('pending')
      ->setSettings([
        'allowed_values' => [
          'pending' => 'Pending',
          'complete' => 'Complete',
          'cancelled' => 'Cancelled',
        ],
      ]);

    $fields['payment_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Payment type'))
      ->setDescription(t('The payment type of the ticket entity.'))
      ->setSettings([
        'allowed_values' => [
          'knet' => 'K-Net',
        ],
      ]);

    $fields['payment_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Payment id'))
      ->setDescription(t('The payment id of the ticket entity.'))
      ->setRequired(FALSE);

    $fields['transaction_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Transaction id'))
      ->setDescription(t('The transaction id of the ticket entity.'))
      ->setRequired(FALSE);

    $fields['ticket_info'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Ticket info'))
      ->setDescription(t('The ticket info of the ticket entity.'))
      ->setRequired(FALSE);

    $fields['order_total'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Order Total'))
      ->setDescription(t('The order total of the ticket entity.'))
      ->setRequired(FALSE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of ticket entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    return $fields;
  }

}
