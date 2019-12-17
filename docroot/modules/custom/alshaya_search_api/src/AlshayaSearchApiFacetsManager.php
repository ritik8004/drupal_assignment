<?php

namespace Drupal\alshaya_search_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\block\BlockInterface;
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
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * The Theme Manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  private $themeManager;

  /**
   * Facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  private $facetManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * AlshayaSearchApiFacetsManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Config Factory service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The Theme Manager service.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facets_manager
   *   Facet manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager,
                              ThemeManagerInterface $theme_manager,
                              DefaultFacetManager $facets_manager,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->themeManager = $theme_manager;
    $this->facetManager = $facets_manager;
    $this->entityTypeManager = $entity_type_manager;
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
    $source = $this->configFactory->getEditable($facet_source_id);

    $id = $prefix ? $prefix . '_' . $field_key : $field_key;
    $facet_id = 'facets.facet.' . $id;

    $data = $this->getFromTemplate($template_id);

    if (empty($data)) {
      return;
    }

    $data['id'] = $id;
    $data['facet_source_id'] = $facet_source_id;
    $data['field_identifier'] = $data['field_identifier'] ?? 'attr_' . $field_key;
    $data = array_replace_recursive($data, $overrides);
    $data['url_alias'] = strtolower(str_replace(' ', '_', $data['name']));
    if ($source->get('url_processor') != 'alshaya_facets_pretty_paths') {
      $data['url_alias'] = $field_key;
    }
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

    if (empty($block_data)) {
      return;
    }

    $block_data['id'] = $formatted_id;
    $block_data['theme'] = $this->themeManager->getActiveTheme()->getName();
    $block_data['plugin'] = 'facet_block:' . $id;
    $block_data['settings']['id'] = $block_data['plugin'];
    $block_data['settings']['label'] = $data['name'];

    $this->configFactory->getEditable($block_id)->setData($block_data)->save();

    // Translate facet block titles.
    foreach ($this->getNonDefaultLanguageCodes() ?? [] as $langcode) {
      $config = $this->languageManager->getLanguageConfigOverride($langcode, $block_id);
      $settings = [];
      // @codingStandardsIgnoreLine
      $settings['label'] = t($data['name'], [], ['langcode' => $langcode]);
      $config->set('settings', $settings);
      $config->save();
    }
  }

  /**
   * Get non default language codes.
   *
   * This will return [ar] for most of our cases.
   *
   * @return array
   *   Non-default language codes.
   */
  private function getNonDefaultLanguageCodes() {
    static $langcodes;

    if (empty($langcodes)) {
      $languages = $this->languageManager->getLanguages();
      unset($languages[$this->languageManager->getDefaultLanguage()->getId()]);
      $langcodes = array_keys($languages);
    }

    return $langcodes;
  }

  /**
   * Remove facet, block and filter bar entry for a field.
   *
   * @param string $field_key
   *   Field code, without attr_.
   * @param string $filter_bar_id
   *   Filter bar config id.
   * @param string $prefix
   *   Prefix to use blank, plp, promo.
   */
  public function removeFacet($field_key, $filter_bar_id, $prefix = '') {
    $id = $prefix ? $prefix . '_' . $field_key : $field_key;

    $facet_id = 'facets.facet.' . $id;
    $facet = $this->configFactory->getEditable($facet_id);
    if ($facet) {
      $facet->delete();
    }

    $formatted_id = str_replace('_', '', $id);
    $block_id = 'block.block.' . $formatted_id;
    $block = $this->configFactory->getEditable($block_id);
    if ($block) {
      $block->delete();
    }

    // Update the filter bar (summary).
    $filter_bar = $this->configFactory->getEditable($filter_bar_id);
    $facets = $filter_bar->get('facets');

    if (isset($facets[$id])) {
      unset($facets[$id]);
      $filter_bar->set('facets', $facets);
      $filter_bar->save();
    }
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
    $content = '';

    if (file_exists($path . $template_id . '.yml')) {
      $content = file_get_contents($path . $template_id . '.yml');
    }
    elseif (strpos($template_id, 'facets.facet') === 0) {
      $content = file_get_contents($path . 'facets.facet.default.yml');
    }

    return Yaml::parse($content);
  }

  /**
   * Get the block list of all facets of a given source.
   *
   * @param string $facet_source
   *   Facet source.
   *
   * @return array
   *   Block arrays.
   */
  public function getBlocksForFacets(string $facet_source) {
    // Get all facets of the given source.
    $facets = $this->facetManager->getFacetsByFacetSourceId($facet_source);
    $blocks = $block_ids = [];
    if (!empty($facets)) {
      foreach ($facets as $facet) {
        $block_ids[] = str_replace('_', '', $facet->id());
      }

      if (!empty($block_ids)) {
        /* @var \Drupal\block\Entity\Block[] $block*/
        $blocks_list = $this->entityTypeManager->getStorage('block')->loadMultiple($block_ids);
        // Sort the blocks.
        uasort($blocks_list, [$this, 'sortBlocksByWeight']);
        foreach ($blocks_list as $block) {
          // If block is enabled.
          if ($block instanceof BlockInterface && $block->status() && $block->access('view')) {
            $blocks[] = $this->entityTypeManager->getViewBuilder('block')->view($block);
          }
        }
      }
    }

    return $blocks;
  }

  /**
   * Sorts array of block objects by object weight property.
   *
   * @param \Drupal\block\BlockInterface $a
   *   A facet.
   * @param \Drupal\block\BlockInterface $b
   *   A facet.
   *
   * @return int
   *   Sort value.
   */
  public function sortBlocksByWeight(BlockInterface $a, BlockInterface $b) {
    $a_weight = $a->getWeight();
    $b_weight = $b->getWeight();

    if ($a_weight == $b_weight) {
      return 0;
    }

    return ($a_weight < $b_weight) ? -1 : 1;
  }

}
