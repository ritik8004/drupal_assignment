<?php

namespace Drupal\acq_sku;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Component\Serialization\Json;

/**
 * Class Api Helper.
 */
class ApiHelper {

  /**
   * Alshaya API Wrapper service object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * The class constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Alshaya API Wrapper service object.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper
  ) {
    $this->apiWrapper = $api_wrapper;
  }

  /**
   * Calls Magento API to fetch categories.
   *
   * @param string $langcode
   *   The langcode.
   *
   * @return array
   *   The processed categories data.
   */
  public function getCategories(string $langcode) {
    $this->apiWrapper->updateStoreContext($langcode);
    // Call Magento categories API.
    $categories_data = $this->apiWrapper->invokeApi(
      'categories/extended',
      [],
      'GET'
    );

    $categories_data = Json::decode($categories_data);
    $categories_data = $this->getMappedCategoryData($categories_data);

    return $categories_data;
  }

  /**
   * Map the categories to the format which Drupal already accepts.
   *
   * @param array $category
   *   The category data from Magento API response.
   *
   * @return array
   *   The mapped category array.
   */
  private function getMappedCategoryData(array $category = []) {
    if (empty($category)) {
      return $category;
    }

    $children = $category['children_data'];
    $children_data = [];
    foreach ($children as $child) {
      $children_data[] = $this->getMappedCategoryData($child);
    }

    return [
      'category_id' => (int) ($category['id'] ?? 0),
      'parent_id'   => (int) ($category['parent_id'] ?? 0),
      'store_id'    => (int) ($category['store_id'] ?? 0),
      'name'        => (string) ($category['name'] ?? ''),
      'description' => (string) ($category['description'] ?? ''),
      'position'    => (int) ($category['position'] ?? 0),
      'level'       => (int) ($category['level'] ?? 0),
      'in_menu'     => (bool) ($category['include_in_menu'] ?? TRUE),
      'is_active'   => (bool) ($category['is_active'] ?? TRUE),
      'product_ids' => [],
      'children'    => $children_data,
      'extension'   => [],
    ];
  }

}
