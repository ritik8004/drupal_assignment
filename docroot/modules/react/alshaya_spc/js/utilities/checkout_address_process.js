import { getShippingMethods } from './checkout_util';
import { addShippingInCart } from './update_cart';

/**
 * Process the data got from address form submission.
 *
 * @param {*} e
 * @param {*} cart_id
 */
export const checkoutAddressProcess = function (e, cart_id) {
  let form_data = {};
  form_data['static'] = {
    'firstname': e.target.elements.fname.value,
    'lastname': e.target.elements.lname.value,
    'email': e.target.elements.email.value,
    'city': 'Dummy Value',
    'telephone': e.target.elements.mobile.value,
    'country_id': window.drupalSettings.country_code
  };

  // Getting dynamic fields data.
  Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
    form_data[field.key] = e.target.elements[key].value
  });

  // Get shipping methods based on address info.
  let shipping_methods = getShippingMethods(cart_id, form_data);
  if (shipping_methods instanceof Promise) {
    shipping_methods.then((shipping) => {
      // Add shipping method in cart.
      form_data['carrier_info'] = {
        'code': shipping[0].carrier_code,
        'method': shipping[0].method_code
      };
      var cart = addShippingInCart('update shipping', form_data);
      if (cart instanceof Promise) {
        cart.then((cart_result) => {
          let cart_data = {
            'cart': cart_result,
            'delivery_type': cart_result.delivery_method,
            'shipping_methods': shipping,
            'address': form_data
          }
          var event = new CustomEvent('refreshCartOnAddress', {
            bubbles: true,
            detail: {
              data: () => cart_data
            }
          });
          document.dispatchEvent(event);
        });
      }
    });
  }
}
