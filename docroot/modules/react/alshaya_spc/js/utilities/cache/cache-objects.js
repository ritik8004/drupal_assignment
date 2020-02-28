import CncSearch from "./cnc";

window.cacheInstances = {};
/**
 * Helper function to initiate only single object
 * for any given promise function name.
 */
export const createCacheObject = (func) => {
  if (typeof window.cacheInstances[func.name] === 'undefined') {
    window.cacheInstances[func.name] = new CncSearch();
  }
  return window.cacheInstances[func.name];
}
