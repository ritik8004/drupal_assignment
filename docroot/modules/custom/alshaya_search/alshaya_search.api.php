<?php

/**
 * @file
 * Hooks specific to the alshaya_search module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter query parameter of current search.
 *
 * @param array $query
 *   Array of query parameters.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   Current $form_sate.
 * @param object $redirect_lang
 *   Language object.
 */
function hook_alshaya_search_query_param_alter(array &$query, \Drupal\Core\Form\FormStateInterface $form_state, $redirect_lang) {

}

/**
 * @} End of "addtogroup hooks".
 */
