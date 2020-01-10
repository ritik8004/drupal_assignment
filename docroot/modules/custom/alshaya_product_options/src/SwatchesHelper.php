<?php

namespace Drupal\alshaya_product_options;

use Drupal\acq_sku\ProductOptionsManager;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Site\Settings;
use Drupal\facets\FacetInterface;
use Drupal\taxonomy\TermInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use Drupal\alshaya_product_options\Brand\AlshayaBrandListHelper;

/**
 * Class SwatchesHelper.
 *
 * @package Drupal\alshaya_product_options
 */
class SwatchesHelper {

  /**
   * Constant for identifying textual swatch type.
   */
  const SWATCH_TYPE_TEXTUAL = '0';

  /**
   * Constant for identifying visual swatch type with color number value.
   */
  const SWATCH_TYPE_VISUAL_COLOR = '1';

  /**
   * Constant for identifying visual swatch type with color number value.
   */
  const SWATCH_TYPE_VISUAL_IMAGE = '2';

  /**
   * Constant for identifying empty swatch type.
   */
  const SWATCH_TYPE_EMPTY = '3';

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * File Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Production Options Manager service object.
   *
   * @var \Drupal\acq_sku\ProductOptionsManager
   */
  protected $productOptionsManager;

  /**
   * Cache Backend service for product_options.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * SKU Fields Manager.
   *
   * @var \Drupal\acq_sku\SKUFieldsManager
   */
  protected $skuFieldsManager;

  /**
   * Contain the sku base field array.
   *
   * @var array
   */
  protected $skuBaseFieldDefination = [];

  /**
   * GuzzleHttp\Client definition.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * SwatchesHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   * @param \Drupal\acq_sku\ProductOptionsManager $product_options_manager
   *   Production Options Manager service object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for product_options.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\acq_sku\SKUFieldsManager $sku_fields_manager
   *   SKU Fields Manager.
   * @param \GuzzleHttp\Client $http_client
   *   GuzzleHttp\Client object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LoggerChannelInterface $logger,
                              ProductOptionsManager $product_options_manager,
                              CacheBackendInterface $cache,
                              LanguageManagerInterface $language_manager,
                              SKUFieldsManager $sku_fields_manager,
                              Client $http_client,
                              ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileStorage = $this->entityTypeManager->getStorage('file');
    $this->logger = $logger;
    $this->productOptionsManager = $product_options_manager;
    $this->cache = $cache;
    $this->languageManager = $language_manager;
    $this->skuFieldsManager = $sku_fields_manager;
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
  }

  /**
   * Update Term with Attribute option value if changed.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Taxonomy term.
   * @param array $swatch_info
   *   Swatch info array received from API.
   */
  public function updateAttributeOptionSwatch(TermInterface $term, array $swatch_info) {
    $save_term = FALSE;
    $logo_attribute = AlshayaBrandListHelper::getLogoAttribute();

    // Save again if type changed.
    if ($swatch_info['swatch_type'] != $term->get('field_attribute_swatch_type')->getString()) {
      $save_term = TRUE;
    }
    // Save again if value changed.
    elseif ($term->get('field_attribute_swatch_value')->getString() != $swatch_info['swatch']) {
      $save_term = TRUE;
    }
    // Save again if swatch original image is not avaialable.
    elseif (($swatch_info['swatch_type'] == self::SWATCH_TYPE_VISUAL_IMAGE) && $term->get('field_sku_attribute_code')->getString() === $logo_attribute && empty($term->get('field_attribute_swatch_org_image')->getString())) {
      $save_term = TRUE;
    }

    if ($save_term) {
      // Delete existing file first.
      if ($term->get('field_attribute_swatch_image')->first()) {
        $file_value = $term->get('field_attribute_swatch_image')->first()->getValue();
        $file = $this->fileStorage->load($file_value['target_id']);
        if ($file) {
          $file->delete();
        }
      }

      // Reset all current values.
      $term->get('field_attribute_swatch_text')->setValue(NULL);
      $term->get('field_attribute_swatch_color')->setValue(NULL);
      $term->get('field_attribute_swatch_image')->setValue(NULL);

      // Saving in separate field to validate next time for change.
      $term->get('field_attribute_swatch_value')->setValue($swatch_info['swatch']);
      $term->get('field_attribute_swatch_type')->setValue($swatch_info['swatch_type']);

      switch ($swatch_info['swatch_type']) {
        case self::SWATCH_TYPE_TEXTUAL:
          $term->get('field_attribute_swatch_text')->setValue(
            $swatch_info['swatch']
          );
          break;

        case self::SWATCH_TYPE_VISUAL_COLOR:
          $term->get('field_attribute_swatch_color')->setValue(
            $swatch_info['swatch']
          );
          break;

        case self::SWATCH_TYPE_VISUAL_IMAGE:
          try {
            $file = $this->downloadSwatchImage($swatch_info['swatch']);
            // We will allow to store swatch original image
            // If it's settings mdc_swatch_image_style is available.
            $mdcSwatchImageStyle = $this->configFactory
              ->get('alshaya_product_options.settings')
              ->get('mdc_swatch_image_style') ?? '';
            if (!empty($mdcSwatchImageStyle)) {
              $swatchOriginalImageUrl = str_replace($mdcSwatchImageStyle . '/', '', $swatch_info['swatch']);
              $swatchOriginalImagefile = $this->downloadSwatchImage($swatchOriginalImageUrl);
              if (!empty($swatchOriginalImagefile)) {
                $term->get('field_attribute_swatch_org_image')->setValue($swatchOriginalImagefile);
              }
            }
          }
          catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return;
          }

          $term->get('field_attribute_swatch_image')->setValue($file);
          break;

        default:
          // Swatch type not known.
          return;
      }
    }

    if ($save_term) {
      $term->save();
    }

    // We might not save the term for multiple languages but we store cache
    // per language. Lets update cache.
    $attribute_code = $term->get('field_sku_attribute_code')->getString();
    $option_id = $term->get('field_sku_option_id')->getString();
    $langcode = $term->language()->getId();
    $tid = $term->id();

    $cache = $this->getSwatchDataFromTerm($term);
    $this->updateCache($attribute_code, $option_id, $langcode, $tid, $cache);
  }

  /**
   * Get swatch data for particular attribute option.
   *
   * @param string $attribute_code
   *   Attribute code.
   * @param string $option_id
   *   Attribute option id.
   * @param string $langcode
   *   Language code to get data for particular language.
   *
   * @return array
   *   Swatch data.
   */
  public function getSwatch($attribute_code, $option_id, $langcode = '') {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $cid = implode('_', [$attribute_code, $option_id, $langcode]);
    $cache = $this->cache->get($cid);

    // Try once to load the term and check for cache.
    // If memcache went down or someone edited term directly in Drupal.
    if (empty($cache)) {
      $data = [];
      $term = $this->productOptionsManager->loadProductOptionByOptionId($attribute_code, $option_id, $langcode);
      if ($term instanceof TermInterface) {
        $data = $this->getSwatchDataFromTerm($term);
        $this->updateCache($attribute_code, $option_id, $langcode, $term->id(), $data);
      }
    }
    else {
      $data = $cache->data;
    }

    return $data;
  }

  /**
   * Wrapper function to store data in cache.
   *
   * @param string $attribute_code
   *   Attribute code.
   * @param string $option_id
   *   Attribute option id.
   * @param string $langcode
   *   Language code to build unique cache id per language.
   * @param int $tid
   *   Term id to use for cache tag.
   * @param array $data
   *   Data to cache.
   */
  private function updateCache($attribute_code, $option_id, $langcode, $tid, array $data) {
    $cid = implode('_', [$attribute_code, $option_id, $langcode]);
    $this->cache->set($cid, $data, Cache::PERMANENT, ['taxonomy_term:' . $tid]);
  }

  /**
   * Download swatch image to Drupal and create File entity.
   *
   * @param string $url
   *   Swatch image url.
   *
   * @return \Drupal\file\Entity\File
   *   File entity.
   *
   * @throws \Exception
   */
  private function downloadSwatchImage($url) {
    // Preparing args for all info/error messages.
    $args = ['@file' => $url];

    // Download the file contents.
    try {
      $options = [
        'timeout' => Settings::get('media_download_timeout', 5),
      ];

      $file_data = $this->httpClient->get($url, $options)->getBody();
    }
    catch (RequestException $e) {
      watchdog_exception('alshaya_product_options', $e);
    }

    // Check to ensure empty file not saved in SKU.
    if (empty($file_data)) {
      throw new \Exception(new FormattableMarkup('Failed to download file "@file".', $args));
    }

    // Get the path part in the url, remove hostname.
    $path = parse_url($url, PHP_URL_PATH);

    // Remove slashes from start and end.
    $path = trim($path, '/');

    // Get the file name.
    $file_name = basename($path);

    // Prepare the directory path.
    $directory = 'public://swatches/' . str_replace('/' . $file_name, '', $path);

    // Prepare the directory.
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

    // Save the file as file entity.
    /** @var \Drupal\file\Entity\File $file */
    if ($file = file_save_data($file_data, $directory . '/' . $file_name, FILE_EXISTS_REPLACE)) {
      return $file;
    }
    else {
      throw new \Exception(new FormattableMarkup('Failed to save file "@file".', $args));
    }
  }

  /**
   * Wrapper function to get array containing only the required swatch data.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Product option Term.
   *
   * @return array
   *   Swatch data.
   */
  private function getSwatchDataFromTerm(TermInterface $term) {
    $data = [];
    $data['type'] = $term->get('field_attribute_swatch_type')->getString();
    $data['name'] = $term->getName();

    // 0 is valid type, chech specifically for empty/null values.
    if ($data['type'] === NULL or $data['type'] === '') {
      $this->logger->warning('No swatch type found for attribute term id: @id with value: @value', [
        '@id' => $term->id(),
        '@value' => $term->label(),
      ]);

      $data['type'] = self::SWATCH_TYPE_TEXTUAL;
    }

    switch ($data['type']) {
      case self::SWATCH_TYPE_TEXTUAL:
        $data['swatch'] = $term->get('field_attribute_swatch_text')->getString();
        break;

      case self::SWATCH_TYPE_VISUAL_COLOR:
        $data['swatch'] = $term->get('field_attribute_swatch_color')->getString();
        break;

      case self::SWATCH_TYPE_VISUAL_IMAGE:
        if ($term->get('field_attribute_swatch_image')->first()) {
          $file_value = $term->get('field_attribute_swatch_image')
            ->first()
            ->getValue();

          /** @var \Drupal\file\Entity\File $file */
          $file = $this->fileStorage->load($file_value['target_id']);
          $data['swatch'] = file_url_transform_relative(file_create_url($file->getFileUri()));
        }
        break;

      default:
        return [];
    }

    return $data;
  }

  /**
   * Get swatch data for value of particular facet.
   *
   * @param \Drupal\facets\FacetInterface $facet
   *   Facet Entity.
   * @param string $value
   *   Value to look swatch data for.
   *
   * @return array
   *   Swatch data or empty array.
   */
  public function getSwatchForFacet(FacetInterface $facet, $value) {
    if (empty($this->skuBaseFieldDefination)) {
      $this->skuBaseFieldDefination = $this->skuFieldsManager->getFieldAdditions();
      // Filter attributes fields.
      $this->skuBaseFieldDefination = array_filter($this->skuBaseFieldDefination, function ($field) {
        return ($field['parent'] == 'attributes');
      });
    }

    $field_code = str_replace('attr_', '', $facet->getFieldIdentifier());

    if (!isset($this->skuBaseFieldDefination[$field_code])) {
      return [];
    }
    // If field/facet is not swatchable, no need to process further.
    if (isset($this->skuBaseFieldDefination[$field_code])
      && empty($this->skuBaseFieldDefination[$field_code]['swatch'])) {
      return [];
    }

    $attribute_code = $this->skuBaseFieldDefination[$field_code]['source'] ?? $field_code;
    return $this->getSwatch($attribute_code, $value);
  }

}
