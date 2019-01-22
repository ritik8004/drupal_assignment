<?php

/**
 * @file
 * Hooks specific to the acq_sku_position module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allow other modules to take action after position sync finished.
 */
function hook_acq_sku_position_sync_finished() {

}

/**
 * Allow other modules to skip terms from position sync.
 */
function hook_acq_sku_position_sync_alter(array &$terms) {
  if (!empty($terms)) {
    unset($terms[0]);
  }
}

/**
 * @} End of "addtogroup hooks".
 */
