<?php

namespace Drupal\alshaya_search_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AlshayaSearchApiFacetsManager.
 *
 * @package Drupal\alshaya_search_api
 */
class AlshayaSearchApiFacetsManager {

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The Theme Manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  private $themeManager;

  /**
   * AlshayaSearchApiFacetsManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Config Factory service.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The Theme Manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              ThemeManagerInterface $theme_manager) {
    $this->configFactory = $config_factory;
    $this->themeManager = $theme_manager;
  }

  /**
   * Create facet for a newly added SKU Base field.
   *
   * @param string $field_key
   *   Field code, without attr_.
   * @param string $facet_source_id
   *   Facet source id.
   * @param string $filter_bar_id
   *   Filter bar config id.
   * @param string $prefix
   *   Prefix to use blank, plp, promo.
   * @param array $overrides
   *   Overrides if any.
   */
  public function createFacet($field_key, $facet_source_id, $filter_bar_id, $prefix = '', array $overrides = []) {
    $template_id = 'facets.facet.' . $field_key;

    $id = $prefix ? $prefix . '_' . $field_key : $field_key;
    $facet_id = 'facets.facet.' . $id;

    $data = $this->getFromTemplate($template_id);

    if (empty($data)) {
      return;
    }

    $data['id'] = $id;
    $data['facet_source_id'] = $facet_source_id;
    $data['url_alias'] = $id;
    $data = array_replace_recursive($data, $overrides);
    $this->configFactory->getEditable($facet_id)->setData($data)->save();

    // Update the filter bar (summary).
    $filter_bar = $this->configFactory->getEditable($filter_bar_id);
    $facets = $filter_bar->get('facets');
    $facets[$id] = [
      'checked' => 1,
      'label' => $data['name'],
      'separator' => ', ',
      'show_count' => 1,
      'weight' => 0,
    ];
    $filter_bar->set('facets', $facets);
    $filter_bar->save();

    $template_id = $prefix
      ? 'block.block.' . $prefix . '_facet'
      : 'block.block.facet';

    // @see FacetBlockAjaxController::ajaxFacetBlockView().
    $formatted_id = str_replace('_', '', $id);
    $block_id = 'block.block.' . $formatted_id;

    $block_data = $this->getFromTemplate($template_id);
    $block_data['id'] = $formatted_id;
    $block_data['theme'] = $this->themeManager->getActiveTheme()->getName();
    $block_data['plugin'] = 'facet_block:' . $id;
    $block_data['settings']['id'] = $block_data['plugin'];
    $block_data['settings']['label'] = $prefix
      ? ucfirst($prefix) . ' ' . $data['name']
      : $data['name'];
    $this->configFactory->getEditable($block_id)->setData($block_data)->save();
  }

  /**
   * Helper function to get data from template.
   *
   * @param string $template_id
   *   Template config id to load yaml data from.
   *
   * @return mixed
   *   Parsed yaml data.
   */
  private function getFromTemplate($template_id) {
    $path = drupal_get_path('module', 'alshaya_search_api') . '/config/template/';
    $content = file_get_contents($path . $template_id . '.yml');
    return Yaml::parse($content);
  }

}
