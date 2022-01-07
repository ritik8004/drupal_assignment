<?php

namespace Drupal\alshaya_js_optimisations;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Asset\LibraryDependencyResolver;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class Alshaya JS Optimisation Helper.
 */
class AlshayaJsOptimisationHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Depedency resolver.
   *
   * @var \Drupal\Core\Asset\LibraryDependencyResolver
   */
  protected $depedencyResolver;

  /**
   * Constructs a JS optimisation helper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Asset\LibraryDependencyResolver $depedency_resolver
   *   Dependency resolver methods.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LibraryDependencyResolver $depedency_resolver) {
    $this->configFactory = $config_factory;
    $this->depedencyResolver = $depedency_resolver;
  }

  /**
   * Config for JS Categories.
   *
   * @var jsCategories
   */
  public static $jsCategories = [
    'ie_only' => [
      'weight' => -30,
      'attributes' => [
        'nomodule' => TRUE,
        'data-group' => ['ie-only'],
      ],
      'dependencies' => [],
    ],
    'site_libraries' => [
      'weight' => -29,
      'attributes' => [
        'data-group' => ['site-library'],
      ],
      'dependencies' => [
        'ie-only',
      ],
    ],
    'critical' => [
      'weight' => -26,
      'attributes' => [
        'data-group' => ['critical'],
      ],
      'dependencies' => [
        'site-library',
        'ie-only',
      ],
    ],
    'sitewide_1' => [
      'weight' => -25,
      'attributes' => [
        'data-group' => ['sitewide-1'],
      ],
      'dependencies' => [
        'site-library',
        'ie-only',
      ],
    ],
    'sitewide_2' => [
      'weight' => -24,
      'attributes' => [
        'data-group' => ['sitewide-2'],
      ],
      'dependencies' => [
        'site-library',
        'ie-only',
        'sitewide-1',
      ],
    ],
  ];

  /**
   * Text config for JS Optimisations.
   */
  public static function getJsOptimisationTextConfig() {
    return [
      'uglification' => [
        'label' => t('Enable JS Uglification'),
        'description' => t('Uglification removes whitespaces, minfies variables and function names on the scripts. This will reduce the script size and its download size by a small percentage.'),
      ],
      'critical_js' => [
        'label' => t('Critical JS and Categorisation settings'),
        'description' => t('Critical JS functionality helps to prioritise loading of important scripts. Other non critical scripts will be deprioritised and loaded asynchronously. JS categorisation helps to aggregate related scripts together. Please configure this settings properly as the wrong settings can break the application and/or give no performance gains.'),

        'status_label' => t('Enable Critical JS and Categorisation'),
        'ie_only' => [
          'label' => t('IE Only Scripts'),
          'description' => t('JS asset library polyfills used only to provide compatibility with IE.'),
        ],
        'site_libraries' => [
          'label' => t('Site Libraries'),
          'description' => t('Priority library scripts loaded for all pages throughout the site.'),
        ],
        'critical' => [
          'label' => t('Critical Scripts'),
          'description' => t('JS asset libraries to load with high priority on pageload. They render content and interactions above the fold, that are immediately visible on pageload. They are also responsibe for any critical and highlighting functionality on a page.'),
        ],
        'sitewide_1' => [
          'label' => t('Sitewide Scripts 1'),
          'description' => t('JS asset libraries used on all pages throughout the site. These are libraries and initialisation codes that are required by all pages.'),
        ],
        'sitewide_2' => [
          'label' => t('Sitewide Scripts 2'),
          'description' => t('JS asset libraries used on most pages of the site. These are libraries and initialisation codes that are required by most pages.'),
        ],
      ],
      'form_warning' => t('WARNING: Saving this form will rebuild cache on JS files. The first load of site pages will be slower than usual.'),
    ];
  }

  /**
   * JS aggregation status flag.
   *
   * @var isJsAggregation
   */
  public static $isJsAggregation = FALSE;

  /**
   * Critical JS status flag.
   *
   * @var criticalJsEnabled
   */
  public static $criticalJsEnabled = NULL;

  /**
   * Resolved critical JS libraries.
   *
   * @var resolvedLibraries
   */
  public static $resolvedLibraries = [];

  /**
   * Fetch and save local copy of critical JS settings.
   */
  public function fetchCriticalJsSettings() {
    $critical_js = $this->configFactory->get('alshaya_js_optimisations.settings')->get('critical_js');
    if (!empty($critical_js)) {
      self::$criticalJsEnabled = $critical_js['status'];
      self::$resolvedLibraries = Yaml::decode($critical_js['processed_libraries']);
    }
  }

  /**
   * Resolve dependencies, assign weights and attributes.
   */
  public function resolveCategories($critical_js = []) {
    $js_category = self::$jsCategories;
    $categories = array_keys($js_category);
    $resolved_data = [];
    foreach ($categories as $category) {
      if (empty($critical_js[$category])) {
        continue;
      }

      $lists = Yaml::decode($critical_js[$category]);
      if (empty($lists)) {
        continue;
      }

      $lists = $this->includeDependencies($lists, $category);

      // Assign configured attributes and weights to libraries.
      foreach ($lists as $library_name) {
        $set_weight = NULL;
        $defer = FALSE;
        $library_name = explode('/', $library_name);
        $extension = $library_name[0];
        $library = $library_name[1];
        $data_group = [];

        if (
          isset($resolved_data[$extension]) &&
          isset($resolved_data[$extension][$library]) &&
          isset($resolved_data[$extension][$library]['attributes']) &&
          isset($resolved_data[$extension][$library]['attributes']['data-group'])
        ) {
          $data_group = $resolved_data[$extension][$library]['attributes']['data-group'];
        }

        if (!array_intersect($js_category[$category]['dependencies'], $data_group)) {
          if (in_array('critical', $data_group)) {
            $delta = 0;
            if ($category === 'sitewide_1') {
              $delta = 2;
            }
            elseif ($category === 'sitewide_2') {
              $delta = 1;
            }

            // New weight for scripts that are critical and sitewide.
            $set_weight = $js_category['critical']['weight'] - $delta;
          }
          elseif ($category === 'sitewide_1' || $category === 'sitewide_2') {
            // Flag for non critical sitewide scripts.
            $defer = TRUE;
          }

          if (!in_array($js_category[$category]['attributes']['data-group'][0], $data_group)) {
            if (empty($resolved_data[$extension])) {
              $resolved_data[$extension] = [];
            }

            if (empty($resolved_data[$extension][$library])) {
              $resolved_data[$extension][$library] = [];
            }

            if (empty($resolved_data[$extension][$library]['attributes'])) {
              $resolved_data[$extension][$library]['attributes'] = [];
            }

            $resolved_data[$extension][$library]['attributes'] = array_merge_recursive(
              $resolved_data[$extension][$library]['attributes'],
              $js_category[$category]['attributes']
            );
            $resolved_data[$extension][$library]['weight'] = $set_weight ? $set_weight : $js_category[$category]['weight'];

            // Preserve existing 'defer' on scripts.
            if ($defer) {
              // Add 'defer' on non critical sitewide scripts.
              $resolved_data[$extension][$library]['attributes']['defer'] = TRUE;
            }
          }
        }
      }
    }

    return $resolved_data;
  }

  /**
   * Prefix library name with extension.
   */
  private function prefixLibrary(array $items, $prefix) {
    return array_map(function ($item) use ($prefix) {
      return $prefix . $item;
    }, $items);
  }

  /**
   * Includes depedencies to categories.
   */
  private function includeDependencies(array $lists, $category) {
    $included_dependecies = [];
    foreach ($lists as $extension => $libraries) {
      $libraries = (array) $libraries;
      $libraries = $this->prefixLibrary($libraries, $extension . '/');
      $library_list = $libraries;

      if ($category === 'critical' || $category === 'sitewide_1' || $category === 'sitewide_2') {
        $library_list = $this->depedencyResolver->getLibrariesWithDependencies($libraries);
      }

      $included_dependecies = array_merge($included_dependecies, $library_list);
    }
    return $included_dependecies;
  }

}
