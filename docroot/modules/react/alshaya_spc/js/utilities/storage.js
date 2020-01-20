export const addInfoInStorage = function (cart) {
  // Adding current time to storage to know the last time cart updated.
  cart.last_update = new Date().getTime();
  const data = JSON.stringify(cart);
  localStorage.setItem('cart_data', data);
}

export const removeCartFromStorage = function () {
  localStorage.removeItem('cart_data');
}
