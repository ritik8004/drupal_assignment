CORE service classes by replacing the service named in alshaya_js_optimisations.services.yml

Class 1:
    Requirement:
    To Prioritize Javascriptfiles by categorizing it and allocating weight to it
    Set data-group attribute to files, then aggregating based on the attributes

    Core class: docroot/core/lib/Drupal/Core/Asset/JsCollectionGrouper.php
    Copied to ==>> docroot/modules/custom/alshaya_performance/modules/alshaya_js_optimisations/src/Asset/PerformanceJsCollectionGrouper.php

    Changes:
        In the method:
            public function group(array $js_assets) {}
        To the array $group_keys added $item['attributes']
    Note: This addition value does the aggregation of js grouped with the data attributes (Refer alshaya_js_optimisations.module)
