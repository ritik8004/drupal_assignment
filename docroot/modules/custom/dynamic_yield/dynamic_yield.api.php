<?php

/**
 * @file
 * Hooks specific to the dynamic_yield module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allow other modules to alter DY context locale.
 *
 * @param string $lng
 *   DY context locale.
 */
function hook_dynamic_yield_context_alter(&$lng) {
  $lng = 'en_US';
}
