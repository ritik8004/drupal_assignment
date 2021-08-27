<?php

/**
 * @file
 * Hooks specific to the alshaya_performance module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allow all modules and themes to add items to preload.
 *
 * @param array $preload
 *   Items to preload.
 */
function hook_alshaya_performance_preload_alter(array &$preload) {
  $preload[] = [
    'as' => 'image',
    'href' => '/themes/custom/transac/alshaya_white_label/imgs/cards/card-visa.svg',
  ];
  $preload[] = [
    'as' => 'image',
    'href' => '/themes/custom/transac/alshaya_white_label/imgs/cards/card-mastercard.svg',
  ];
}

/**
 * @} End of "addtogroup hooks".
 */
