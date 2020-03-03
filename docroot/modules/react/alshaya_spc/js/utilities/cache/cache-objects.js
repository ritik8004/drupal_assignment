import CncSearch from './cnc';

const tempCacheObjects = {};
/**
 * Helper function to initiate only single object
 * for any given promise function name.
 */
export const createCacheObject = (func) => {
  if (typeof tempCacheObjects[func.name] === 'undefined') {
    tempCacheObjects[func.name] = new CncSearch();
  }
  return tempCacheObjects[func.name];
};
