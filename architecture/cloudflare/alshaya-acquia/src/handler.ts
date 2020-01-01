import { KVNamespace } from '@cloudflare/workers-types'
const CryptoJS = require('crypto-js');

export async function handleRequest(request: Request): Promise<Response> {
  var authKey = await AlshayaAcquiaStability.get('x-alshaya-key');
  var slackUrl = await AlshayaAcquiaStability.get('slackUrl');

  var slackOptions = {
    method: 'POST',
    body: '',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    }
  };

  if (request.headers.get('x-alshaya-key') !== authKey) {
    slackOptions.body = 'payload={"text": "Cloudflare worker url invoked with invalid auth key."}';
    var slackResponse = await fetch(slackUrl, slackOptions);

    return new Response('Invalid request.');
  }

  var stack = request.headers.get('x-alshaya-stack');
  var status = 'TRUE';
  if (request.headers.get('x-queue-status')) {
    status = request.headers.get('x-queue-status').toString();
  }

  slackOptions.body = 'payload={"text": "Cloudflare worker url invoked with status=' + status + '; for stack=' + stack + '"}';
  var slackResponse = await fetch(slackUrl, slackOptions);

  if (stack === undefined || stack === null) {
    return new Response('Please specify stack id.');
  }

  function guid() {
    function s4() {
      return Math.floor((1 + Math.random()) * 0x10000)
        .toString(16)
        .substring(1);
    }

    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
      s4() + '-' + s4() + s4() + s4();
  }

  function epochTime() {
    var d = new Date();
    var t = d.getTime();
    var o = t + "";
    return o.substring(0, 10);
  }

  var hmacId = await AlshayaAcquiaStability.get('hmacId');
  var secret = await AlshayaAcquiaStability.get('secret'); //not base64encoded
  var hmacRealm = "Acquia";
  var hmacVersion = "2.0";
  var acqHmacContentType = "application/json";
  var partsMock;
  var signature;
  var headerString;

  var siteIds = (await AlshayaAcquiaStability.get('siteIdsStack' + stack)).split(',');
  for (var siteId in siteIds) {
    siteId = siteIds[siteId];

    var nonce = guid();
    var acqHmacTimestamp = epochTime();

    partsMock = [
      'GET',
      'api.eu-west-1.prod.acm.acquia.io',
      '/v2/config/site/' + siteId + '/queue',
      'pause=' + status,
      'id=' + hmacId + '&nonce=' + nonce + '&realm=' + encodeURIComponent(hmacRealm) + '&version=' + hmacVersion
    ];

    partsMock.push(acqHmacTimestamp);

    // If request has body, know there is a content-length header added later, and include a hash of the body in the HMAC plaintext.
    signature = CryptoJS.HmacSHA256(partsMock.join("\n"), secret).toString(CryptoJS.enc.Base64);

    // TARGET header string
    // acquia-http-hmac realm="Acquia",id="key",nonce="7d5e7dbd-2deb-4939-b45b-09b2777dca08",version="2.0",headers="",signature="s2I3e/w6PPsanfMxaW2MCJvMF3bbFTM5BbDdLXSR55c="
    headerString = "acquia-http-hmac realm=\"" + hmacRealm + "\",id=\"" + hmacId + "\",nonce=\"" + nonce + "\",version=\"" + hmacVersion + "\",headers=\"\",signature=\"" + signature + "\"";

    var url = 'https://api.eu-west-1.prod.acm.acquia.io/v2/config/site/' + siteId + '/queue?pause=' + status;

    var options = {
      headers: {
        'X-Authorization-Timestamp': acqHmacTimestamp,
        'X-Authorization-Content-SHA256': '',
        'Authorization': headerString,
        'Content-Type': acqHmacContentType,
        'Accept': acqHmacContentType
      }
    };

    var response = await fetch(url, options);

    slackOptions.body = 'payload={"text": "stack=' + stack + '; siteid=' + siteId + '; response= ' + (await response.text()).replace(/["']/g, "") + '"}';
    slackResponse = await fetch(slackUrl, slackOptions);
  }

  return new Response('Request processed.');
}
