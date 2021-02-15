/**
 * @file
 * Checkout olapic script for tracking the order.
 */
(function ($, Drupal, drupalSettings) {

    'use strict';

    Drupal.behaviors.olapiccheckoutpixel = {
        attach: function (context) {
            if (typeof drupalSettings.cartObject != "undefined") {
                //==== Olapic Require: DO NOT CHANGE
                var olapicRequireCheckoutScript = (function (oHead) {
                    var onError = function () {
                        throw new URIError('Olapic checkout script could not be loaded');
                    };
                    return function (olapicScriptSrc, onLoadCallback) {
                        var oScript = document.createElement('script');
                        oScript.type = 'text\/javascript';
                        oScript.src = olapicScriptSrc;
                        oScript.async = true;
                        oScript.onerror = onError;
                        if (onLoadCallback) {
                            if (oScript.addEventListener) {
                                oScript.addEventListener('load', onLoadCallback, false);
                            } else if (oScript.readyState) {
                                oScript.onreadystatechange = function () {
                                    if (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete') {
                                        onLoadCallback();
                                    }
                                };
                            } else {
                                oScript.attachEvent('load', onLoadCallback);
                            }
                        }
                        oHead.appendChild(oScript);
                    };
                })(document.head || document.getElementsByTagName('head')[0]);

                // ==== Checkout Code:
                olapicRequireCheckoutScript(drupalSettings.cartObject.olapic_checkout_pixel_external_script_url, function () {
                    // Initialization
                    olapicCheckout.init(drupalSettings.cartObject.data_apikey);

                    // Add the Products: Product loop starts. This is where you will store each product purchased info
                    for (var i = drupalSettings.cartObject.products.length - 1; i >= 0; i--) {
                        var product = drupalSettings.cartObject.products[i];

                        for (var c = product.qtyOrdered - 1; c >= 0; c--) {
                            olapicCheckout.addProduct(product.sku, product.finalPrice);
                        };
                    };
                    // Product loop ends.

                    // Add the metadata/attributes
                    olapicCheckout.addAttribute('transactionId', drupalSettings.cartObject.transaction_id);
                    olapicCheckout.addAttribute('currencyCode', drupalSettings.cartObject.currency);
                    // Add Segmentation Values
                    olapicCheckout.addSegment('country', drupalSettings.cartObject.country);
                    // Send the information
                    olapicCheckout.execute();
                });

            }
        }
    };
})(jQuery, Drupal, drupalSettings);
