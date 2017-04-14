<?php
/**
 * @file
 * Contains \Drupal\acq_sku\Entity\Product.
 */

namespace Drupal\acq_sku\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\user\UserInterface;

/**
 * Defines the SKU entity.
 *
 * @ContentEntityType(
 *   id = "acq_sku",
 *   label = @Translation("SKU entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\acq_sku\Entity\Controller\SKUViewBuilder",
 *     "list_builder" = "Drupal\acq_sku\Entity\Controller\SKUListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\acq_sku\Form\SKUForm",
 *       "add" = "Drupal\acq_sku\Form\SKUForm",
 *       "edit" = "Drupal\acq_sku\Form\SKUForm",
 *       "delete" = "Drupal\acq_sku\Form\SKUDeleteForm",
 *     },
 *     "access" = "Drupal\acq_sku\SKUAccessControlHandler",
 *   },
 *   base_table = "acq_sku",
 *   admin_permission = "administer commerce sku entity",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "bundle" = "type"
 *   },
 *   bundle_entity_type = "acq_sku_type",
 *   bundle_label = @Translation("SKU type"),
 *   links = {
 *     "canonical" = "/admin/commerce/sku/{acq_sku}",
 *     "edit-form" = "/admin/commerce/sku/{acq_sku}/edit",
 *     "delete-form" = "/admin/commerce/sku/{acq_sku}/delete",
 *     "collection" = "/admin/commerce/sku/list"
 *   },
 *   field_ui_base_route = "acq_sku.configuration",
 * )
 */
class SKU extends ContentEntityBase implements SKUInterface {

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getSKU() {
    return $this->get('sku')->value;
  }

  /**
   * @return null|object - Returns a plugin instance if one exists
   */
  public function getPluginInstance() {
    $plugin_manager = \Drupal::service('plugin.manager.sku');
    $plugin_definition = $plugin_manager->pluginFromSKU($this);

    if (empty($plugin_definition)) {
      return NULL;
    }

    $class = $plugin_definition['class'];
    return new $class();
  }

  /**
   * Query to get ids of given skus.
   *
   * @param array $sku
   *   an Array of sku.
   *
   * @return array|int
   */
  public static function getSKUids(array $sku) {
    $query = \Drupal::entityQuery('acq_sku')
                    ->condition('sku', $sku, 'IN');
    $ids = $query->execute();

    return $ids;
  }

  /**
   * Loads a SKU Entity from SKU.
   *
   * @param $sku string - SKU to load.
   * @return SKU - Found SKU
   */
  public static function loadFromSKU($sku) {
    $ids = SKU::getSKUids(array($sku));

    if (count($ids) != 1) {
      \Drupal::logger('acq_sku')->error(
        'Duplicate product or non-existent SKU @sku found while loading.',
        array('@sku' => $sku)
      );
      return;
    }

    $id = array_shift($ids);
    $sku = SKU::load($id);

    return $sku;
  }

  /**
   * Get all the cross sell sku of given skus.
   *
   * @param array $sku
   *   An array of sku.
   *
   * @return array
   *   Return array of cross sell skus.
   */
  public static function getCrossSellSKUs(array $sku) {
    $ids = SKU::getSKUids($sku);
    $skus = SKU::loadMultiple($ids);

    $crossskus = array();
    foreach ($skus as $sku) {
      $crosssell = $sku->getCrossSell();
      foreach ($crosssell as $item) {
        $crossskus[] = $item['value'];
      }
    }

    return $crossskus;
  }

  /**
   * Get all the up sell sku of given skus.
   *
   * @param array $sku
   *   An array of sku.
   *
   * @return array
   *   Return array of up sell skus.
   */
  public static function getUpSellSKUs(array $sku) {
    $ids = SKU::getSKUids($sku);
    $skus = SKU::loadMultiple($ids);

    $upsellskus = array();
    foreach ($skus as $sku) {
      $upSell = $sku->getUpSell();
      foreach ($upSell as $item) {
        $upsellskus[] = $item['value'];
      }
    }

    return $upsellskus;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setChangedTime($timestamp) {
    $this->set('changed', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTimeAcrossTranslations() {
    $changed = $this->getUntranslated()->getChangedTime();
    foreach ($this->getTranslationLanguages(FALSE) as $language) {
      $translation_changed = $this->getTranslation($language->getId())
        ->getChangedTime();
      $changed = max($translation_changed, $changed);
    }
    return $changed;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * Get all the cross sell sku values of current entity.
   */
  public function getCrossSell() {
    return $this->get('crosssell')->getValue();
  }

  /**
   * Get all the upsell sku values of current entity.
   */
  public function getUpSell() {
    return $this->get('upsell')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * Define the field properties here.
   *
   * Field name, type and size determine the table structure.
   *
   * In addition, we can define how the field and its content can be manipulated
   * in the GUI. The behaviour of the widgets used can be determined here.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('SKU Type'))
      ->setDescription(t('The SKU type.'))
      ->setSetting('target_type', 'acq_sku_type')
      ->setReadOnly(TRUE);

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the SKU entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the SKU entity.'))
      ->setReadOnly(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The SKU\'s human-friendly name.'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -10,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -10,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['sku'] = BaseFieldDefinition::create('string')
      ->setLabel(t('SKU'))
      ->setDescription(t('The SKU.'))
      ->setRequired(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -11,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -11,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['price'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Display Price'))
      ->setDescription(t('Display Price of this SKU.'))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -6,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['crosssell'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Cross sell SKU'))
      ->setDescription(t('Reference to all Cross sell SKUs.'))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 5,
      ))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', TRUE);

    $fields['upsell'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Up sell SKU'))
      ->setDescription(t('Reference to all up sell SKUs.'))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 6,
      ))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', TRUE);

    $fields['related'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Related SKU'))
      ->setDescription(t('Reference to all related SKUs.'))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 7,
      ))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', TRUE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setDescription(t('Product image'))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'image',
        'weight' => -11,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'image_image',
        'weight' => -9,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['attributes'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Attributes'))
      ->setDescription(t('Non-Drupal native product data.'))
      ->setCardinality(-1)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of Product entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    // Get all the fields added by other modules and add them as base fields.
    $additionalFields = \Drupal::config('acq_sku.base_field_additions')->getRawData();

    // Get the default weight increment value from variables.
    $defaultWeightIncrement = \Drupal::state()->get('acq_sku.base_field_weight_increment', 20);

    // Check if we have additional fields to be added as base fields.
    if (!empty($additionalFields) && is_array($additionalFields)) {
      foreach ($additionalFields as $machine_name => $field_info) {
        // Initialise the field variable.
        $field = NULL;

        // Showing the fields at the bottom.
        $weight = $defaultWeightIncrement + count($fields);

        switch ($field_info['type']) {
          case 'string':
            $field = BaseFieldDefinition::create('string');

            if ($field_info['visible_view']) {
              $field->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'string',
                'weight' => $weight,
              ]);
            }

            if ($field_info['visible_form']) {
              $field->setDisplayOptions('form', [
                'type' => 'string_textfield',
                'weight' => $weight,
              ]);
            }
            break;

          case 'image':
            $field = BaseFieldDefinition::create('image');
            if ($field_info['visible_view']) {
              $field->setDisplayOptions('view', array(
                'label' => 'hidden',
                'type' => 'image',
                'weight' => $weight,
              ));
            }

            if ($field_info['visible_form']) {
              $field->setDisplayOptions('form', array(
                'type' => 'image_image',
                'weight' => $weight,
              ));
            }
            break;
        }

        // Check if we don't have the field type defined yet.
        if (empty($field)) {
          throw new \RuntimeException('Field type not defined yet, please contact TA.');
        }

        $field->setLabel(t($field_info['label']));

        // Update cardinality with default value if empty.
        $field_info['description'] = empty($field_info['description']) ? 1 : $field_info['description'];
        $field->setDescription(t($field_info['description']));

        // Update cardinality with default value if empty.
        $field_info['cardinality'] = empty($field_info['cardinality']) ? 1 : $field_info['cardinality'];
        $field->setCardinality($field_info['cardinality']);


        $field->setDisplayConfigurable('form', (bool) $field_info['visible_form']);
        $field->setDisplayConfigurable('view', (bool) $field_info['visible_view']);

        // We will use attr prefix to avoid conflicts with default base fields.
        $fields['attr_' . $machine_name] = $field;
      }
    }

    return $fields;
  }
}
