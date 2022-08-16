<?php

/**
 * @file
 * Hooks specific to the alshaya profile.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the fields to query for the category API.
 *
 * @param array $fields
 *   Fields of the category query.
 */
function hook_alshaya_rcs_category_query_fields_alter(array &$fields) {
  $fields[] = 'description';
}

/**
 * @} End of "addtogroup hooks".
 */
