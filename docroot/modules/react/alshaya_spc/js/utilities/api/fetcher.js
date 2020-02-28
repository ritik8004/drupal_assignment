import {  } from "../cache/cnc";
import { createCacheObject } from "../cache/cache-objects";

/**
 * Helper function to handle api request for GET method for now.
 *
 * Created helper method to avoid error handling everytime we make
 * api requests. This general function will always be used to make
 * api request, and it handles caching, returns error correctly so
 * on usage does not have to do comoplicated condition check and
 * returns data in promise.
 *
 * @param {*} promiseFunc
 *   Promise function name.
 */
export const createFetcher = promiseFunc => {
  return {
    read: arg => {
      // Initiate cache and cache responses of stores to avoid
      // Duplicate api calls.
      // @todo: rename cache class and helper method to generalise
      // When we use this method accross the site.
      let cachedObj = createCacheObject(promiseFunc);
      let cachedResults = cachedObj.getResults(arg);
      if (!cachedResults) {
        try {
          return promiseFunc(arg)
            .then(response => {
              if (!response) {
                return {error: 'error!'};
              }

              if (typeof response.data !== 'object') {
                return {error: 'error!'};
              }

              if (!response.data.error && response.data.error) {
                console.error(cart_result.error_message);
                return {error: 'error!'};
              }

              cachedObj.cacheResult(response.data);
              return response.data;
            },
            reject => {
              return {error: reject};
            });
        }
        catch (error) {
          return new Promise(
            resolve => resolve({error: error})
          );
        }
      }
      // read: should always return promise, so that we don't have to
      // check at api call point if it's a promise or not.
      return new Promise(
        resolve => resolve(cachedResults)
      );
    }
  }
}
