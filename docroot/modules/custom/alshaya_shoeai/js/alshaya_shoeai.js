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
    // newRecommendation callback is only for PDP to set the default size as per recommendation from ShoeAI.
    let newRecommendation = function(recommendation){
      shoe_size_recommendation(recommendation)
    };
    // inCart callback is call only on PDP for adding product to cart from ShoeAI widget.
    let inCart = function(recommendation){
      shoe_size_add_to_cart(recommendation)
    };
    script.text = '{shopID:"' + shoeAi.shopId + '", locale:"' +
     language + '", scale: "eu", zeroHash:"' + shoeAi.zeroHash + '", newRecommendation:' + newRecommendation +', inCart:' + inCart + '}';
    document.body.appendChild(script);
    // Initialize shoeai purchase tracking script on cart page.
    document.addEventListener('updateCartItemData', function (e) {
      initialiseShoeSizeShoppingCart(e, 'updateCartItemData');
    });
    // Initialize shoeai purchase tracking script in PLP,SLP and PDP.
    document.addEventListener('product-add-to-cart-success', function (e) {
      initialiseShoeSizeShoppingCart(e, 'addToCartPlp');
    });
    var confirmationPage = document.querySelectorAll('#spc-checkout-confirmation');
    if (confirmationPage.length > 0 && drupalSettings.order_details) {
      addShoeSizeConfirmationScript();
    }
  }
})(drupalSettings);

/**
* Helper function for returning
* the status enabled/disabled of shoeai.
* Returns true/false boolean.
*/
function shoeAiStatus(shoeAi) {
  if (shoeAi && shoeAi.status != null && shoeAi.status == 1) {
    return true;
  }

  return false;
}

// Helper function for adding shoe in cart from add to cart button in shoeai widget.
window.shoe_size_add_to_cart = (recommendation) => {
  if(recommendation.size) {
    const recommendedSize = recommendation.size['eu'] ? recommendation.size['eu'].replace('.0','') : null;
    // work only if recommendedSize is not null.
    if (recommendedSize) {
      const addToCartButton = document.querySelector('#add-to-cart-main');
      addToCartButton.click();
      return;
    }
  }
}  

// Helper function for getting recommended shoesize from shoeai and select the size in PDP if available.
window.shoe_size_recommendation = (recommendation) => {
  const recommendedSize = recommendation.size['eu'] ? recommendation.size['eu'].replace('.0','') : null;
  // work only if recommendedSize is not null.
  if (recommendedSize) {
    const sizes = document.querySelectorAll('.magv2-select-list-name');
    for (const size of sizes) {
      if (size.innerText == recommendedSize) {
        size.click();
        return;
      }
    }
  }
}

// Helper function for initialising setShoeSizeShoppingCart function.
function initialiseShoeSizeShoppingCart(e, event) {
  const localCart = window.commerceBackend.getCartDataFromStorage();
  var items = localCart.cart.items;
  var totals = localCart.cart.totals.items;
  var updated_sku = '';
  var updated_qty = '';
  if (event == 'updateCartItemData') {
    // Updated_sku is required to know which item is updated.
    updated_sku = e.detail.data.item.sku;
    // Updated_qty is required as items and totals dont have correct quantity.
    updated_qty = e.detail.data.qty;
  }
  setShoeSizeShoppingCart(totals, items, updated_sku, updated_qty);
};

/*
 * Helper function for populating
 * ShoeSizeShoppingCart variable with cart item
 * and call shoeai ext. script.
 */
function setShoeSizeShoppingCart(totals, items, updated_sku, updated_qty) {
  // Using data.totals.items as it has information related to size of shoes.
  const products = Object.values(totals);
  // Products do not have skus hence need to take from data.items.
  const skus = Object.keys(items);
  if (skus.length && products.length) {
    const purchase = [];
    const { shopId, scale } = drupalSettings.shoeai;
    let currency = '';
    if (drupalSettings.alshaya_spc && drupalSettings.alshaya_spc.currency_config.currency_code) {
      currency = drupalSettings.alshaya_spc.currency_config.currency_code;
    }
    let i = 0;
    // Create purchase object with all products in cart.
    for (i; i < products.length; i++) {
      purchase[i] = {};
      purchase[i].shoeID = skus[i];
      purchase[i].price = products[i].price_incl_tax ? products[i].price_incl_tax : products[i].price;
      if (skus[i] == updated_sku) {
        purchase[i].quantity = updated_qty;
      } else {
        purchase[i].quantity = products[i].qty;
      }
      purchase[i].scale = scale;
      purchase[i].currency = currency;
      // fetch the size.
      const options = JSON.parse(products[i].options)
        ? JSON.parse(products[i].options)[0] : [];
      if (options) {
        purchase[i].size = options.value;
      }
    }
    // Setting the variable ShoeSizeShoppingCart and calling shoeai script.
    if (purchase.length && shopId) {
      window.ShoeSizeShoppingCart = {};
      window.ShoeSizeShoppingCart.shopID = shopId;
      window.ShoeSizeShoppingCart.purchases = purchase;
      const script = document.createElement('script');
      script.type = 'text/javascript';
      script.src = 'https://shoesize.me/plugin/cart.js?shopid=' + encodeURIComponent(window.ShoeSizeShoppingCart.shopID)
        + '&sid=' + Math.round(new Date().getTime() / 1000);
      (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(script);
    }
  }
};

/*
 * Helper function for populating
 * ShoeSizeShoppingCartConfirmation variable on confirmation page
 * and call shoeai ext. script for order confirmation.
 * Gets response true if order is unique if confirmation URL is reloaded than gets false.
 */
function addShoeSizeConfirmationScript() {
  var shoeAi = drupalSettings.shoeai;
  // Order confirmation script.
  var orderNumber = drupalSettings.order_details.order_number
    ? drupalSettings.order_details.order_number
    : 'null';
  var ssm_id = Math.round(new Date().getTime()/1000);
  if (orderNumber) {
    // Variable required for shoeai ext. js call returns false.
    window.ShoeSizeShoppingCartConfirmation = {};
    window.ShoeSizeShoppingCartConfirmation = {
      shopID: shoeAi.shopId,
      orderID: orderNumber,
      ssm_sid: ssm_id
    };
    var conirmationScript = document.createElement('script');
    conirmationScript.type = 'text/javascript';
    conirmationScript.src = 'https://shoesize.me/plugin/confirm.js?'+
      'shopid='+encodeURIComponent(shoeAi.shopId)+'&id='+orderNumber+
      '&sid='+ssm_id;
    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(conirmationScript);
  };
}