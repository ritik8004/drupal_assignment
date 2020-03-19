/**
 * Returns the user's market
 * @param {Request} request
 */
async function handleRequest(request) {
  // The `cf-ipcountry` header is not supported in the previewer
  var country = request.headers.get('cf-ipcountry')

  if (undefined === country) {
    country = 'xx';
  }

  const someJSON = {
    result: [country.toLowerCase()],
  }

  const init = {
    headers: {
      'content-type': 'application/json;charset=UTF-8',
    },
  }

  var response = new Response(JSON.stringify(someJSON), init)
  // We'll be calling this code from Javascript, so allow all origins
  // in order to avoid being blocked by CORS policy.
  response.headers.set('Access-Control-Allow-Origin', '*')

  return response
}
addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request))
})

