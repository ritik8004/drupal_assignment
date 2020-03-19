/**
 * Returns the user's market
 * @param {Request} request
 */
async function handleRequest(request) {
  // The `cf-ipcountry` header is not supported in the previewer
  const country = request.headers.get('cf-ipcountry')
  var market = countryMap[country]

  if (undefined === market) {
    market = 'xx';
  }

  const someJSON = {
    result: [market],
  }

  const init = {
    headers: {
      'content-type': 'application/json;charset=UTF-8',
    },
  }

  var response = new Response(JSON.stringify(someJSON), init)
  response.headers.set('Access-Control-Allow-Origin', '*')

  return response
}
addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request))
})
/**
 * A map of the country to market
 * @param {Object} countryMap
 */
const countryMap = {
  "KW": 'kw',
  "SA" : "sa",
  "AE"  : "ae",
  "EG" : "eg",
}
