/**
 * @file
 * Contains Alshaya ShoeAI functionality.
 */
(function (drupalSettings) {  
  let shoeAi = drupalSettings.shoeai;
  if (shoeAi.status != null && shoeAi.status == 'enabled') {
    let script = document.createElement('script');
    script.src = 'https://shoesize.me/assets/plugin/loader.js';
    script.type = 'text/javascript';
    script.async = true;  
    script.text = '{shopID:"' + shoeAi.shopId + '", locale: "' + shoeAi.locale + '", scale: "eu", zeroHash: "' + shoeAi.zeroHash + '"}';
    document.head.appendChild(script);
  }
})(drupalSettings);