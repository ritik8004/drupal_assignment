/**
 * @file
 * Initialize Alshaya Clarity tracking code.
 */

(function ($, Drupal, drupalSettings) {
  var msclarity_id = drupalSettings.alshaya_msclarity.msclarity_id;
  (function(c,l,a,r,i,t,y){
    c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
    t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
  })(window, document, "clarity", "script", msclarity_id);
})(jQuery, Drupal, drupalSettings);
