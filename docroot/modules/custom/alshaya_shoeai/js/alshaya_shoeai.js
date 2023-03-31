/**
 * @file
 * Contains Alshaya ShoeAI functionality.
 */
(function (drupalSettings) {  
  let shoeAi = drupalSettings.shoeai;
  if (shoeAiStatus(shoeAi)) {
    let language = drupalSettings.path.currentLanguage;
    let script = document.createElement('script');
    script.src = 'https://shoesize.me/assets/plugin/loader.js';
    script.type = 'text/javascript';
    script.async = true;  
    script.text = '{shopID:"' + shoeAi.shopId + '", locale: "' +
     language + '", scale: "eu", zeroHash: "' + shoeAi.zeroHash + '"}';
    document.body.appendChild(script);
  }
})(drupalSettings);

function shoeAiStatus(shoeAi) {
  if (shoeAi && shoeAi.status != null && shoeAi.status == 1) {
    return true;
  } else {
    return false;
  }
}
