<?php

namespace Drupal\alshaya_facets_pretty_paths;

use Drupal\acq_sku\ProductOptionsManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\facets_summary\FacetsSummaryManager\DefaultFacetsSummaryManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageInterface;

/**
 * Utilty Class.
 */
class AlshayaFacetsPrettyPathsHelper {

  use StringTranslationTrait;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Term Storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * Node Storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Default Facets Summary Manager.
   *
   * @var \Drupal\facets_summary\FacetsSummaryManager\DefaultFacetsSummaryManager
   */
  protected $defaultFacetsSummaryManager;

  /**
   * Facet Summary Object.
   *
   * @var \Drupal\facets_summary\FacetsSummaryInterface
   */
  protected $facetSummaryStorage;

  /**
   * Replacement characters for facet values.
   */
  const REPLACEMENTS = [
    // Convert space to double underscore.
    ' ' => '__',
    // Convert hyphen to underscore.
    '-' => '_',
  ];

  /**
   * Meta info type select list values.
   */
  const FACET_META_TYPE_IGNORE = 0;
  const FACET_META_TYPE_PREFIX = 1;
  const FACET_META_TYPE_SUFFIX = 2;
  const VISIBLE_IN_META_TITLE = 4;
  const VISIBLE_IN_META_DESCRIPTION = 5;

  /**
   * UserRecentOrders constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facets_manager
   *   Facet manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\facets_summary\FacetsSummaryManager\DefaultFacetsSummaryManager $default_facets_summary_manager
   *   Default Facets Summary Manager.
   */
  public function __construct(RouteMatchInterface $route_match,
                              RequestStack $request_stack,
                              EntityTypeManagerInterface $entity_type_manager,
                              LanguageManagerInterface $language_manager,
                              AliasManagerInterface $alias_manager,
                              DefaultFacetManager $facets_manager,
                              ConfigFactoryInterface $config_factory,
                              SkuManager $sku_manager,
                              DefaultFacetsSummaryManager $default_facets_summary_manager) {
    $this->routeMatch = $route_match;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->facetSummaryStorage = $entity_type_manager->getStorage('facets_summary');
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
    $this->facetManager = $facets_manager;
    $this->configFactory = $config_factory;
    $this->skuManager = $sku_manager;
    $this->defaultFacetsSummaryManager = $default_facets_summary_manager;
  }

  /**
   * Encode url components according to given rules.
   *
   * @param string $source
   *   Facet source.
   * @param string $alias
   *   Facet alias.
   * @param string $value
   *   Raw element value.
   *
   * @return string
   *   Encoded element.
   */
  public function encodeFacetUrlComponents(string $source, string $alias, string $value) {
    $static = &drupal_static(__FUNCTION__, []);
    if (isset($static[$alias][$value])) {
      return $static[$alias][$value];
    }

    $attribute_code = $this->getFacetAliasFieldMapping($source)[$alias];
    $is_swatch = in_array($attribute_code, $this->skuManager->getProductListingSwatchAttributes());
    $encoded = $value;

    $storage = $this->termStorage;
    $entity_type = 'term';

    // We use ids only for category.
    if ($attribute_code === 'field_category') {
      $static[$alias][$value] = $value;
      return $value;
    }
    elseif ($attribute_code == 'field_acq_promotion_label') {
      $storage = $this->nodeStorage;
      $entity_type = 'node';
      $query = $storage->getQuery();
      $query->condition('type', 'acq_promotion');
      $query->condition('status', NodeInterface::PUBLISHED);
      $query->condition($attribute_code, $value);
    }
    elseif ($is_swatch) {
      $query = $storage->getQuery();
      $query->condition('field_sku_option_id', $value);
      $query->condition('field_sku_attribute_code', $attribute_code);
      $query->condition('vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY);
    }
    else {
      $query = $storage->getQuery();
      $query->condition('name', $value);
      $query->condition('field_sku_attribute_code', $attribute_code);
      $query->condition('vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY);
    }
    $ids = $query->execute();
    $langcode = 'en';

    foreach ($ids ?? [] as $id) {
      if ($entity_type == 'term') {
        $alias = $this->aliasManager->getAliasByPath('/taxonomy/term/' . $id, $langcode);
        $alias = trim($alias, '/');

        if (strpos($alias, 'taxonomy/term') === FALSE) {
          $encoded = str_replace($this->getProductOptionAliasPrefix() . '/', '', $alias);

          // Decode it once, it will be encoded again later.
          $encoded = urldecode($encoded);
          break;
        }
      }
      else {
        $alias = $this->aliasManager->getAliasByPath('/node/' . $id, $langcode);
        $alias = trim($alias, '/');

        if (strpos($alias, 'node/') === FALSE) {
          // Decode it once, it will be encoded again later.
          $encoded = urldecode($alias);
          break;
        }
      }
    }

    foreach (self::REPLACEMENTS as $original => $replacement) {
      $encoded = str_replace($original, $replacement, $encoded);
    }

    $static[$alias][$value] = $encoded;
    return $encoded;
  }

  /**
   * Decode url components according to given rules.
   *
   * @param string $source
   *   Facet source.
   * @param string $alias
   *   Facet alias.
   * @param string $value
   *   Encoded element value.
   *
   * @return string
   *   Raw element.
   */
  public function decodeFacetUrlComponents(string $source, string $alias, string $value) {
    $static = &drupal_static(__FUNCTION__, []);
    if (isset($static[$value])) {
      return $static[$value];
    }

    $attribute_code = $this->getFacetAliasFieldMapping($source)[$alias];
    $is_swatch = in_array($attribute_code, $this->skuManager->getProductListingSwatchAttributes());

    // We use ids only for category.
    if ($attribute_code === 'field_category') {
      $static[$value] = $value;
      return $value;
    }

    $decoded = $value;
    foreach (self::REPLACEMENTS as $original => $replacement) {
      $decoded = str_replace($replacement, $original, $decoded);
    }

    $current_langcode = $this->languageManager->getCurrentLanguage()->getId();

    $type = 'term';
    $storage = $this->termStorage;

    if ($alias == 'promotions') {
      $type = 'node';
      $storage = $this->nodeStorage;
    }

    $id = str_replace(
      $type == 'term' ? '/taxonomy/term/' : '/node/',
      '',
      $this->aliasManager->getPathByAlias($type == 'term' ? '/' . $this->getProductOptionAliasPrefix() . '/' . $decoded : '/' . $decoded, $current_langcode)
    );

    if ($id) {
      $entity = $storage->load($id);

      if ($entity instanceof EntityInterface) {
        if ($entity->language()->getId() != $current_langcode && $entity->hasTranslation($current_langcode)) {
          $entity = $entity->getTranslation($current_langcode);
        }

        if ($type === 'term') {
          $decoded = $is_swatch ? $entity->get('field_sku_option_id')->getString() : $entity->label();
        }
        else {
          $decoded = $entity->get('field_acq_promotion_label')->getString();
        }
      }
    }

    $static[$value] = $decoded;
    return $decoded;
  }

  /**
   * Get active facets from request or route.
   *
   * @return array
   *   Filter array.
   */
  public function getActiveFacetFilters() {
    $alshaya_active_facet_filters = &drupal_static(__FUNCTION__, NULL);

    if (isset($alshaya_active_facet_filters)) {
      return $alshaya_active_facet_filters;
    }

    $alshaya_active_facet_filter_string = '';
    if ($this->routeMatch->getParameter('facets_query')) {
      $alshaya_active_facet_filter_string = $this->routeMatch->getParameter('facets_query');
    }
    elseif ($this->routeMatch->getRouteName() === 'views.ajax') {
      $q = $this->currentRequest->query->get('q') ?? $this->currentRequest->query->get('facet_filter_url');
      if ($q) {
        $route_params = Url::fromUserInput($q)->getRouteParameters();
        if (isset($route_params['facets_query'])) {
          $alshaya_active_facet_filter_string = $route_params['facets_query'];
        }
      }
    }
    elseif ($this->routeMatch->getRouteName() === 'facets.block.ajax') {
      $alshaya_active_facet_filter_string = $this->currentRequest->query->get('facet_link');
      $alshaya_active_facet_filter_string = substr($alshaya_active_facet_filter_string, strpos($alshaya_active_facet_filter_string, "/--") + 3);
    }
    elseif (strpos($this->currentRequest->getPathInfo(), "/--") !== FALSE) {
      $alshaya_active_facet_filter_string = substr($this->currentRequest->getPathInfo(), strpos($this->currentRequest->getPathInfo(), "/--") + 3);
    }

    $alshaya_active_facet_filter_string = rtrim($alshaya_active_facet_filter_string, '/');

    // For example, if we received: "/--price-0/any-radom-string"
    // We need to remove "/any-radom-string", from active filter's string.
    $alshaya_active_facet_filter_string = !empty($alshaya_active_facet_filter_string) ? explode('/', $alshaya_active_facet_filter_string)[0] : $alshaya_active_facet_filter_string;

    $alshaya_active_facet_filters = array_filter(explode('--', $alshaya_active_facet_filter_string));

    return $alshaya_active_facet_filters;
  }

  /**
   * Get alias <=> field mapping for facets.
   *
   * @param string $source
   *   Facet Source.
   *
   * @return array
   *   Mapping with alias as key and field as value.
   */
  public function getFacetAliasFieldMapping(string $source) {
    $static = &drupal_static(__FUNCTION__, []);

    if (isset($static[$source])) {
      return $static[$source];
    }

    $static[$source] = [];

    // Get all facets of the given source.
    $facets = $this->facetManager->getFacetsByFacetSourceId($source);
    foreach ($facets ?? [] as $facet) {
      $static[$source][$facet->getUrlAlias()] = str_replace('attr_', '', $facet->getFieldIdentifier());
    }
    return $static[$source];
  }

  /**
   * Get the product options taxonomy path alias prefix.
   *
   * @return string
   *   Prefix.
   */
  public function getProductOptionAliasPrefix() {
    static $prefix = NULL;

    if (!isset($prefix)) {
      $pattern = $this->configFactory->get('pathauto.pattern.sku_product_option')->get('pattern');
      $prefix = str_replace('/[term:name]', '', $pattern);
    }

    return $prefix;
  }

  /**
   * To get meta info type of the given facet id.
   *
   * @param string $facet_id
   *   Facet id.
   *
   * @return array
   *   Meta info type array.
   */
  public function getMetaInfotypeFromFacetId($facet_id) {
    $static = &drupal_static(__FUNCTION__, []);
    if (!empty($static[$facet_id])) {
      return $static[$facet_id];
    }

    $config = \Drupal::config('facets.facet.' . $facet_id);
    $meta_info_type = $config->get('third_party_settings.alshaya_facets_pretty_paths.meta_info_type');
    $type = $meta_info_type['type'] ?? self::FACET_META_TYPE_IGNORE;
    $facet_prefix_text = $meta_info_type['prefix_text'] ?? '';
    $facet_visibility = $meta_info_type['visibility'] ?? '';
    $static[$facet_id] = [
      'type' => $type,
      // Since prefix text is dynamic - Size/at. we use t() with variable.
      // @codingStandardsIgnoreLine
      'prefix_text' => $this->t($facet_prefix_text),
      'visibility' => $facet_visibility,
    ];
    return $static[$facet_id];
  }

  /**
   * To get the active facet summary items.
   *
   * @param int $visibility
   *   Visibility constant.
   * @param int $page
   *   Current page.
   *
   * @return array
   *   Active prefix/suffix facets.
   */
  public function getFacetSummaryItems($visibility, $page) {
    $static = &drupal_static(__FUNCTION__, []);

    if (isset($static[$visibility][$page])) {
      return $static[$visibility][$page];
    }
    // Reusing the facet summary block values.
    $active_facet_items = $this->getFacetSummary($page);
    $active_prefix_facet = [];
    $active_suffix_facet = [];
    foreach ($active_facet_items as $value) {
      if (isset($value['#title']) && isset($value['#attributes'])) {
        $active_facet_id = $value['#attributes']['data-drupal-facet-id'];
        $meta_info_type = $this->getMetaInfotypeFromFacetId($active_facet_id);
        if (in_array($visibility, $meta_info_type['visibility'])) {
          if ($meta_info_type['type'] == self::FACET_META_TYPE_PREFIX) {
            // Strip tags to get text from the price range markup.
            // Language direction left to right (5 KWD - 10 KWD).
            $active_prefix_facet[] = (!empty($meta_info_type['prefix_text'])) ? $meta_info_type['prefix_text'] . ' ' . strip_tags($value['#title']['#value']) : strip_tags($value['#title']['#value']);
          }
          elseif ($meta_info_type['type'] == self::FACET_META_TYPE_SUFFIX) {
            $facet_value = strip_tags($value['#title']['#value']);
            // Condition added for the price range filter order
            // right to left for Arabic site.
            // Ex. If filtered by the facet '5 KWD - 10 KWD':
            // 1. For en site the value will be 5 KWD - 10 KWD.
            // 2. Reversing the value for ar site 10 KWD - 5 KWD.
            if (strpos($active_facet_id, 'price') > -1) {
              $prices = explode(' - ', $facet_value);
              array_map('trim', $prices);
              // Checking if the current language's direction
              // is right to left.
              if (count($prices) > 1 && $this->languageManager->getCurrentLanguage()->getDirection() === LanguageInterface::DIRECTION_RTL) {
                // Reversing the array as it will be used in string(meta description)
                // Ex: Shop an exclusive and luxurious range of
                // short dresses for women from H&M starting
                // from 5,000D0K0-10,000D0K0 online in
                // Kuwait City and throughout Kuwait.
                // Compare specifications and prices
                // for 2020 short dresses and more. Enjoy free returns
                // and cash on delivery! (translated ar text).
                $prices = array_reverse($prices);
              }

              $facet_value = implode(' - ', $prices);
            }
            // Strip tags to get the value from price markup.
            $active_suffix_facet[] = (!empty($meta_info_type['prefix_text'])) ? $meta_info_type['prefix_text'] . ' ' . $facet_value : $facet_value;
          }
        }
      }
    }

    $static[$visibility][$page] = [$active_prefix_facet, $active_suffix_facet];
    return $static[$visibility][$page];
  }

  /**
   * To get the active facet summary.
   *
   * @param string $page
   *   Current page.
   *
   * @return array
   *   Active facet summary.
   */
  public function getFacetSummary($page) {
    $active_facet_items = &drupal_static(__FUNCTION__);
    if (isset($active_facet_items)) {
      return $active_facet_items;
    }
    // Active facet items for PLP pages.
    $summary = _alshaya_facets_pretty_paths_get_mappings()[$page]['summary'];
    $facet_summary = $this->facetSummaryStorage->load($summary);
    $alshaya_facet_summary = $this->defaultFacetsSummaryManager->build($facet_summary);
    $active_facet_items = $alshaya_facet_summary['#items'];
    return $active_facet_items;
  }

  /**
   * Wrapper function to check if pretty path is enabled or not.
   *
   * @param string $type
   *   Page type - plp / search / promo.
   *
   * @return bool
   *   TRUE if enabled.
   */
  public function isPrettyPathEnabled(string $type) {
    $static = &drupal_static(__FUNCTION__, []);

    if (isset($static[$type])) {
      return $static[$type];
    }

    $mapping = _alshaya_facets_pretty_paths_get_mappings()[$type];
    $source = $this->configFactory->getEditable('facets.facet_source.search_api__' . $mapping['id']);
    $static[$type] = ($source->get('url_processor') === 'alshaya_facets_pretty_paths');
    return $static[$type];
  }

}
