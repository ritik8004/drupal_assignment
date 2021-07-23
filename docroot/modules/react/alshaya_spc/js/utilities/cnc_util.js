/**
 * Helper to check if click and collect collection points is enabled.
 */
export const collectionPointsEnabled = () => drupalSettings.cnc_collection_points_enabled || false;

/**
 * Helper to get click and collect section title.
 */
export const getCncSectionTitle = () => ((collectionPointsEnabled() === true)
  ? Drupal.t('Collection Point')
  : Drupal.t('Collection Store'));

/**
 * Helper to get click and collect section description.
 */
export const getCncSectionDescription = () => ((collectionPointsEnabled() === true)
  ? Drupal.t('select your preferred collection point')
  : Drupal.t('select your preferred collection store'));

/**
 * Helper to get click and collect modal title.
 */
export const getCncModalTitle = () => ((collectionPointsEnabled() === true)
  ? 'cnc_collection_point'
  : 'cnc_collection_store');

/**
 * Helper to get click and collect modal description.
 */
export const getCncModalDescription = () => ((collectionPointsEnabled() === true)
  ? 'cnc_find_your_collection_point'
  : 'cnc_find_your_nearest_store');

/**
 * Helper to get select button text in modal.
 */
export const getCncModalButtonText = () => ((collectionPointsEnabled() === true)
  ? 'cnc_select'
  : 'cnc_select_this_store');
