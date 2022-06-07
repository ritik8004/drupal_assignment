(function ($,Drupal) {
  'use strict';

  /**
   * Global goinstore function to perform add to cart.
   *
   * @param {string} parentSku
   *   The sku value.
   * @param {string} childSku
   *   The child sku value.
   * @param {string} qty
   *   The child sku value.
   *
   * @returns {object}
   *   The product info object.
   */
  Drupal.goinstore = function (parentSku,childSku,qty) {
    // GoInStore global function to add product to cart.
    const options = [];
    var productData = Drupal.getProductData(parentSku);
    if (productData instanceof Promise) {
      productData.then((data) => {
        if (data.configurable_combinations !== undefined) {
          // Prepare the array of selected options.
          Object.keys(data.configurable_combinations.by_sku[childSku]).forEach((attributeName) => {
            if (data.configurable_attributes[attributeName].is_pseudo_attribute) {
              return;
            }
            const option = {
              option_id: data.configurable_attributes[attributeName].id,
              option_value: data.configurable_combinations.by_sku[childSku][attributeName],
            };
            options.push(option);
          });
        }

        // Perform Add to cart.
        const responce =  window.commerceBackend.addUpdateRemoveCartItem({
          action: 'add item',
          sku: parentSku,
          quantity: qty,
          options: options,
        });
        if (responce instanceof Promise) {
          responce.then((result) => {
            if (result.status === 200 && result.data !== undefined) {
              // Refreshing mini-cart.
              const eventMiniCart = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => result.data } });
              document.dispatchEvent(eventMiniCart);
              // Refreshing cart components.
              const eventCart = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => result.data } });
              document.dispatchEvent(eventCart);
            }
          });
        }
      });
    }
  };

  /**
   * Return product info from backend.
   *
   * @param {string} sku
   *   The sku value.
   *
   * @returns {object}
   *   The product info object.
   */
  Drupal.getProductData = async function (sku) {
    // Prepare the product info api url.
    const apiUrl = Drupal.url(`rest/v1/product-info/${btoa(sku)}`);
    return jQuery.ajax({
      url: apiUrl,
      type: 'GET',
      dataType: 'json',
      success: function (res) {
        return res;
      },
      error: function (xhr, textStatus, error) {
        // Processing of error here.
        Drupal.alshayaLogger('error', 'Failed to fetch product info data: @error', {
          '@error': error
        });
        return error;
      }
    });
  };

})(jQuery,Drupal);
