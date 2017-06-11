<?php

namespace Drupal\acq_sku\Entity;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\file\Entity\File;
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
   * Processed media array.
   *
   * @var array
   */
  protected $mediaData = [];

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
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
  public function getSku() {
    return $this->get('sku')->value;
  }

  /**
   * Function to return media files for a SKU.
   *
   * @param bool $reset
   *   Flag to reset cache and generate array again from serialized string.
   *
   * @return array
   *   Array of media files.
   */
  public function getMedia($reset = FALSE) {
    if (!$reset && !empty($this->mediaData)) {
      return $this->mediaData;
    }

    if ($media_data = $this->get('media')->getString()) {
      $update_sku = FALSE;

      $media_data_full = unserialize($media_data);

      if (empty($media_data_full)) {
        return [];
      }

      // @TODO: Remove this hard coded fix after getting answer why we have empty
      // second array index and why all media come in first array index.
      $media_data = reset($media_data_full);

      foreach ($media_data as &$data) {
        if ($data['media_type'] == 'image') {
          if (empty($data['fid'])) {
            try {
              // Prepare the File object when we access it the first time.
              $data['fid'] = $this->downloadMediaImage($data);
              $update_sku = TRUE;
            }
            catch (\Exception $e) {
              \Drupal::logger('acq_sku')->error($e->getMessage());
              continue;
            }
          }

          $data['file'] = File::load($data['fid']);

          if (empty($data['label'])) {
            $data['label'] = $this->label();
          }
        }

        $this->mediaData[$data['position']] = $data;
      }

      if ($update_sku) {
        // @TODO: Remove this hard coded fix after getting answer why we have
        // empty second array index and why all media come in first array index.
        $media_data_full[0] = $media_data;
        $this->get('media')->setValue(serialize($media_data_full));
        $this->save();
      }
    }

    // Sort them by position again to ensure it works everytime.
    ksort($this->mediaData);

    return $this->mediaData;
  }

  /**
   * Function to save image file into public dir.
   *
   * @param array $data
   *   File data.
   *
   * @return int
   *   File id.
   */
  protected function downloadMediaImage(array $data) {
    // Preparing args for all info/error messages.
    $args = ['@file' => $data['file'], '@sku_id' => $this->id()];

    // Download the file contents.
    $file_data = file_get_contents($data['file']);

    // Check to ensure errors like 404, 403, etc. are catched and empty file
    // not saved in SKU.
    if (empty($file_data)) {
      throw new \Exception(new FormattableMarkup('Failed to download file "@file" for SKU id @sku_id.', $args));
    }

    // Get the path part in the url, remove hostname.
    $path = parse_url($data['file'], PHP_URL_PATH);

    // Remove slashes from start and end.
    $path = trim($path, '/');

    // Get the file name.
    $file_name = basename($path);

    // Prepare the directory path.
    $directory = 'public://media/' . str_replace('/' . $file_name, '', $path);

    // Prepare the directory.
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

    // Save the file as file entity.
    /** @var \Drupal\file\Entity\File $file */
    if ($file = file_save_data($file_data, $directory . '/' . $file_name, FILE_EXISTS_REPLACE)) {
      return $file->id();
    }
    else {
      throw new \Exception(new FormattableMarkup('Failed to save file "@file" for SKU id @sku_id.', $args));
    }
  }

  /**
   * Function to return first image from media files for a SKU.
   *
   * @return array
   *   Array of media files.
   */
  public function getThumbnail() {
    $media = $this->getMedia();

    // We loop through all the media items and return the first image.
    foreach ($media as $media_item) {
      if ($media_item['media_type'] == 'image') {
        return $media_item;
      }
    }

    return [];
  }

  /**
   * Get plugin instance for current object.
   *
   * @return null|object
   *   Returns a plugin instance if one exists.
   */
  public function getPluginInstance() {
    $plugin_manager = \Drupal::service('plugin.manager.sku');
    $plugin_definition = $plugin_manager->pluginFromSku($this);

    if (empty($plugin_definition)) {
      return NULL;
    }

    $class = $plugin_definition['class'];
    return new $class();
  }

  /**
   * Loads a SKU Entity from SKU.
   *
   * @param string $sku
   *   SKU to load.
   * @param string $langcode
   *   Language code.
   * @param bool $log_not_found
   *   Log errors when store not found. Can be false during sync.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   Found SKU
   */
  public static function loadFromSku($sku, $langcode = '', $log_not_found = TRUE) {
    $query = \Drupal::entityQuery('acq_sku');
    $query->condition('sku', $sku, 'IN');

    if (\Drupal::languageManager()->isMultilingual()) {
      if (empty($langcode)) {
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
      }

      $query->condition('langcode', $langcode);
    }

    $ids = $query->execute();

    if (count($ids) == 0) {
      // We don't log the error while doing sync.
      if ($log_not_found) {
        \Drupal::logger('acq_sku')->error('No SKU found for @sku.', ['@sku' => $sku]);
      }

      return NULL;
    }
    // For multiple entries, we just log the error and continue with first one.
    elseif (count($ids) > 1) {
      \Drupal::logger('acq_sku')->error('Duplicate SKUs found while loading for @sku.', ['@sku' => $sku]);
    }

    $id = array_shift($ids);
    $sku = SKU::load($id);

    return $sku;
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
      $translation_changed = $this->getTranslation($language->getId())->getChangedTime();
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
      ->setDescription(t("The SKU's human-friendly name."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -10,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['sku'] = BaseFieldDefinition::create('string')
      ->setLabel(t('SKU'))
      ->setDescription(t('The SKU.'))
      ->setRequired(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -11,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -11,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['price'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Display Price'))
      ->setDescription(t('Display Price of this SKU.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['special_price'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Special Price'))
      ->setDescription(t('Special Price of this SKU.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['final_price'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Final Price'))
      ->setDescription(t('Final Price of this SKU.'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['crosssell'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Cross sell SKU'))
      ->setDescription(t('Reference to all Cross sell SKUs.'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 5,
      ])
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', TRUE);

    $fields['upsell'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Up sell SKU'))
      ->setDescription(t('Reference to all up sell SKUs.'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 6,
      ])
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', TRUE);

    $fields['related'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Related SKU'))
      ->setDescription(t('Reference to all related SKUs.'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 7,
      ])
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', TRUE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setDescription(t('Product image'))
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'image',
        'weight' => -11,
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => -9,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['media'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Media'))
      ->setDescription(t('Store all the media files info.'))
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
          case 'attribute':
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

          case 'text_long':
            $field = BaseFieldDefinition::create('text_long');

            if ($field_info['visible_view']) {
              $field->setDisplayOptions('view', [
                'label' => 'hidden',
                'type' => 'text_default',
                'weight' => $weight,
              ]);
            }

            if ($field_info['visible_form']) {
              $field->setDisplayOptions('form', [
                'type' => 'text_textfield',
                'weight' => $weight,
              ]);
            }
            break;
        }

        // Check if we don't have the field type defined yet.
        if (empty($field)) {
          throw new \RuntimeException('Field type not defined yet, please contact TA.');
        }

        $field->setLabel($field_info['label']);

        // Update cardinality with default value if empty.
        $field_info['description'] = empty($field_info['description']) ? 1 : $field_info['description'];
        $field->setDescription($field_info['description']);

        // Update cardinality with default value if empty.
        $field_info['cardinality'] = empty($field_info['cardinality']) ? 1 : $field_info['cardinality'];
        $field->setCardinality($field_info['cardinality']);

        $field->setDisplayConfigurable('form', 1);
        $field->setDisplayConfigurable('view', 1);

        // We will use attr prefix to avoid conflicts with default base fields.
        $fields['attr_' . $machine_name] = $field;
      }
    }

    return $fields;
  }

}
