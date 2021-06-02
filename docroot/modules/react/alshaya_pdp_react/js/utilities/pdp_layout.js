import axios from 'axios';
import React from 'react';
import ReactDOM from 'react-dom';

/**
 * Clear cart data.
 *
 * @param {*} selector
 */
export const updateCart = (url, postData) => axios({
  url,
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  data: JSON.stringify(postData),
});

/**
 * Get post data on add to cart.
 */
export const getPostData = (skuCode, variantSelected, parentSKU) => {
  const cartAction = 'add item';
  const cartData = Drupal.alshayaSpc.getCartData();
  const cartId = (cartData) ? cartData.cart_id : null;
  const qty = document.getElementById('qty') ? document.getElementById('qty').value : 1;
  // If parent sku is empty.
  const parentSku = parentSKU || skuCode;

  const postData = {
    action: cartAction,
    sku: parentSku,
    quantity: qty,
    cart_id: cartId,
    variant_sku: variantSelected,
  };

  const productData = {
    quantity: qty,
    parentSku: skuCode,
    sku: parentSKU,
    variant: variantSelected,
  };

  return [postData, productData];
};

/**
 * Triggers add to cart event.
 */
export const triggerAddToCart = (
  response,
  productDataValue,
  productInfo,
  configurableCombinations = null,
  skuCode,
  addToCartBtn,
  pdpLabelRefresh,
  context,
  closeModal,
) => {
  const productData = productDataValue;
  const cartBtn = addToCartBtn;

  // If there any error we throw from middleware.
  if (response.data.error === true) {
    let errorMessage = response.data.error_message;
    if (response.data.error_code === '604') {
      errorMessage = Drupal.t('The product that you are trying to add is not available.');
    }
    ReactDOM.render(<p>{errorMessage}</p>, document.getElementById('add-to-cart-error'));
    if (cartBtn.classList.contains('magv2-add-to-basket-loader')) {
      cartBtn.classList.remove('magv2-add-to-basket-loader');
      cartBtn.innerHTML = Drupal.t('Add To Bag');
      if (context === 'main' && window.innerWidth < 768) {
        document.querySelector('body').classList.remove('overlay-select');
      }
    }
    if (response.data.error_code === '400') {
      Drupal.alshayaSpc.clearCartData();
    }

    // Process required data and trigger add to cart failure event.
    const form = document.getElementsByClassName('sku-base-form')[0];
    const elements = document.querySelectorAll('.cart-form-attribute');
    const selectedOptions = [];
    // Get the key-value pair of selected option name and value.
    elements.forEach((element) => {
      const configLabel = element.getAttribute('data-attribute-name');
      const configValue = element.querySelector('ul li.active').getAttribute('data-attribute-label');
      const option = `${configLabel}: ${configValue}`;
      selectedOptions.push(option);
    });
    productData.options = selectedOptions;
    // Prepare the event.
    const cartNotification = new CustomEvent('product-add-to-cart-failed', {
      detail: {
        productData,
        message: errorMessage,
      },
    });
    // Dispatch event so that handlers can process it.
    form.dispatchEvent(cartNotification);
  } else if (response.data.cart_id) {
    if ((typeof response.data.items[productData.variant] !== 'undefined'
          || typeof response.data.items[productData.parentSku] !== 'undefined')) {
      const cartItem = typeof response.data.items[productData.variant] !== 'undefined' ? response.data.items[productData.variant] : response.data.items[productData.parentSku];
      productData.totalQty = cartItem.qty;
    }

    let configurables = [];
    let productUrl = (context === 'main') ? productInfo[skuCode].url : productInfo[skuCode].link;
    let price = (context === 'main') ? productInfo[skuCode].priceRaw : productInfo[skuCode].final_price;

    const promotions = productInfo[skuCode].promotionsRaw;
    let productDataSKU = productData.sku;
    let parentSKU = productData.parentSku;

    let maxSaleQty = (context === 'main') ? productData.maxSaleQty : productData.max_sale_qty;
    let maxSaleQtyParent = productData.max_sale_qty_parent;
    const gtmAttributes = productInfo[skuCode].gtm_attributes;
    let { freeGiftPromotion } = productInfo[skuCode];
    let options = [];

    if (configurableCombinations) {
      const productVariantInfo = productInfo[skuCode].variants[productData.variant];
      productDataSKU = productData.variant;
      price = productVariantInfo.priceRaw;
      parentSKU = productVariantInfo.parent_sku;
      configurables = (context === 'main') ? productVariantInfo.configurableOptions : productVariantInfo.configurable_values;
      maxSaleQty = (context === 'main') ? productVariantInfo.maxSaleQty : productVariantInfo.max_sale_qty;
      maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;
      options = (context === 'main') ? productVariantInfo.configurableOptions : productVariantInfo.configurable_values;
      freeGiftPromotion = productVariantInfo.freeGiftPromotion;

      if (productVariantInfo.url !== undefined) {
        const langcode = document.getElementsByTagName('html')[0].getAttribute('lang');
        productUrl = productVariantInfo.url[langcode];
      }
    }

    Drupal.alshayaSpc.storeProductData({
      sku: productDataSKU,
      parentSKU,
      title: productData.product_name,
      url: productUrl,
      image: productData.image,
      price,
      options,
      configurables,
      promotions,
      maxSaleQty,
      maxSaleQtyParent,
      gtmAttributes,
      freeGiftPromotion,
    });

    // Triggering event to notify react component.
    const refreshMiniCartEvent = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data() { return response.data; }, productData } });
    document.dispatchEvent(refreshMiniCartEvent);

    const refreshCartEvent = new CustomEvent('refreshCart', { bubbles: true, detail: { data() { return response.data; } } });
    document.dispatchEvent(refreshCartEvent);

    const cartData = Drupal.alshayaSpc.getCartData();
    const form = document.getElementsByClassName('sku-base-form')[0];
    const cartNotification = new CustomEvent('product-add-to-cart-success', {
      bubbles: true,
      detail: {
        productData,
        cartData,
      },
    });

    // Adding add to cart button
    // success class.
    if (cartBtn.classList.contains('magv2-add-to-basket-loader')) {
      cartBtn.classList.remove('magv2-add-to-basket-loader');
      cartBtn.innerHTML = `${Drupal.t('Item added')}<span class="magv2-button-tick-icon" />`;
      cartBtn.classList.toggle('magv2-add-to-basket-success');
    }

    const { addToCartNotificationTime } = drupalSettings;

    // Close CS/US modal/size panel and display cart notification
    // after 500ms of add to cart success state.
    setTimeout(() => {
      if (context === 'main') {
        document.querySelector('body').classList.remove('overlay-select');
      } else {
        closeModal();
      }
      form.dispatchEvent(cartNotification);
    }, 500);

    // Removing the success button after 2 seconds.
    setTimeout(() => {
      if (cartBtn.classList.contains('magv2-add-to-basket-success')) {
        cartBtn.classList.remove('magv2-add-to-basket-success');
        cartBtn.innerHTML = Drupal.t('Add To Bag');
      }
    }, addToCartNotificationTime * 1000);

    // Refresh dynamic promo labels on cart update.
    pdpLabelRefresh(cartData);
  }
};

export const getProductValues = (skuItemCode, variant, setVariant) => {
  let brandLogo; let brandLogoAlt; let
    brandLogoTitle; let freeGiftImage;
  let freeGiftPromoUrl; let freeGiftMessage;
  let freeGiftTitle; let freeGiftPromoCode = null;
  let freeGiftPromoType;
  let configurableCombinations = '';
  const { productInfo } = drupalSettings;
  const { variants } = productInfo[skuItemCode];
  const { stockStatus } = productInfo[skuItemCode];
  const { productLabels } = drupalSettings;
  let title = '';
  let priceRaw = '';
  let finalPrice = '';
  let pdpGallery = '';
  let labels = '';
  let stockQty = '';
  let firstChild = '';
  let promotions = '';
  if (skuItemCode) {
    if (productInfo[skuItemCode].brandLogo) {
      brandLogo = productInfo[skuItemCode].brandLogo.logo
        ? productInfo[skuItemCode].brandLogo.logo : null;
      brandLogoAlt = productInfo[skuItemCode].brandLogo.alt
        ? productInfo[skuItemCode].brandLogo.alt : null;
      brandLogoTitle = productInfo[skuItemCode].brandLogo.title
        ? productInfo[skuItemCode].brandLogo.title : null;
    }
    // free gift promotion variable from parent sku.
    if (productInfo[skuItemCode].freeGiftPromotion.length !== 0) {
      freeGiftPromoType = productInfo[skuItemCode].freeGiftPromotion['#promo_type'];
      if (freeGiftPromoType === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
        freeGiftImage = productInfo[skuItemCode].freeGiftPromotion['#image'] || null;
        freeGiftTitle = productInfo[skuItemCode].freeGiftPromotion.promo_title || null;
        freeGiftPromoCode = productInfo[skuItemCode].freeGiftPromotion['#promo_code'] || null;
        freeGiftPromoUrl = productInfo[skuItemCode].freeGiftPromotion['#promo_web_url'] || null;
        freeGiftMessage = productInfo[skuItemCode].freeGiftPromotion['#message']
          ? productInfo[skuItemCode].freeGiftPromotion['#message']['#markup']
          : null;
      } else {
        freeGiftImage = productInfo[skuItemCode].freeGiftPromotion['#sku_image']
          ? productInfo[skuItemCode].freeGiftPromotion['#sku_image']
          : null;
        freeGiftTitle = productInfo[skuItemCode].freeGiftPromotion['#free_sku_title']
          ? productInfo[skuItemCode].freeGiftPromotion['#free_sku_title']
          : null;
        freeGiftPromoCode = productInfo[skuItemCode].freeGiftPromotion['#promo_code'];
      }
    }
    title = productInfo[skuItemCode].cart_title;
    priceRaw = productInfo[skuItemCode].priceRaw;
    finalPrice = productInfo[skuItemCode].finalPrice;
    pdpGallery = productInfo[skuItemCode].rawGallery;
    labels = productLabels[skuItemCode];
    stockQty = productInfo[skuItemCode].stockQty;
    firstChild = skuItemCode;
    promotions = productInfo[skuItemCode].promotionsRaw;
    if (productInfo[skuItemCode].type === 'configurable') {
      configurableCombinations = drupalSettings.configurableCombinations;
      if (Object.keys(variants).length > 0) {
        if (variant == null) {
          setVariant(configurableCombinations[skuItemCode].firstChild);
        } else {
          const variantInfo = productInfo[skuItemCode].variants[variant];
          title = variantInfo.cart_title;
          priceRaw = variantInfo.priceRaw;
          finalPrice = variantInfo.finalPrice;
          pdpGallery = variantInfo.rawGallery;
          labels = productLabels[variant];
          stockQty = variantInfo.stock.qty;
          firstChild = configurableCombinations[skuItemCode].firstChild;
          promotions = variantInfo.promotionsRaw;
          // free gift promotion variable from variant sku.
          if (productInfo[skuItemCode].freeGiftPromotion.length !== 0) {
            freeGiftPromoType = variantInfo.freeGiftPromotion['#promo_type'];
            if (freeGiftPromoType === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
              freeGiftImage = variantInfo.freeGiftPromotion['#image']
                || null;
              freeGiftTitle = variantInfo.freeGiftPromotion.promo_title
                || null;
              freeGiftPromoCode = variantInfo.freeGiftPromotion['#promo_code']
                || null;
              freeGiftPromoUrl = variantInfo.freeGiftPromotion['#promo_web_url']
                || null;
              freeGiftMessage = variantInfo.freeGiftPromotion['#message']
                ? variantInfo.freeGiftPromotion['#message']['#markup']
                : null;
            } else {
              freeGiftImage = variantInfo.freeGiftPromotion['#sku_image']
                ? variantInfo.freeGiftPromotion['#sku_image']
                : null;
              freeGiftTitle = variantInfo.freeGiftPromotion['#free_sku_title']
                ? variantInfo.freeGiftPromotion['#free_sku_title']
                : null;
              freeGiftPromoCode = variantInfo.freeGiftPromotion['#promo_code'];
            }
          }
        }
      }
    }
  }
  const shortDesc = skuItemCode ? productInfo[skuItemCode].shortDesc : [];
  const description = skuItemCode ? productInfo[skuItemCode].description : [];
  const relatedProducts = [
    'crosssell',
    'upsell',
    'related',
  ];

  return {
    brandLogo,
    brandLogoAlt,
    brandLogoTitle,
    title,
    priceRaw,
    finalPrice,
    pdpGallery,
    shortDesc,
    description,
    configurableCombinations,
    relatedProducts,
    stockStatus,
    labels,
    stockQty,
    firstChild,
    promotions,
    freeGiftImage,
    freeGiftTitle,
    freeGiftPromoCode,
    freeGiftPromoUrl,
    freeGiftMessage,
    freeGiftPromoType,
  };
};

/**
 * Fetch available stores for given lat and lng.
 */
export const fetchAvailableStores = (coords) => {
  const { productInfo } = drupalSettings;
  let skuItemCode = null;
  if (productInfo) {
    [skuItemCode] = Object.keys(productInfo);
  }
  const baseUrl = window.location.origin;
  const apiUrl = Drupal.url(`stores/product/${btoa(skuItemCode)}/${coords.lat}/${coords.lng}?json`);
  const GET_STORE_URL = `${baseUrl}${apiUrl}`;
  return axios.get(GET_STORE_URL);
};

/**
 * Add to cart on click event for configurable products.
 */
export const addToCartConfigurable = (
  e,
  id,
  configurableCombinations,
  skuCode,
  productInfo,
  pdpLabelRefresh,
  context,
  closeModal,
) => {
  e.preventDefault();
  // Adding add to cart loading.
  const addToCartBtn = document.getElementById(id);
  addToCartBtn.classList.toggle('magv2-add-to-basket-loader');

  const options = [];
  const attributes = configurableCombinations[skuCode].configurables;
  Object.keys(attributes).forEach((key) => {
    const option = {
      option_id: attributes[key].attribute_id,
      option_value: document.querySelector(`#pdp-add-to-cart-form-${context} #${key}`).querySelectorAll('.active')[0].value,
    };

    // Skipping the psudo attributes.
    if (drupalSettings.psudo_attribute === undefined
      || drupalSettings.psudo_attribute !== option.option_id) {
      options.push(option);
    }
  });

  const variantSelected = document.getElementById(`pdp-add-to-cart-form-${context}`).getAttribute('variantselected');
  const parentSKU = productInfo[skuCode].variants[variantSelected].parent_sku;
  const getPost = getPostData(skuCode, variantSelected, parentSKU);

  const postData = getPost[0];
  const productData = getPost[1];

  postData.options = options;
  productData.product_name = (context === 'main')
    ? productInfo[skuCode].variants[variantSelected].cart_title
    : productInfo[skuCode].variants[variantSelected].title;
  productData.image = productInfo[skuCode].variants[variantSelected].cart_image;
  const cartEndpoint = `${drupalSettings.cart_update_endpoint}?lang=${drupalSettings.path.currentLanguage}`;

  updateCart(cartEndpoint, postData).then(
    (response) => {
      triggerAddToCart(
        response,
        productData,
        productInfo,
        configurableCombinations,
        skuCode,
        addToCartBtn,
        pdpLabelRefresh,
        context,
        closeModal,
      );
    },
  )
    .catch((error) => {
      Drupal.logJavascriptError('addToCartConfigurable', error, GTM_CONSTANTS.CART_ERRORS);
    });
};

/**
 * Add to cart on click event for simple products.
 */
export const addToCartSimple = (
  e,
  id,
  skuCode,
  productInfo,
  pdpLabelRefresh,
  context,
  closeModal,
) => {
  e.preventDefault();
  // Adding add to cart loading.
  const addToCartBtn = document.getElementById(id);
  addToCartBtn.classList.toggle('magv2-add-to-basket-loader');

  const variantSelected = document.getElementById(`pdp-add-to-cart-form-${context}`).getAttribute('variantselected');
  const options = [];

  const getPost = getPostData(skuCode, variantSelected, skuCode);

  const postData = getPost[0];
  const productData = getPost[1];
  postData.options = options;

  productData.product_name = (context === 'main') ? productInfo[skuCode].cart_title : productInfo[skuCode].title;
  productData.image = productInfo[skuCode].cart_image;

  const cartEndpoint = `${drupalSettings.cart_update_endpoint}?lang=${drupalSettings.path.currentLanguage}`;

  updateCart(cartEndpoint, postData).then(
    (response) => {
      triggerAddToCart(
        response,
        productData,
        productInfo,
        null,
        skuCode,
        addToCartBtn,
        pdpLabelRefresh,
        context,
        closeModal,
      );
    },
  )
    .catch((error) => {
      Drupal.logJavascriptError('addToCartSimple', error, GTM_CONSTANTS.CART_ERRORS);
    });
};

/**
 * Close side modals when clicked anywhere on screen.
 */
export const closeModalHelper = (overlayClass, containerClass, closeModalfn) => {
  document.querySelector('body').addEventListener('click', (e) => {
    let checkIfModalIsNotOpen;

    // Skip if modal is not open.
    if (Array.isArray(overlayClass)) {
      checkIfModalIsNotOpen = [...overlayClass]
        .reduce((cond, ent) => cond && !document.querySelector('body').classList.contains(ent), true);
    } else {
      checkIfModalIsNotOpen = !document.querySelector('body').classList.contains(overlayClass);
    }

    // Return if not open as logic should only affect open modal state.
    if (checkIfModalIsNotOpen) {
      return;
    }

    // Skip if clicked inside modal container.
    let currEl = e.target;

    const containerClassReducer = (cond, ent) => cond || currEl.classList.contains(ent);

    // Check if the clicked element is inside container class/es.
    while (!currEl.nodeName === 'BODY') {
      let ifClickedInsideContainerClasses;

      if (Array.isArray(containerClass)) {
        ifClickedInsideContainerClasses = [...containerClass]
          .reduce(containerClassReducer, false);
      } else {
        ifClickedInsideContainerClasses = currEl.classList.contains(containerClass);
      }

      // If user clicks inside container class then return w/o doing anything.
      if (ifClickedInsideContainerClasses) {
        return;
      }

      currEl = currEl.parentNode;
    }

    // If bubbling reaches top of DOM tree (body), trigger logic for closing modal/s.
    if (currEl.nodeName === 'BODY') {
      if (Array.isArray(overlayClass)) {
        overlayClass.forEach((el) => {
          document.querySelector('body').classList.remove(el);
        });
      } else {
        closeModalfn(e);
      }
    }
  });
};

/**
 *
 * @param {booleon} productBuyableStatus
 *  Sku's buyable status.
 * @returns {boolean}
 *  Return true if checkoutFeatureStatus is enabled, and
 *  product is buyable. False if anyone fails.
 */
export const isCartAvailable = (productBuyableStatus) => {
  // Get the global checkout feature status from drupal settings.
  const { checkoutFeatureStatus } = drupalSettings;

  // Check if checkout is enabled and product is buyable.
  return checkoutFeatureStatus === 'enabled' && productBuyableStatus;
};
