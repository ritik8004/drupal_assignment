/* eslint-disable */
// @todo: Will remove eslint disable once we add more utility methods to this file.

/**
 * Helper to check if click and collect collection points is enabled.
 */
export const collectionPointsEnabled = () => drupalSettings.cnc_collection_points_enabled || false;
