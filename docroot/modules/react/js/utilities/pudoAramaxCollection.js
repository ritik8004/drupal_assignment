/**
 * Helper to check if click and collect collection points feature is enabled.
 */
const collectionPointsEnabled = () => drupalSettings.cnc_collection_points_enabled || false;

export default collectionPointsEnabled;
