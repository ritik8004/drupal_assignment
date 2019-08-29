<?php

namespace Drupal\alshaya_facets_pretty_paths;

use Drupal\acq_sku\ProductOptionsManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\facets\FacetManager\DefaultFacetManager;
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
   */
  public function __construct(RouteMatchInterface $route_match,
                              RequestStack $request_stack,
                              EntityTypeManagerInterface $entity_type_manager,
                              LanguageManagerInterface $language_manager,
                              AliasManagerInterface $alias_manager,
                              DefaultFacetManager $facets_manager) {
    $this->routeMatch = $route_match;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
    $this->facetManager = $facets_manager;
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

    $query = $this->termStorage->getQuery();
    $query->condition('name', $value);
    $query->condition('field_sku_attribute_code', $attribute_code);
    $query->condition('vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY);
    $tids = $query->execute();
    foreach ($tids ?? [] as $tid) {
      $term = $this->termStorage->load($tid);
      if ($term instanceof TermInterface) {
        if ($term->language()->getId() != 'en' && $term->hasTranslation('en')) {
          $term = $term->getTranslation('en');
        }

        $encoded = str_replace('en/', '', trim($term->toUrl()->toString(), '/'));
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
   * @param string $value
   *   Encoded element value.
   *
   * @return string
   *   Raw element.
   */
  public function decodeFacetUrlComponents(string $value) {
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

    $tid = str_replace('/taxonomy/term/', '', $this->aliasManager->getPathByAlias('/' . $decoded, 'en'));
    if ($tid) {
      $term = $this->termStorage->load($tid);

      if ($term instanceof TermInterface) {
        $langcode = $this->languageManager->getCurrentLanguage()->getId();
        if ($term->language()->getId() != $langcode && $term->hasTranslation($langcode)) {
          $term = $term->getTranslation($langcode);
        }

        $decoded = $term->label();
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
   * Change language of params to other language.
   *
   * @param string $attribute_code
   *   Target Language Code.
   * @param string $filter_value
   *   Target Language Code.
   * @param bool $default
   *   Whether to translate to default langague or not.
   *
   * @return string
   *   Processed query params.
   */
  public function getTranslatedFilters(string $attribute_code, string $filter_value, bool $default = TRUE) {
    $current_langcode = $this->languageManager->getCurrentLanguage()->getId();
    if ($current_langcode !== 'en') {
      $translated_filter_values = &drupal_static(__FUNCTION__, []);
      $required_langcode = $default ? 'en' : $current_langcode;
      if (isset($translated_filter_values[$filter_value][$required_langcode])) {
        return $translated_filter_values[$filter_value][$required_langcode];
      }
      $attribute_code = str_replace('plp_', '', $attribute_code);
      $attribute_code = str_replace('promo_', '', $attribute_code);
      $query = $this->termStorage->getQuery();
      $query->condition('field_sku_attribute_code', $attribute_code);
      $query->condition('vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY);
      $query->condition('name', $filter_value);
      $tids = $query->execute();
      if (!empty($tids)) {
        $tid = reset($tids);
        $term = $this->termStorage->load($tid);
        if ($term instanceof TermInterface && $term->hasTranslation($required_langcode)) {
          $term = $term->getTranslation($required_langcode);
          $translated_filter_values[$filter_value][$required_langcode] = $term->label();
          return $term->label();
        }
      }
    }
    return $filter_value;
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

}
