/**
 * Helper to check if click and collect collection points is enabled.
 */
export const collectionPointsEnabled = () => drupalSettings.cnc_collection_points_enabled || false;

/**
 * Helper to get click and collect store list icon.
 */
export const getCncStoreIcon = () => drupalSettings.cnc_store_icon || '';

/**
 * Helper to get click and collect store map icon.
 */
export const getCncStoreMapIcon = () => drupalSettings.cnc_store_map_icon || '';

/**
 * Helper to get click and collect store list icon.
 */
export const getCncCollectionPointIcon = () => drupalSettings.cnc_collection_point_icon || '';

/**
 * Helper to get click and collect store list icon.
 */
export const getCncCollectionPointMapIcon = () => drupalSettings.cnc_collection_point_map_icon || '';

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

/**
 * Helper to check if given store is a aramex/pudo collection point.
 */
export const isCollectionPoint = (type) => ((type === 'collection_point'));

/**
 * Helper to get cnc list icon.
 */
export const getCncListIcon = (type) => {
  if (collectionPointsEnabled() !== true) {
    return '';
  }

  const icon = isCollectionPoint(type) ? getCncCollectionPointIcon() : getCncStoreIcon();

  return icon;
};
