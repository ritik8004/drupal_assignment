export const addInfoInStorage = (cart) => {
  const cartObj = { ...cart };
  // Adding current time to storage to know the last time cart updated.
  cartObj.last_update = new Date().getTime();
  const data = JSON.stringify(cart);
  localStorage.setItem('cart_data', data);
};

export const removeCartFromStorage = () => localStorage.removeItem('cart_data');

export const getInfoFromStorage = () => {
  let cartData = localStorage.getItem('cart_data');
  if (!cartData) {
    window.cartData = null;
    return null;
  }
  cartData = JSON.parse(cartData);
  window.cartData = cartData;
  return cartData;
};
