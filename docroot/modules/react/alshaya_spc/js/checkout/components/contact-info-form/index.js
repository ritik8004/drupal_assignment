import React from 'react';
import { ClicknCollectContext } from '../../../context/ClicknCollect';
import {
  addShippingInCart, cleanMobileNumber,
  removeFullScreenLoader,
  showFullScreenLoader, validateInfo,
} from '../../../utilities/checkout_util';
import FixedFields from '../fixed-fields';
import { validateContactInfo } from '../../../utilities/checkout_address_process';
import { extractFirstAndLastName } from '../../../utilities/cart_customer_util';
import { dispatchCustomEvent } from '../../../utilities/events';

class ContactInfoForm extends React.Component {
  static contextType = ClicknCollectContext;

  handleSubmit = (e, store) => {
    e.preventDefault();

    if (drupalSettings.user.uid === 0) {
      const notValidAddress = validateContactInfo(e, false);
      if (notValidAddress) {
        return;
      }
    }

    showFullScreenLoader();
    const { contactInfo: { fullname, email } } = this.context;
    const name = drupalSettings.user.uid > 0 ? fullname : e.target.elements.fullname.value.trim();
    const { firstname, lastname } = extractFirstAndLastName(name);
    const formData = {
      static: {
        firstname,
        lastname,
        email: drupalSettings.user.uid > 0 ? email : e.target.elements.email.value,
        telephone: `+${drupalSettings.country_mobile_code}${cleanMobileNumber(e.target.elements.mobile.value)}`,
        country_id: drupalSettings.country_code,
      },
      shipping_type: 'cnc',
      store: {
        name: store.name,
        code: store.code,
        rnc_available: store.rnc_available,
        cart_address: store.cart_address,
      },
      carrier_info: { ...drupalSettings.map.cnc_shipping },
    };

    this.processShippingUpdate(formData);
  };

  /**
   * Validate mobile number and email address and on success process shipping address update.
   */
  processShippingUpdate = (formData) => {
    const validationData = {
      mobile: formData.static.telephone,
    };
    const { contactInfo } = this.context;

    if (contactInfo === null
      || (Object.prototype.hasOwnProperty.call(contactInfo, 'email')
        && contactInfo.email !== formData.static.email)
    ) {
      validationData.email = formData.static.email;
    }

    const validationRequest = validateInfo(validationData);
    // API call to validate mobile number and email address.
    return validationRequest.then((result) => {
      if (result.status === 200 && result.data.status) {
        // Show errors if any, else call update cart api to update shipping address.
        // Flag to determine if there any error.
        let isError = false;

        // If invalid mobile number.
        if (result.data.mobile === false) {
          document.getElementById('mobile-error').innerHTML = Drupal.t('Please enter valid mobile number.');
          document.getElementById('mobile-error').classList.add('error');
          isError = true;
        } else {
          // Remove error class and any error message.
          document.getElementById('mobile-error').innerHTML = '';
          document.getElementById('mobile-error').classList.remove('error');
        }

        if (result.data.email !== undefined) {
          if (result.data.email === 'invalid') {
            document.getElementById('email-error').innerHTML = Drupal.t('The email address %mail is not valid.', { '%mail': validationData.email });
            document.getElementById('email-error').classList.add('error');
            isError = true;
          } else if (result.data.email === 'exists') {
            document.getElementById('email-error').innerHTML = Drupal.t('Customer already exists.');
            document.getElementById('email-error').classList.add('error');
            isError = true;
          } else {
            document.getElementById('email-error').innerHTML = '';
            document.getElementById('email-error').classList.remove('error');
          }
        }

        if (isError) {
          removeFullScreenLoader();
        } else {
          this.updateShipping(formData);
        }
      }
    }).catch((error) => {
      removeFullScreenLoader();
      console.error(error);
    });
  };

  /**
   * Update cart with shipping address.
   */
  updateShipping = (formData) => {
    const cartInfo = addShippingInCart('update shipping', formData);
    if (cartInfo instanceof Promise) {
      const { updateContactInfo } = this.context;
      cartInfo
        .then((cart_result) => {
          removeFullScreenLoader();

          if (!cart_result) {
            return null;
          }

          if (cart_result.error) {
            console.error(cart_result.error_message);
            return null;
          }

          updateContactInfo(formData.static);
          const cartData = {
            cart: cart_result,
            delivery_type: cart_result.delivery_type,
            address: formData.store.address,
          };
          dispatchCustomEvent('refreshCartOnCnCSelect', {
            data: () => cartData,
          });
          return null;
        })
        .catch((error) => {
          console.error(error);
        });
    }
  };

  render() {
    const { store, subTitle } = this.props;
    const { contactInfo } = this.context;

    return (
      <form
        className="spc-contact-form"
        onSubmit={(e) => this.handleSubmit(e, store)}
      >
        <FixedFields
          showEmail={drupalSettings.user.uid === 0}
          showFullName={drupalSettings.user.uid === 0}
          default_val={contactInfo ? { static: contactInfo } : []}
          subTitle={subTitle}
        />
        <div className="spc-address-form-actions">
          <button
            id="save-address"
            className="spc-address-form-submit"
            type="submit"
          >
            {Drupal.t('Save')}
          </button>
        </div>
      </form>
    );
  }
}

export default ContactInfoForm;
