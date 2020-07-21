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
export const getPostData = (skuCode, variantSelected) => {
  const cartAction = 'add item';
  const cartData = Drupal.alshayaSpc.getCartData();
  const cartId = (cartData) ? cartData.cart_id : null;
  const qty = document.getElementById('qty') ? document.getElementById('qty').value : 1;

  const postData = {
    action: cartAction,
    sku: variantSelected,
    quantity: qty,
    cart_id: cartId,
  };

  const productData = {
    quantity: qty,
    parentSku: skuCode,
    sku: variantSelected,
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
) => {
  const productData = productDataValue;
  const cartData = Drupal.alshayaSpc.getCartData();
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
  } else if (response.data.cart_id) {
    if (response.data.response_message.status === 'success'
        && (typeof response.data.items[productData.variant] !== 'undefined'
          || typeof response.data.items[productData.parentSku] !== 'undefined')) {
      const cartItem = typeof response.data.items[productData.variant] !== 'undefined' ? response.data.items[productData.variant] : response.data.items[productData.parentSku];
      productData.totalQty = cartItem.qty;
    }

    let configurables = [];
    let productUrl = productInfo[skuCode].url;
    let price = productInfo[skuCode].priceRaw;
    let promotions = productInfo[skuCode].promotionsRaw;
    let productDataSKU = productInfo[skuCode].sku;
    let parentSKU = productInfo[skuCode].sku;
    let { maxSaleQty } = productInfo[skuCode];
    let maxSaleQtyParent = productInfo[skuCode].max_sale_qty_parent;
    const gtmAttributes = productInfo[skuCode].gtm_attributes;

    if (configurableCombinations) {
      const productVariantInfo = productInfo[skuCode].variants[productData.variant];
      productDataSKU = productData.variant;
      price = productVariantInfo.priceRaw;
      parentSKU = productVariantInfo.parent_sku;
      promotions = productVariantInfo.promotionsRaw;
      configurables = productVariantInfo.configurableOptions;
      maxSaleQty = productVariantInfo.maxSaleQty;
      maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;

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
      cartBtn.innerHTML = Drupal.t('Item added');
      cartBtn.classList.toggle('magv2-add-to-basket-success');
    }

    const { addToCartNotificationTime } = drupalSettings;

    // Removing the success button after 2 seconds.
    setTimeout(() => {
      if (cartBtn.classList.contains('magv2-add-to-basket-success')) {
        cartBtn.classList.remove('magv2-add-to-basket-success');
        cartBtn.innerHTML = Drupal.t('Add To Bag');
      }
    }, addToCartNotificationTime * 1000);
  }
};

export const getProductValues = (skuItemCode, variant, setVariant) => {
  let brandLogo; let brandLogoAlt; let
    brandLogoTitle = null;
  let configurableCombinations = '';

  const { productInfo } = drupalSettings;
  let title = '';
  let priceRaw = '';
  let finalPrice = '';
  let pdpGallery = '';
  if (skuItemCode) {
    if (productInfo[skuItemCode].brandLogo) {
      brandLogo = productInfo[skuItemCode].brandLogo.logo
        ? productInfo[skuItemCode].brandLogo.logo : null;
      brandLogoAlt = productInfo[skuItemCode].brandLogo.alt
        ? productInfo[skuItemCode].brandLogo.alt : null;
      brandLogoTitle = productInfo[skuItemCode].brandLogo.title
        ? productInfo[skuItemCode].brandLogo.title : null;
    }
    title = productInfo[skuItemCode].title;
    priceRaw = productInfo[skuItemCode].priceRaw;
    finalPrice = productInfo[skuItemCode].finalPrice;
    pdpGallery = productInfo[skuItemCode].rawGallery;
    if (productInfo[skuItemCode].type === 'configurable') {
      configurableCombinations = drupalSettings.configurableCombinations;
      if (variant == null) {
        setVariant(configurableCombinations[skuItemCode].firstChild);
      } else {
        title = productInfo[skuItemCode].variants[variant].cart_title;
        priceRaw = productInfo[skuItemCode].variants[variant].priceRaw;
        finalPrice = productInfo[skuItemCode].variants[variant].finalPrice;
        pdpGallery = productInfo[skuItemCode].variants[variant].rawGallery;
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
export const addToCartConfigurable = (e, id, configurableCombinations, skuCode, productInfo) => {
  e.preventDefault();
  // Adding add to cart loading.
  const addToCartBtn = document.getElementById(id);
  addToCartBtn.classList.toggle('magv2-add-to-basket-loader');

  const options = [];
  const attributes = configurableCombinations[skuCode].configurables;
  Object.keys(attributes).forEach((key) => {
    const option = {
      option_id: attributes[key].attribute_id,
      option_value: document.querySelector(`#${key}`).querySelectorAll('.active')[0].value,
    };

    // Skipping the psudo attributes.
    if (drupalSettings.psudo_attribute === undefined
      || drupalSettings.psudo_attribute !== option.option_id) {
      options.push(option);
    }
  });

  const variantSelected = document.getElementById('pdp-add-to-cart-form').getAttribute('variantselected');
  const getPost = getPostData(skuCode, variantSelected);

  const postData = getPost[0];
  const productData = getPost[1];

  postData.options = options;
  productData.product_name = productInfo[skuCode].variants[variantSelected].cart_title;
  productData.image = productInfo[skuCode].variants[variantSelected].cart_image;
  const cartEndpoint = drupalSettings.cart_update_endpoint;

  updateCart(cartEndpoint, postData).then(
    (response) => {
      triggerAddToCart(
        response,
        productData,
        productInfo,
        configurableCombinations,
        skuCode,
        addToCartBtn,
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
export const addToCartSimple = (e, id, skuCode, productInfo) => {
  e.preventDefault();
  // Adding add to cart loading.
  const addToCartBtn = document.getElementById(id);
  addToCartBtn.classList.toggle('magv2-add-to-basket-loader');

  const variantSelected = document.getElementById('pdp-add-to-cart-form').getAttribute('variantselected');

  const getPost = getPostData(skuCode, variantSelected);

  const postData = getPost[0];
  const productData = getPost[1];

  productData.productName = productInfo[skuCode].cart_title;
  productData.image = productInfo[skuCode].cart_image;

  const cartEndpoint = drupalSettings.cart_update_endpoint;

  updateCart(cartEndpoint, postData).then(
    (response) => {
      triggerAddToCart(response, productData, productInfo, skuCode, addToCartBtn);
    },
  )
    .catch((error) => {
      console.log(error.response);
    });
};
