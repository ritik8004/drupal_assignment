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
