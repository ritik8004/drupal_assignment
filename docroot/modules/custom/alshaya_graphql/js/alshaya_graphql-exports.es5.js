/**
 * Make an AJAX call to Magento Graphql API.
 *
 * @param {string} query
 *   The query to send for graphql request.
 * @param {string} method
 *   The request method.
 * @param {object} variables
 *   The object to send for graphql request.
 *
 * @returns {Promise<AxiosPromise<object>>}
 *   Returns a promise object.
 */
exports.invokeGraphqlApi = async function (query, method = 'GET', variables = {}) {
  const headers = {
    'content-type': 'application/json',
    'Alshaya-Channel': 'web',
  };

  let mainApiResponse = null;
  // Remove extra enclosing {}.
  query = query.slice(1, -1);
  // Encode to valid uri format.
  query = encodeURIComponent(query);

  let graphqlQuery = `query=${query}`;
  if (typeof variables !== 'undefined') {
    variables = JSON.stringify(variables);
    graphqlQuery = `query=${query}&variables=${variables}`;
  }

  const mainApi = jQuery.ajax({
    url: drupalSettings.graphql.baseUrl + '/graphql',
    method: method,
    headers,
    data: graphqlQuery,
    success: function (response) {
      mainApiResponse = response;
    },
    error: function () {
    // If graphQl API is returning Error.
    Drupal.alshayaLogger('error', 'Failed to process the GraphQL query.');
    }
  });

  return jQuery.when(mainApi).then(
    function jQueryWhenThen() {
      return mainApiResponse;
    }
  )
};
