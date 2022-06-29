/**
 * @file
 * Add the olapic script once we have the product data.
 */

(function () {
  // Add the script after RCS page entity is loaded.
  RcsEventManager.addListener('alshayaPageEntityLoaded', function (e) {
    console.log(e);
  });
})(jQuery);
