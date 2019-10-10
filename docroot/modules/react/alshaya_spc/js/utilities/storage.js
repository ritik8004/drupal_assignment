export const addInfoInStorage = function (cart) {
  const data = JSON.stringify(cart);
  localStorage.setItem('cart_data', data);
}
