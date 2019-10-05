import qs from 'qs';
import { createBrowserHistory } from 'history';

const updateAfter = 700;
const history = createBrowserHistory();

function getCurrentSearchQueryString() {
  return qs.parse(window.location.hash.substr(1));
}

function getCurrentSearchQuery() {
  const parsedHash = getCurrentSearchQueryString();
  return parsedHash && parsedHash.query ? parsedHash.query : '';
}

// Push query to browser histroy to ga back and see previous results.
function updateSearchQuery(queryValue) {
  history.push({hash: queryValue});
}

export { getCurrentSearchQueryString, getCurrentSearchQuery, updateSearchQuery, updateAfter }
