/**
 * @file
 * Contains Alshaya ShoeAI functionality.
 */
(function (drupalSettings) {  
  let shoeAi = drupalSettings.shoeai;
  if (shoeAi.status != null && shoeAi.status == 'enabled') {
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
