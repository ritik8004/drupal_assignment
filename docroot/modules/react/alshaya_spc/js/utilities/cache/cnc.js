/**
 * Cache class for click n collect.
 *
 * To avoid unnecessary api request on repeated location
 * search.
 *
 * @todo: rename this class moving forward, so that we can
 * use this with createFetcher for GET requests.
 */
class CncSearch {
  constructor() {
    this.query = '';
    this.queryCount = 0;
    this.cache = {};
    this.cacheHits = 0;
    this.cacheHitsHistory = [];
  }

  getResults(query) {
    this.query = JSON.stringify(query);
    if (this.cache[this.query]) {
      this.cacheHits = this.cacheHits + 1;
      this.queryCount = this.queryCount + 1;
      this.cacheHitsHistory.concat(this.query);
      return this.cache[this.query];
    }
    return null;
  }

  cacheResult(results) {
    this.cache[this.query] = results;
    this.queryCount = this.queryCount + 1;
    return results;
  }
}

export default CncSearch;
