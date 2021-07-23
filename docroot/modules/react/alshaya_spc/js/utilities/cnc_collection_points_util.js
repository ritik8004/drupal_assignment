/* eslint-disable */
// @todo: Remove eslint disable once we add more utility methods to this file.

/**
 * Helper to check if click and collect collection points is enabled.
 */
export const cnc_collection_points_enabled = () => {
  const cncCollectionPointsEnabled = drupalSettings.cnc_collection_points_enabled || false;
  return cncCollectionPointsEnabled;
};
