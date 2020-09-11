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
    const errorMessage = <p>{response.data.error_message}</p>;
    ReactDOM.render(errorMessage, document.getElementById('add-to-cart-error'));
    if (cartBtn.classList.contains('magv2-add-to-basket-loader')) {
      cartBtn.classList.remove('magv2-add-to-basket-loader');
      cartBtn.innerHTML = Drupal.t('Add To Bag');
    }
    if (response.data.error_code === '400') {
      Drupal.alshayaSpc.clearCartData();
    }
    const cartData = Drupal.alshayaSpc.getCartData();
    const form = document.getElementsByClassName('sku-base-form')[0];
    const cartNotification = new CustomEvent('product-add-to-cart-failed', {
      detail: {
        productData,
        cartData,
      },
    });
    form.dispatchEvent(cartNotification);
  } else if (response.data.cart_id) {
    if (response.data.response_message.status === 'success'
        && (typeof response.data.items[productData.variant] !== 'undefined'
          || typeof response.data.items[productData.parentSku] !== 'undefined')) {
      const cartItem = typeof response.data.items[productData.variant] !== 'undefined' ? response.data.items[productData.variant] : response.data.items[productData.parentSku];
      productData.totalQty = cartItem.qty;
    }

    let configurables = [];
    let productUrl = (context === 'main') ? productInfo[skuCode].url : productInfo[skuCode].link;
    let price = (context === 'main') ? productInfo[skuCode].priceRaw : productInfo[skuCode].final_price;

    let promotions = productInfo[skuCode].promotionsRaw;
    let productDataSKU = productData.sku;
    let parentSKU = productData.parentSku;

    let maxSaleQty = (context === 'main') ? productData.maxSaleQty : productData.max_sale_qty;
    let maxSaleQtyParent = productData.max_sale_qty_parent;
    const gtmAttributes = productInfo[skuCode].gtm_attributes;
    let options = [];

    if (configurableCombinations) {
      const productVariantInfo = productInfo[skuCode].variants[productData.variant];
      productDataSKU = productData.variant;
      price = productVariantInfo.priceRaw;
      parentSKU = productVariantInfo.parent_sku;
      promotions = productVariantInfo.promotionsRaw;
      configurables = (context === 'main') ? productVariantInfo.configurableOptions : productVariantInfo.configurable_values;
      maxSaleQty = (context === 'main') ? productVariantInfo.maxSaleQty : productVariantInfo.max_sale_qty;
      maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;
      options = (context === 'main') ? productVariantInfo.configurableOptions : productVariantInfo.configurable_values;

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
    form.dispatchEvent(cartNotification);
    // Adding add to cart button
    // success class.
    if (cartBtn.classList.contains('magv2-add-to-basket-loader')) {
      cartBtn.classList.remove('magv2-add-to-basket-loader');
      cartBtn.innerHTML = `${Drupal.t('Item added')}<span class="magv2-button-tick-icon" />`;
      cartBtn.classList.toggle('magv2-add-to-basket-success');
    }

    const { addToCartNotificationTime } = drupalSettings;

    // Close CS/US modal on add to cart success.
    if (context === 'related') {
      closeModal();
    }

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
  let freeGiftTitle; let freeGiftPromoCode = null;
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
    if (productInfo[skuItemCode].freeGiftPromotion !== undefined) {
      freeGiftImage = productInfo[skuItemCode].freeGiftPromotion['#sku_image']
        ? productInfo[skuItemCode].freeGiftPromotion['#sku_image'] : null;
      freeGiftTitle = productInfo[skuItemCode].freeGiftPromotion['#free_sku_title']
        ? productInfo[skuItemCode].freeGiftPromotion['#free_sku_title'] : null;
      freeGiftPromoCode = productInfo[skuItemCode].freeGiftPromotion['#promo_code'];
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
          title = productInfo[skuItemCode].variants[variant].cart_title;
          priceRaw = productInfo[skuItemCode].variants[variant].priceRaw;
          finalPrice = productInfo[skuItemCode].variants[variant].finalPrice;
          pdpGallery = productInfo[skuItemCode].variants[variant].rawGallery;
          labels = productLabels[variant];
          stockQty = productInfo[skuItemCode].variants[variant].stock.qty;
          firstChild = configurableCombinations[skuItemCode].firstChild;
          promotions = productInfo[skuItemCode].variants[variant].promotionsRaw;
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
  const apiUrl = Drupal.url(`stores/product/${skuItemCode}/${coords.lat}/${coords.lng}?json`);
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
      console.log(error);
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
      console.log(error.response);
    });
};
