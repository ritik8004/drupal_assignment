<?php

namespace Drupal\alshaya_js_optimisations;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Asset\LibraryDependencyResolver;

/**
 * Class Alshaya JS Optimisation Config.
 */
class JsOptimisationsConfig {

  /**
   * The Depedency resolver.
   *
   * @var \Drupal\Core\Asset\LibraryDependencyResolver
   */
  protected $depedencyResolver;

  /**
   * Constructs a JS optimisation config object.
   *
   * @param \Drupal\Core\Asset\LibraryDependencyResolver $depedency_resolver
   *   Dependency resolver methods.
   */
  public function __construct(LibraryDependencyResolver $depedency_resolver) {
    $this->depedencyResolver = $depedency_resolver;
  }

  /**
   * Defining a static variable with the js categories.
   *
   * @var jsCategory
   */
  public static $jsCategory = [
    'ie_only' => [
      'data-group' => 'ie-only',
      'weight' => -30,
      'priorities' => [],
    ],
    'site_libraries' => [
      'data-group' => 'site-library',
      'weight' => -29,
      'priorities' => [
        'ie-only',
      ],
    ],
    'critical' => [
      'data-group' => 'critical',
      'weight' => -26,
      'priorities' => [
        'site-library',
        'ie-only',
      ],
    ],
    'sitewide_1' => [
      'data-group' => 'sitewide-1',
      'weight' => -25,
      'priorities' => [
        'site-library',
        'ie-only',
      ],
    ],
    'sitewide_2' => [
      'data-group' => 'sitewide-2',
      'weight' => -24,
      'priorities' => [
        'site-library',
        'ie-only',
        'sitewide-1',
      ],
    ],
  ];

  /**
   * Patterns the library with groups and assigning weights.
   */
  public function resolveCategories($critical_js = []) {
    $js_category = self::$jsCategory;
    $categories = array_keys($js_category);
    $resolved_data = [];
    foreach ($categories as $category) {
      if (isset($critical_js[$category])) {
        $lists = Yaml::decode($critical_js[$category]);
        if ($lists) {
          $lists = $this->includeDependencies($lists, $category);
          foreach ($lists as $library_name) {
            $set_weight = NULL;
            $library_name = explode('/', $library_name);
            $extension = $library_name[0];
            $library = $library_name[1];
            $data_group = [];
            if (isset($resolved_data[$extension]) && isset($resolved_data[$extension][$library]) && isset($resolved_data[$extension][$library]['data-group'])) {
              $data_group = $resolved_data[$extension][$library]['data-group'];
            }
            if (!array_intersect($js_category[$category]['priorities'], $data_group)) {
              switch ($category) {
                case 'sitewide_1':
                  if (in_array('critical', $data_group)) {
                    $set_weight = -28;
                  }
                  break;

                case 'sitewide_2':
                  if (in_array('critical', $data_group)) {
                    $set_weight = -27;
                  }
                  break;
              }
              if (!in_array($js_category[$category]['data-group'], $data_group)) {
                $resolved_data[$extension][$library]['data-group'][] = $js_category[$category]['data-group'];
                $resolved_data[$extension][$library]['weight'] = $set_weight ? $set_weight : $js_category[$category]['weight'];
              }
            }
          }
        }
      }
    }
    return $resolved_data;
  }

  /**
   * Method to prefix library name with extension.
   */
  public function prefixLibrary(array $items, $prefix) {
    return array_map(function ($item) use ($prefix) {
      return $prefix . $item;
    }, $items);
  }

  /**
   * Includes depedencies too if it is mentioned categories.
   */
  public function includeDependencies(array $lists, $category) {
    $included_dependecies = [];
    foreach ($lists as $extension => $libraries) {
      $libraries = (array) $libraries;
      $libraries = $this->prefixLibrary($libraries, $extension . '/');
      if ($category === 'critical' || $category === 'sitewide_1' || $category === 'sitewide_2') {
        $included_dependecies = array_merge($included_dependecies, $this->depedencyResolver->getLibrariesWithDependencies($libraries));
      }
      else {
        $included_dependecies = array_merge($included_dependecies, $libraries);
      }
    }
    return $included_dependecies;
  }

}
