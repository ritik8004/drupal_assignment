<?php

namespace Drupal\alshaya_facets_pretty_paths;

use Drupal\acq_sku\ProductOptionsManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Utilty Class.
 */
class AlshayaFacetsPrettyPathsHelper {
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
   * Replacement characters for facet values.
   */
  const REPLACEMENTS = [
    // Convert space to double underscore.
    ' ' => '__',
    // Convert hyphen to underscore.
    '-' => '_',
  ];

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
   */
  public function __construct(RouteMatchInterface $route_match,
                              RequestStack $request_stack,
                              EntityTypeManagerInterface $entity_type_manager,
                              LanguageManagerInterface $language_manager,
                              AliasManagerInterface $alias_manager,
                              DefaultFacetManager $facets_manager,
                              ConfigFactoryInterface $config_factory) {
    $this->routeMatch = $route_match;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
    $this->facetManager = $facets_manager;
    $this->configFactory = $config_factory;
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
    if (is_numeric($value)) {
      return $value;
    }

    $static = &drupal_static(__FUNCTION__, []);
    if (isset($static[$alias][$value])) {
      return $static[$alias][$value];
    }

    $encoded = $value;
    $attribute_code = $this->getFacetAliasFieldMapping($source)[$alias];

    $storage = $this->termStorage;
    if ($attribute_code == 'field_acq_promotion_label') {
      $storage = $this->nodeStorage;
      $query = $storage->getQuery();
      $query->condition('type', 'acq_promotion');
      $query->condition('status', NodeInterface::PUBLISHED);
      $query->condition($attribute_code, $value);
    }
    else {
      $query = $storage->getQuery();
      $query->condition('name', $value);
      $query->condition('field_sku_attribute_code', $attribute_code);
      $query->condition('vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY);
    }
    $ids = $query->execute();
    foreach ($ids ?? [] as $id) {
      $entity = $storage->load($id);
      if ($entity instanceof EntityInterface) {
        if ($entity->language()->getId() != 'en' && $entity->hasTranslation('en')) {
          $entity = $entity->getTranslation('en');
        }

        $encoded = str_replace(
          $entity instanceof TermInterface ? 'en/' . $this->getProductOptionAliasPrefix() . '/' : 'en/',
          '',
          trim($entity->toUrl()->toString(), '/')
        );

        break;
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
   * @param string $alias
   *   Facet alias.
   * @param string $value
   *   Encoded element value.
   *
   * @return string
   *   Raw element.
   */
  public function decodeFacetUrlComponents(string $alias, string $value) {
    if (is_numeric($value)) {
      return $value;
    }

    $static = &drupal_static(__FUNCTION__, []);
    if (isset($static[$value])) {
      return $static[$value];
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

        $decoded = $type == 'term' ? $entity->label() : $entity->get('field_acq_promotion_label')->getString();
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
    elseif (strpos($this->currentRequest->getPathInfo(), "/--") !== FALSE) {
      $alshaya_active_facet_filter_string = substr($this->currentRequest->getPathInfo(), strpos($this->currentRequest->getPathInfo(), "/--") + 3);
    }

    $alshaya_active_facet_filter_string = rtrim($alshaya_active_facet_filter_string, '/');

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

}
