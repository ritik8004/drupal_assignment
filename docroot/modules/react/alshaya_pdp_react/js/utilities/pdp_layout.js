import axios from 'axios';

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
 * Clear cart data.
 *
 * @param {*} selector
 */
export const clearCartData = () => {
  localStorage.removeItem('cart_data');
};

/**
 * Get the cart data from local storage.
 */
export const getCartData = () => {
  let cartData = localStorage.getItem('cart_data');
  if (cartData) {
    cartData = JSON.parse(cartData);
    if (cartData && cartData.cart !== undefined) {
      cartData = cartData.cart;
      if (cartData.cart_id !== null) {
        return cartData;
      }
    }
  }

  return null;
};

/**
 * Store product cart data in local storage.
 */
export const storeProductData = (data) => {
  const langcode = document.getElementsByTagName('html')[0].getAttribute('lang');
  const key = ['product', langcode, data.sku].join(':');
  const productData = {
    sku: data.sku,
    parentSKU: data.parentSKU,
    title: data.title,
    url: data.url,
    image: data.image,
    price: data.price,
    options: data.options,
    promotions: data.promotions,
    maxSaleQty: data.maxSaleQty,
    maxSaleQtyParent: data.maxSaleQtyParent,
    gtmAttributes: data.gtmAttributes,
    created: new Date().getTime(),
  };

  localStorage.setItem(key, JSON.stringify(productData));

  return data;
};

/**
 * Get post data on add to cart.
 */
export const getPostData = (skuCode, variantSelected) => {
  const cartAction = 'add item';
  const cartData = getCartData();
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
) => {
  const productData = productDataValue;
  // If there any error we throw from middleware.
  if (response.data.error === true) {
    if (response.data.error_code === '400') {
      clearCartData();
    }
  } else if (response.data.cart_id) {
    if (response.data.response_message.status === 'success'
        && (typeof response.data.items[productData.variant] !== 'undefined'
          || typeof response.data.items[productData.parentSku] !== 'undefined')) {
      const cartItem = typeof response.data.items[productData.variant] !== 'undefined' ? response.data.items[productData.variant] : response.data.items[productData.parentSku];
      productData.totalQty = cartItem.qty;
    }

    const form = document.getElementsByClassName('sku-base-form')[0];
    const cartNotification = new CustomEvent('product-add-to-cart-success', {
      detail: {
        response,
        productData,
      },
    });
    form.dispatchEvent(cartNotification);

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

    storeProductData({
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
        title = productInfo[skuItemCode].variants[variant].title;
        priceRaw = productInfo[skuItemCode].variants[variant].priceRaw;
        finalPrice = productInfo[skuItemCode].variants[variant].finalPrice;
        pdpGallery = productInfo[skuItemCode].variants[variant].rawGallery;
      }
    }
  }
  const shortDesc = skuItemCode ? productInfo[skuItemCode].shortDesc : [];
  const description = skuItemCode ? productInfo[skuItemCode].description : [];

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
  };
};
