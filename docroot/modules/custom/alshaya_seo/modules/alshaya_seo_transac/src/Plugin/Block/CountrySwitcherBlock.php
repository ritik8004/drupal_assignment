<?php

namespace Drupal\alshaya_seo_transac\Plugin\Block;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\external_hreflang\Plugin\metatag\Tag\ExternalHreflang;

/**
 * Provides country switcher block in all brands.
 *
 * @Block(
 *   id = "alshaya_seo_transac_country_switcher",
 *   admin_label = @Translation("Country Switcher")
 * )
 */
class CountrySwitcherBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AlshayaMegaMenuBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];
    $hreflangsFiltered = [];
    // Fetch hreflang_external metatag from taxonomy_term__acq_product_category.
    // If empty fallback to global.
    $hreflang_tags = $this->fetchMetaTags('taxonomy_term__acq_product_category');
    if (empty($hreflang_tags)) {
      $hreflang_tags = $this->fetchMetaTags('global');
      if (empty($hreflang_tags)) {
        return NULL;
      }
    }

    $hreflangs = ExternalHreflang::getHrefLangsArrayFromString($hreflang_tags);
    $activeLanguages = array_keys($this->languageManager->getLanguages());

    // Building final array to render.
    foreach ($hreflangs as $key => $marketUrl) {
      $hreflangItems = explode('-', $key);
      // Checking if language code is present.
      if (isset($hreflangItems[0]) && in_array($hreflangItems[0], $activeLanguages)) {
        $hreflangItems[1] = strtoupper($hreflangItems[1]);
        // Filtering target url.
        $hreflangsFiltered[implode('-', $hreflangItems)] = preg_replace('/\[.*?\]/', NULL, $marketUrl);
      }
    }

    // Fetching current site info for dropdown selected attribute.
    $currentMarket = $this->languageManager->getCurrentLanguage()->getId() . '-' . strtoupper($this->configFactory->get('system.date')->get('country.default'));

    $output['sections'] = [
      '#theme' => 'alshaya_country_switcher',
      '#markets' => $hreflangsFiltered,
      '#currentMarket' => $currentMarket,
    ];

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Discard cache for the block once metatag gets updated.
    $cacheTags = [
      'config:metatag.metatag_defaults.global',
      'config:metatag.metatag_defaults.taxonomy_term__acq_product_category',
    ];

    return Cache::mergeTags(
      parent::getCacheTags(),
      $cacheTags
    );
  }

  /**
   * Fetches hreflang_external metatag of an entity.
   *
   * @param string $entityName
   *   The metatag entity name.
   *
   * @return array
   *   hreflang_external metatag.
   */
  protected function fetchMetaTags($entityName) {
    $tagList = [];
    $entity = $this->entityTypeManager->getStorage('metatag_defaults')->load($entityName);
    if (!empty($entity)) {
      $tagList = $entity->get('tags');
      $tagList = $tagList['hreflang_external'] ?? [];
    }

    return $tagList;
  }

}
