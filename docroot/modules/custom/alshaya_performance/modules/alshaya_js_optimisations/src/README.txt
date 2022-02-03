Changes to 'core' service classes in 'alshaya_js_optimisations' services.

Class 1:
  Requirement: To enable Drupal to aggregate scripts with attributes.

  Core class: `docroot/core/lib/Drupal/Core/Asset/JsCollectionGrouper.php`
  Copied to ==>> `docroot/modules/custom/alshaya_performance/modules/alshaya_js_optimisations/src/Asset/PerformanceJsCollectionGrouper.php`

  Changes:
    In the method: `public function group(array $js_assets)`
      To the array `$group_keys` added `$item['attributes']`
    Note: This change will enable Drupal to aggregate scripts with attributes (Refer alshaya_js_optimisations.module).
