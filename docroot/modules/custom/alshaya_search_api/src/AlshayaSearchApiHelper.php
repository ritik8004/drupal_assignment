<?php

namespace Drupal\alshaya_search_api;

use Drupal\acq_sku\ProductOptionsManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class AlshayaSearchApiHelper.
 */
class AlshayaSearchApiHelper {

  /**
   * Term Storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaSearchApiHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->moduleHandler = $module_handler;
  }

  /**
   * Cleanup query params.
   *
   * @param array $query_params
   *   Query Params from view.
   *
   * @return array
   *   Cleaned query params.
   */
  public function getCleanQueryParams(array $query_params): array {
    unset($query_params['pager_query_method']);
    unset($query_params['sort_by']);
    unset($query_params['sort_order']);

    return $query_params;
  }

  /**
   * Change language of params to other language.
   *
   * @param string $langcode
   *   Target Language Code.
   * @param array $query_params
   *   Query params from view.
   *
   * @return array
   *   Processed query params.
   */
  public function getParamsInOtherLanguage(string $langcode, array $query_params): array {
    foreach ($query_params['f'] ?? [] as $key => $param) {
      $data = explode(':', $param);
      if (!is_string($data[1])) {
        continue;
      }

      $code = str_replace('plp_', '', $data[0]);
      $code = str_replace('promo_', '', $code);

      $query = $this->termStorage->getQuery();
      $query->condition('field_sku_attribute_code', $code);
      $query->condition('vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY);
      $query->condition('name', $data[1]);
      $tids = $query->execute();

      if (empty($tids)) {
        continue;
      }

      $tid = reset($tids);
      $term = $this->termStorage->load($tid);

      if ($term->hasTranslation($langcode)) {
        $term = $term->getTranslation($langcode);
      }

      $query_params['f'][$key] = $data[0] . ':' . $term->label();
    }

    // Call hook alter for other parameters.
    $this->moduleHandler->alter('alshaya_search_api_language_switcher', $query_params, $langcode);

    return $query_params;
  }

}
