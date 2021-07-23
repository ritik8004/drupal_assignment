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
