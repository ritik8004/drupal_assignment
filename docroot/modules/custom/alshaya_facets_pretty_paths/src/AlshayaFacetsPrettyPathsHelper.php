<?php

namespace Drupal\alshaya_facets_pretty_paths;

use Drupal\acq_sku\ProductOptionsManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
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
   */
  public function __construct(RouteMatchInterface $route_match,
                              RequestStack $request_stack,
                              EntityTypeManagerInterface $entity_type_manager,
                              LanguageManagerInterface $language_manager) {
    $this->routeMatch = $route_match;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->languageManager = $language_manager;
  }

  /**
   * Encode url components according to given rules.
   *
   * @param string $element
   *   Raw element value.
   *
   * @return string
   *   Encoded element.
   */
  public static function encodeFacetUrlComponents($element) {
    // Convert first letter to lowercase.
    $words = explode(' ', $element);
    $lc_word = '';
    foreach ($words as $word) {
      $word = ' ' . lcfirst($word);
      $lc_word .= $word;
    }
    $element = ltrim($lc_word);

    // Convert spaces to '_'.
    $element = str_replace(' ', '_', $element);

    // Convert - in the facet value to '__'.
    $element = str_replace('-', '__', $element);

    return $element;

  }

  /**
   * Decode url components according to given rules.
   *
   * @param string $element
   *   Encoded element value.
   *
   * @return string
   *   Raw element.
   */
  public static function decodeFacetUrlComponents($element) {
    // Convert __ in the facet value to '-'.
    $element = str_replace('__', '-', $element);

    // Convert _ to spaces.
    $element = str_replace('_', ' ', $element);

    // Capitalize first letter.
    $element = ucwords($element);

    return $element;
  }

  /**
   * Get active facets from request or route.
   *
   * @return array
   *   Filter array.
   */
  public function getActiveFacetFilters() {
    $alshaya_active_facet_filters = &drupal_static(__FUNCTION__, []);

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

}
