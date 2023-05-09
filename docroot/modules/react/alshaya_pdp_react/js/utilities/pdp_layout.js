import axios from 'axios';
import React from 'react';
import ReactDOM from 'react-dom';
import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Clear cart data.
 *
 * @param {*} selector
 */
export const updateCart = (postData) => window.commerceBackend.addUpdateRemoveCartItem(postData);

/**
 * Get the product label list.
 *
 * @param {object} productInfo
 *   Product info object.
 * @param {string} skuItemCode
 *   The main product sku.
 *
 * @return {object}
 *   List of product labels.
 */
export const getProductLabels = (productInfo, skuItemCode) => {
  let labels = {};
  // For V3, we are passing the product label in the productInfo object itself.
  if (hasValue(productInfo[skuItemCode])
    && hasValue(productInfo[skuItemCode].labels)) {
    labels = productInfo[skuItemCode].labels;
  } else if (hasValue(drupalSettings.productLabels)) {
    // This is for V2 sites, where we get the data from Drupal settings.
    labels = drupalSettings.productLabels;
  }

  return labels;
};

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
export const triggerAddToCart = async (
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

    // Process free gift promotion graphQl response before adding.
    if (Drupal.hasValue(productInfo[skuCode].freeGiftPromotion)
      && Drupal.hasValue(window.commerceBackend.processFreeGiftDataReactRender)) {
      await window.commerceBackend.processFreeGiftDataReactRender(
        productInfo,
        skuCode,
      );
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

    gtmAttributes.variant = productDataSKU;
    Drupal.alshayaSpc.storeProductData({
      id: productInfo[skuCode].id,
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

    // Send notification after we finished adding to cart.
    const event = new CustomEvent('afterAddToCart', {
      bubbles: true,
      detail: {
        context: 'pdp',
        productData,
        cartData,
      },
    });
    document.dispatchEvent(event);
  }
};

export const getProductValues = (productInfo, configurableCombinations,
  skuItemCode, variant, setVariant) => {
  let brandLogo; let brandLogoAlt; let
    brandLogoTitle; let freeGiftImage;
  let freeGiftPromoUrl; let freeGiftMessage;
  let freeGiftTitle; let freeGiftPromoCode = null;
  let freeGiftPromoType;
  const { variants } = productInfo[skuItemCode];
  const { stockStatus } = productInfo[skuItemCode];
  const productLabels = getProductLabels(productInfo, skuItemCode);
  let title = '';
  let priceRaw = '';
  let finalPrice = '';
  let pdpGallery = '';
  let labels = '';
  let stockQty = '';
  let firstChild = '';
  let promotions = '';
  let deliveryOptions = null;
  let expressDeliveryClass = '';
  let bigTickectProduct = false;
  let isProductBuyable = '';
  let eligibleForReturn = false;
  let fit = '';
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
    fit = productInfo[skuItemCode].fit;
    labels = hasValue(productLabels) ? productLabels[skuItemCode] : [];
    stockQty = productInfo[skuItemCode].stockQty;
    firstChild = skuItemCode;
    promotions = productInfo[skuItemCode].promotionsRaw;
    deliveryOptions = productInfo[skuItemCode].deliveryOptions;
    expressDeliveryClass = productInfo[skuItemCode].expressDeliveryClass;
    eligibleForReturn = productInfo[skuItemCode].eligibleForReturn;
    if (productInfo[skuItemCode].bigTickectProduct) {
      bigTickectProduct = productInfo[skuItemCode].bigTickectProduct;
    }
    if (productInfo[skuItemCode].type === 'configurable') {
      if (Object.keys(variants).length > 0) {
        if (variant == null) {
          setVariant(configurableCombinations[skuItemCode].firstChild);
        } else {
          const variantInfo = productInfo[skuItemCode].variants[variant];
          title = variantInfo.cart_title;
          priceRaw = variantInfo.priceRaw;
          finalPrice = variantInfo.finalPrice;
          pdpGallery = variantInfo.rawGallery;
          fit = hasValue(variantInfo.fit) ? variantInfo.fit : fit;
          labels = hasValue(productLabels) ? productLabels[variant] : [];
          stockQty = variantInfo.stock.qty;
          firstChild = configurableCombinations[skuItemCode].firstChild;
          promotions = variantInfo.promotionsRaw;
          deliveryOptions = variantInfo.deliveryOptions;
          expressDeliveryClass = variantInfo.expressDeliveryClass;
          eligibleForReturn = variantInfo.eligibleForReturn;
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
  // The additional attribute sometimes is empty and if it's empty then convert
  // it to array.
  let additionalAttributes = {};
  if (hasValue(fit)) {
    additionalAttributes.fit = {
      value: fit,
      label: Drupal.t('FIT'),
    };
  }
  if (hasValue(skuItemCode)
    && hasValue(productInfo[skuItemCode])
    && productInfo[skuItemCode].additionalAttributes) {
    additionalAttributes = Object.assign(additionalAttributes,
      productInfo[skuItemCode].additionalAttributes);
  }

  const relatedProducts = [
    'crosssell',
    'upsell',
    'related',
  ];
  isProductBuyable = productInfo[skuItemCode].is_product_buyable;
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
    additionalAttributes,
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
    deliveryOptions,
    expressDeliveryClass,
    isProductBuyable,
    bigTickectProduct,
    eligibleForReturn,
  };
};

/**
 * Fetch available stores for given lat and lng.
 */
export const fetchAvailableStores = (productInfo, coords) => {
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

  updateCart(postData).then(
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

  updateCart(postData).then(
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
