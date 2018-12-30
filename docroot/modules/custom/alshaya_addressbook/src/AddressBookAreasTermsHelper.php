<?php

namespace Drupal\alshaya_addressbook;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class AddressBookAreasTermsHelper.
 *
 * @package Drupal\alshaya_addressbook
 */
class AddressBookAreasTermsHelper {

  /**
   * Entity Repository object.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorage
   */
  protected $termStorage;

  /**
   * Lanaguage Manager object.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Cache Backend object for "cache.data".
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * DM Version value loaded from config.
   *
   * @var string
   */
  protected $dmVersion;

  /**
   * AddressBookAreasTermsHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language manager object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend object for "cache.data".
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              EntityRepositoryInterface $entity_repository,
                              LanguageManagerInterface $languageManager,
                              ConfigFactoryInterface $config_factory,
                              CacheBackendInterface $cache) {
    $this->entityRepository = $entity_repository;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->languageManager = $languageManager;
    $this->configFactory = $config_factory;
    $this->cache = $cache;
    $this->dmVersion = $this->configFactory->get('alshaya_addressbook.settings')
      ->get('dm_version');
  }

  /**
   * Returns list of governates for a given country.
   *
   * @return array
   *   List or governates.
   */
  public function getAllGovernates() {
    $governate = $this->getAddressCachedData('getAllGovernates');
    if (is_array($governate)) {
      return $governate;
    }

    $term_tree = $this->termStorage->loadTree(AlshayaAddressBookManagerInterface::AREA_VOCAB, 0, 1, TRUE);

    $term_list = [];

    if (!empty($term_tree)) {
      foreach ($term_tree as $term) {
        /* \Drupal\taxonomy\Entity\Term $term */
        $term = $this->entityRepository->getTranslationFromContext($term);
        $term_list[$term->id()] = $term->getName();
      }
    }

    asort($term_list);

    $this->setAddressCachedData($term_list, 'getAllGovernates');

    return $term_list;
  }

  /**
   * Returns list of areas for a given governate.
   *
   * @param int $parent
   *   Parent TID for which terms are to be fetched.
   *
   * @return array
   *   List or areas.
   */
  public function getAllAreasWithParent($parent = NULL) {
    if (empty($parent) && $this->dmVersion == AlshayaAddressBookManagerInterface::DM_VERSION_2) {
      // Parent is required in DM_VERSION_2, not throwing error though.
      return [];
    }

    $term_tree = $this->termStorage->loadTree(AlshayaAddressBookManagerInterface::AREA_VOCAB, $parent, 1, TRUE);

    $term_list = [];

    if (!empty($term_tree)) {
      foreach ($term_tree as $term) {
        /* \Drupal\taxonomy\Entity\Term $term */
        $term = $this->entityRepository->getTranslationFromContext($term);

        if ($this->dmVersion == AlshayaAddressBookManagerInterface::DM_VERSION_2) {
          $term_list[$term->id()] = $term->label();
        }
        else {
          $term_list[$term->label()] = $term->label();
        }
      }
    }

    asort($term_list);

    return $term_list;
  }

  /**
   * Returns list of all areas.
   *
   * @return array
   *   List or areas.
   */
  public function getAllAreas() {
    $area = $this->getAddressCachedData('getAllAreas');

    if (is_array($area)) {
      return $area;
    }

    $term_tree = $this->termStorage->loadTree(AlshayaAddressBookManagerInterface::AREA_VOCAB, 0, 2, TRUE);

    $term_list = [];

    if (!empty($term_tree)) {
      foreach ($term_tree as $term) {
        // We get 1st and 2nd levels, also we check parents
        // (only 2nd level has parents).
        if (empty($this->termStorage->loadParents($term->id()))) {
          continue;
        }

        /* \Drupal\taxonomy\Entity\Term $term */
        $term = $this->entityRepository->getTranslationFromContext($term);
        $term_list[$term->id()] = $term->label();
      }
    }

    asort($term_list);

    $this->setAddressCachedData($term_list, 'getAllAreas');

    return $term_list;
  }

  /**
   * Helper method to fetch TID from area vocab, from the param provided.
   *
   * @param array $conditions
   *   An array of associative array containing conditions, to be used in query,
   *   with following elements:
   *   - 'field': Name of the field being queried.
   *   - 'value': The value for field.
   *   - 'operator': Possible values like '=', '<>', '>', '>=', '<', '<='.
   *
   * @return array
   *   Array of term objects.
   */
  private function getLocationTerms(array $conditions = []) {
    $locations = $this->getAddressCachedData('getLocationTerms');

    if (is_array($locations)) {
      return $locations;
    }

    $terms = [];

    $query = $this->termStorage->getQuery()->condition(
      'vid',
      AlshayaAddressBookManagerInterface::AREA_VOCAB
    );

    foreach ($conditions as $condition) {
      if (!empty($condition['field']) && !empty($condition['value'])) {
        $condition['operator'] = empty($condition['operator']) ? '=' : $condition['operator'];
        $query->condition($condition['field'], $condition['value'], $condition['operator']);
      }
    }

    $tids = $query->execute();

    if (!empty($tids)) {
      $terms = $this->termStorage->loadMultiple($tids);
    }

    $this->setAddressCachedData($terms, 'getLocationTerms');

    return $terms;
  }

  /**
   * Get location term from location id.
   *
   * @param mixed $location_id
   *   Location id.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Term if found or null.
   */
  public function getLocationTermFromLocationId($location_id) {
    if (empty($location_id)) {
      return NULL;
    }

    $terms = $this->getLocationTerms([
      [
        'field' => 'field_location_id',
        'value' => $location_id,
      ],
    ]);

    if (!empty($terms)) {
      return reset($terms);
    }

    return NULL;
  }

  /**
   * Get Shipping Area label for value based on DM Version.
   *
   * Used mainly for SEO / GTM, where we always want the English label.
   *
   * @param mixed $value
   *   Value received in shipping object.
   * @param string $langcode
   *   Language code in which we want the value to be returned.
   *
   * @return mixed|null|string
   *   String value for the area.
   */
  public function getShippingAreaLabel($value, $langcode = 'en') {
    // For DM V2, we will have it id instead of string.
    if ($value && $this->dmVersion == AlshayaAddressBookManagerInterface::DM_VERSION_2) {
      $term = $this->getLocationTermFromLocationId($value);

      if (empty($term)) {
        return '';
      }

      // We always want labels in English for GTM.
      if ($term->language()->getId() != $langcode && $term->hasTranslation($langcode)) {
        $term = $term->getTranslation($langcode);
      }

      return $term->label();
    }

    return $value;
  }

  /**
   * Get cache id for particular address area.
   *
   * @return string
   *   Cache key.
   */
  public function getAddressbookCachedId($key) {
    return 'alshaya_addressbook:' . \Drupal::languageManager()->getCurrentLanguage()->getId() . $key;
  }

  /**
   * Get data from Cache for an address area.
   *
   * @param string $key
   *   Key of the data to get from cache.
   *
   * @return array|null
   *   Data if found or null.
   */
  public function getAddressCachedData($key) {
    $data = &drupal_static($key);
    $cid = $this->getAddressbookCachedId($key);

    if ($cache = \Drupal::cache()->get($cid)) {
      $data = $cache->data;
      return $data;
    }
    return NULL;
  }

  /**
   * Set data in Cache for an address areas.
   *
   * @param array $data
   *   Data to set in cache.
   * @param string $key
   *   Key of the data to get from cache.
   */
  public function setAddressCachedData(array $data, $key) {
    $cid = $this->getAddressbookCachedId($key);
    \Drupal::cache()->set($cid, $data);
  }

}
