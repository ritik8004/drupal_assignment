<?php

namespace Drupal\acq_sku\Entity;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\Core\Site\Settings;
use Drupal\file\FileInterface;
use Drupal\user\UserInterface;
use GuzzleHttp\Exception\RequestException;

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
 *   data_table = "acq_sku_field_data",
 *   translatable = TRUE,
 *   common_reference_target = TRUE,
 *   admin_permission = "administer commerce sku entity",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "bundle" = "type",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid"
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
 *   not_update_base_table = TRUE,
 * )
 */
class SKU extends ContentEntityBase implements SKUInterface {

  /**
   * Flag to avoid downloading image.
   *
   * @var bool
   */
  public static $downloadImage = TRUE;

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
  public function getCacheTagsToInvalidate() {
    $tags = [];
    $tags[] = $this->entityTypeId . ':' . $this->id();
    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = $this->getCacheTagsToInvalidate();

    // Always add cache tag of parent in variants.
    if ($this->bundle() == 'simple') {
      $parents = array_keys($this->getPluginInstance()->getAllParentIds($this->getSku()));
      foreach ($parents as $id) {
        $tags[] = $this->entityTypeId . ':' . $id;
      }
    }

    if ($this->cacheTags) {
      $tags = Cache::mergeTags($tags, $this->cacheTags);
    }

    return $tags;
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
   * @param bool $download_media
   *   Whether to download media or not.
   * @param bool $reset
   *   Flag to reset cache and generate array again from serialized string.
   * @param string $default_label
   *   Default value for alt/title.
   *
   * @return array
   *   Array of media files.
   */
  public function getMedia($download_media = TRUE, $reset = FALSE, $default_label = '') {
    if (!$reset && !empty($this->mediaData)) {
      return $this->mediaData;
    }

    if ($media_data = $this->get('media')->getString()) {
      $update_sku = FALSE;

      $media_data = unserialize($media_data);

      if (empty($media_data)) {
        return [];
      }

      foreach ($media_data as &$data) {
        // We don't want to show disabled images.
        if (isset($data['disabled']) && $data['disabled']) {
          continue;
        }

        $this->mediaData[] = $this->processMediaItem($update_sku, $data, $download_media, $default_label);
      }

      if ($update_sku) {
        $save_sku = TRUE;
        // Allow disabling this through settings.
        if (Settings::get('sku_avoid_parallel_save', 1)) {
          /** @var \Drupal\Core\Lock\PersistentDatabaseLockBackend $lock */
          $lock = \Drupal::service('lock.persistent');
          // If lock is not available to acquire, means other process is
          // updating/deleting the sku in product sync. Skip the processing.
          if (!$lock->lockMayBeAvailable('synchronizeProduct' . $this->getSku())) {
            \Drupal::logger("acq_sku")->notice('Skipping saving of SKU @sku as seems its already updated/deleted in parallel by another process.', [
              '@sku' => $this->getSku(),
            ]);
            $save_sku = FALSE;
          }
        }

        if ($save_sku) {
          $this->get('media')->setValue(serialize($media_data));
          $this->save();
        }
      }
    }

    return array_filter($this->mediaData, function ($row) {
      return !empty($row['fid']);
    });
  }

  /**
   * Function to get processed media item with File entity in array.
   *
   * @param bool $update_sku
   *   Flag to specify if SKU should be updated or not.
   *   Update is done in parent function, here we only update the flag.
   * @param array $data
   *   Media item array.
   * @param bool $download
   *   Flag to specify if we should download missing images or not.
   * @param string $default_label
   *   Default value for alt/title.
   *
   * @return array|null
   *   Processed media item or null if some error occurred.
   */
  protected function processMediaItem(&$update_sku, array &$data, $download = FALSE, $default_label = '') {
    $media_item = $data;

    // Processing is required only for media type image as of now.
    if (isset($data['media_type']) && $data['media_type'] == 'image') {
      if (!empty($data['fid'])) {
        $file = $this->getFileStorage()->load($data['fid']);
        if (!($file instanceof FileInterface)) {
          // Leave a message for developers to find out why this happened.
          \Drupal::logger('acq_sku')->error('Empty file object for fid @fid on sku "@sku" having language @langcode. Trace: @trace', [
            '@fid' => $data['fid'],
            '@sku' => $this->getSku(),
            '@langcode' => $this->language()->getId(),
            '@trace' => json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)),
          ]);

          unset($data['fid']);

          // Try to download again if download flag is set to true.
          if ($download) {
            return $this->processMediaItem($update_sku, $data, TRUE, $default_label);
          }
        }
      }
      elseif ($download && self::$downloadImage) {
        try {
          // Prepare the File object when we access it the first time.
          $file = $this->downloadMediaImage($data);
          $update_sku = TRUE;
        }
        catch (\Exception $e) {
          \Drupal::logger('acq_sku')->error($e->getMessage());
          return NULL;
        }
      }

      if ($file instanceof FileInterface) {
        $data['fid'] = $file->id();
        $media_item['fid'] = $data['fid'];
        $media_item['file'] = $file;
      }

      if (empty($data['label'])) {
        $media_item['label'] = $default_label ?: $this->label();
      }

      return $media_item;
    }
    else {
      // Return whatever we have as is (videos).
      return $media_item;
    }
  }

  /**
   * Function to save image file into public dir.
   *
   * @param array $data
   *   File data.
   *
   * @return \Drupal\file\Entity\File
   *   File id or FALSE if file cant be downloaded.
   *
   * @throws \Exception
   */
  protected function downloadMediaImage(array &$data) {
    $lock_key = '';

    // If image is blacklisted, block download.
    if (isset($data['blacklist_expiry']) && time() < $data['blacklist_expiry']) {
      return FALSE;
    }

    // Allow disabling this through settings.
    if (Settings::get('media_avoid_parallel_downloads', 1)) {
      /** @var \Drupal\Core\Lock\PersistentDatabaseLockBackend $lock */
      $lock = \Drupal::service('lock.persistent');

      // Use remote id for lock key.
      $lock_key = 'download_image_' . $data['value_id'];

      // Acquire lock to ensure parallel processes are executed one by one.
      do {
        $lock_acquired = $lock->acquire($lock_key);

        // Sleep for half a second before trying again.
        if (!$lock_acquired) {
          usleep(500000);

          // Check once if downloaded by another process.
          $cache = \Drupal::cache('media_file_mapping')->get($lock_key);
          if ($cache && $cache->data) {
            $file = $this->getFileStorage()->load($cache->data);
            if ($file instanceof FileInterface) {
              return $file;
            }

            throw new \Exception(sprintf('File id %s mapped for %s in cache invalid, not retrying', $cache->data, $data['value_id']));
          }
        }
      } while (!$lock_acquired);
    }

    // Preparing args for all info/error messages.
    $args = ['@file' => $data['file'], '@sku_id' => $this->id()];

    // Download the file contents.
    try {
      $options = [
        'timeout' => Settings::get('media_download_timeout', 5),
      ];

      $file_stream = \Drupal::httpClient()->get($data['file'], $options);
      $file_data = $file_stream->getBody();
      $file_data_length = $file_stream->getHeader('Content-Length');
    }
    catch (RequestException $e) {
      watchdog_exception('acq_commerce', $e);
    }

    // Check to ensure empty file is not saved in SKU.
    // Using Content-Length Header to check for valid image data, sometimes we
    // also get a 0 byte image with response 200 instead of 404.
    // So only checking $file_data is not enough.
    if (!isset($file_data_length) || $file_data_length[0] === '0') {
      if ($lock_key) {
        $lock->release($lock_key);
      }
      // @TODO: SAVE blacklist info in a way so it does not have dependency on SKU.
      // Blacklist this image URL to prevent subsequent downloads for 1 day.
      $data['blacklist_expiry'] = strtotime('+1 day');
      // Empty file detected log.
      // Leave a message for developers to find out why this happened.
      \Drupal::logger('acq_sku')->error('Empty file detected during download, blacklisted for 1 day from now. File remote id: @remote_id, File URL: @url on SKU @sku. @trace', [
        '@url' => $data['file'],
        '@sku' => $this->getSku(),
        '@remote_id' => $data['value_id'],
        '@trace' => json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)),
      ]);
      return FALSE;
    }

    // Check if image was blacklisted, remove it from blacklist.
    if (isset($data['blacklist_expiry'])) {
      unset($data['blacklist_expiry']);
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

    // There are cases when upstream systems return different file id
    // but re-use the file name and this creates issue with CDNs.
    // So we add value_id as suffix to ensure each image url is unique after
    // every update.
    $file_name_array = explode('.', $file_name);
    $extension = array_pop($file_name_array);
    $file_name_array[] = $data['value_id'];
    $file_name_array[] = $extension;
    $file_name = implode('.', $file_name_array);

    // Save the file as file entity.
    /** @var \Drupal\file\Entity\File $file */
    if ($file = file_save_data($file_data, $directory . '/' . $file_name, FILE_EXISTS_REPLACE)) {
      if ($lock_key) {
        // Add file id in cache for other processes to be able to use.
        \Drupal::cache('media_file_mapping')->set($lock_key, $file->id(), \Drupal::time()->getRequestTime() + 120);

        // Release the lock now.
        $lock->release($lock_key);
      }

      /** @var \Drupal\file\FileUsage\FileUsageInterface $file_usage */
      $file_usage = \Drupal::service('file.usage');
      $file_usage->add($file, $this->getEntityTypeId(), $this->getEntityTypeId(), $this->id());

      return $file;
    }

    if ($lock_key) {
      $lock->release($lock_key);
    }

    throw new \Exception(new FormattableMarkup('Failed to save file "@file" for SKU id @sku_id.', $args));
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
      if (isset($media_item['media_type']) && $media_item['media_type'] == 'image') {
        return $media_item;
      }
    }

    return [];
  }

  /**
   * Get plugin instance for current object.
   *
   * @return null|\Drupal\acq_sku\AcquiaCommerce\SKUPluginInterface
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
   *   Log errors when SKU not found. Can be false during sync.
   * @param bool $create_translation
   *   Create translation and return if entity available but translation is not.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   Found SKU
   */
  public static function loadFromSku($sku, $langcode = '', $log_not_found = TRUE, $create_translation = FALSE) {
    if (empty($sku)) {
      // Simply log for debugging later on why this function is called
      // with empty sku.
      \Drupal::logger('acq_sku')->error('SKU::loadFromSku invoked with empty sku string: @trace.', [
        '@trace' => json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)),
      ]);

      return NULL;
    }

    $is_multilingual = \Drupal::languageManager()->isMultilingual();

    if ($is_multilingual && empty($langcode)) {
      $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    }

    // Check in static.
    $sku_static = &drupal_static(__FUNCTION__);
    if (isset($sku_static[$sku])) {
      $sku_id = $sku_static[$sku];
    }
    else {
      $database = \Drupal::database();
      $sku_records = $database->query('SELECT id FROM {acq_sku_field_data} WHERE sku=:sku', [
        ':sku' => $sku,
      ])->fetchAllKeyed(0, 0);

      // First check if we have some result before doing anything else.
      if (empty($sku_records)) {
        return NULL;
      }

      // If we find more than one, raise a log.
      if (!empty($sku_records) && count($sku_records) > 1) {
        \Drupal::logger('acq_sku')->error('Duplicate SKUs found while loading for @sku & lang code: @langcode.', ['@sku' => $sku, '@langcode' => $langcode]);
      }

      // We should always get one, but get first SKU entity for processing just
      // in case.
      $sku_id = reset($sku_records);
      // Stash before return.
      $sku_static[$sku] = $sku_id;
    }

    $storage = \Drupal::entityTypeManager()->getStorage('acq_sku');
    $sku_entity = $storage->load($sku_id);

    // Sanity check.
    if (!($sku_entity instanceof SKUInterface)) {
      return NULL;
    }

    if ($is_multilingual && $sku_entity->language()->getId() != $langcode) {
      if ($sku_entity->hasTranslation($langcode)) {
        $sku_entity = $sku_entity->getTranslation($langcode);
      }
      elseif ($create_translation) {
        $sku_entity = $sku_entity->addTranslation($langcode, ['sku' => $sku]);
      }
      // Don't log for missing translation if flag is set to false.
      elseif ($log_not_found) {
        \Drupal::logger('acq_sku')->error('SKU translation not found of @sku for @langcode', ['@sku' => $sku, '@langcode' => $langcode]);
      }
    }
    return $sku_entity;
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
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t("The SKU's human-friendly name."))
      ->setTranslatable(TRUE)
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
      ->setTranslatable(TRUE)
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
      ->setTranslatable(FALSE)
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
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['final_price'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Final Price'))
      ->setDescription(t('Final Price of this SKU.'))
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['crosssell'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Cross sell SKU'))
      ->setDescription(t('Reference to all Cross sell SKUs.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 5,
      ])
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', TRUE);

    $fields['upsell'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Up sell SKU'))
      ->setDescription(t('Reference to all up sell SKUs.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 6,
      ])
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', TRUE);

    $fields['related'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Related SKU'))
      ->setDescription(t('Reference to all related SKUs.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 7,
      ])
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', TRUE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setDescription(t('Product image'))
      ->setTranslatable(TRUE)
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
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['attributes'] = BaseFieldDefinition::create('key_value')
      ->setLabel(t('Attributes'))
      ->setDescription(t('Non-Drupal native product data.'))
      ->setTranslatable(TRUE)
      ->setCardinality(-1)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setTranslatable(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'))
      ->setTranslatable(TRUE);

    $fields['attribute_set'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Attribute Set'))
      ->setDescription(t('Attribtue set for the SKU.'))
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['product_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Product Id'))
      ->setDescription(t('Commerce Backend Product Id.'))
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Get all the fields added by other modules and add them as base fields.
    $additionalFields = \Drupal::service('acq_sku.fields_manager')->getFieldAdditions();

    // Get the default weight increment value from variables.
    $defaultWeightIncrement = \Drupal::state()->get('acq_sku.base_field_weight_increment', 20);

    // Check if we have additional fields to be added as base fields.
    if (!empty($additionalFields) && is_array($additionalFields)) {
      foreach ($additionalFields as $machine_name => $field_info) {
        // Showing the fields at the bottom.
        $weight = $defaultWeightIncrement + count($fields);

        // Get field definition using basic field info.
        $field = \Drupal::service('acq_sku.fields_manager')->getFieldDefinitionFromInfo($field_info, $weight);

        // We will use attr prefix to avoid conflicts with default base fields.
        $fields['attr_' . $machine_name] = $field;
      }
    }

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Delete media files.
    foreach ($entities as $entity) {
      /** @var \Drupal\acq_sku\Entity\SKU $entity */
      foreach ($entity->getMedia(FALSE) as $media) {
        if ($media['file'] instanceof FileInterface) {
          $media['file']->delete();
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Reset static cache after saving any SKU.
    // This is done by default when using entity storage.
    // We don't use entity storage and use custom code for static cache.
    drupal_static_reset('getParentSkuIds');
    drupal_static_reset('getAvailableChildrenIds');
  }

  /**
   * {@inheritdoc}
   */
  public function refreshStock() {
    $plugin = $this->getPluginInstance();
    $plugin->refreshStock($this);
  }

  /**
   * Get File Storage.
   *
   * @return \Drupal\file\FileStorageInterface
   *   File Storage.
   */
  private function getFileStorage() {
    static $storage;

    if (empty($storage)) {
      $storage = $this->entityTypeManager()->getStorage('file');
    }

    return $storage;
  }

}
