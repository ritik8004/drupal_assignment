/**
 * @file
 * Add the olapic script once we have the product data.
 */

(function ($, drupalSettings) {
  // Add the script after RCS page entity is loaded.
  RcsEventManager.addListener('alshayaPageEntityLoaded', function (e) {
    // Create a script tag and inject all the required attributes.
    var script = document.createElement('script');
    // Get the required info from the block div.
    const rcsOlaPicBlock = $('#block-rcsalshayaolapicwidget-2');
    script.setAttribute('id', 'olapic-' + rcsOlaPicBlock.attr('rcs_div_id'));
    script.setAttribute('data-instance', rcsOlaPicBlock.attr('rcs_instance_id'));
    script.setAttribute('data-tags', e.detail.entity.sku);
    script.setAttribute('data-olapic', rcsOlaPicBlock.attr('rcs_data_olapic'));
    script.setAttribute('data-apikey', rcsOlaPicBlock.attr('rcs_data_apikey'));
    // Append the script tag inside the div.
    rcsOlaPicBlock.append(script);

    // Adding the olapic external script here because adding it in initial load
    // will not be able to replace the above olapic script.
    var olapic_external_script = document.createElement('script');
    olapic_external_script.src = drupalSettings.olapic_external_script_url;
    olapic_external_script.defer = true;

    document.body.appendChild(olapic_external_script);

    // Re-attach all behaviors.
    Drupal.attachBehaviors(document, drupalSettings);
  });
})(jQuery, drupalSettings);
