import React from 'react';
import { ClicknCollectContext } from '../../../context/ClicknCollect';
import {
  addShippingInCart,
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
    const { fullname, email } = this.context.contactInfo;
    const name = drupalSettings.user.uid > 0 ? fullname : e.target.elements.fullname.value.trim();
    const { firstname, lastname } = extractFirstAndLastName(name);
    const form_data = {
      static: {
        firstname,
        lastname,
        email: drupalSettings.user.uid > 0 ? email : e.target.elements.email.value,
        telephone: e.target.elements.mobile.value,
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

    this.processShippingUpdate(form_data);
  };

  /**
   * Validate mobile number and email address and on success process shipping address update.
   */
  processShippingUpdate = (form_data) => {
    // Mimic axio request when we don't want to validate email address for existing
    // or recently created customer.
    let customerValidationReuest = new Promise((resolve, reject) => {
      resolve({
        data: {
          exists: false,
        },
      });
    });

    const validationData = {
      'mobile': form_data.static.telephone,
    };

    if (this.context.contactInfo === null
      || (this.context.contactInfo.hasOwnProperty('email')
        && this.context.contactInfo.email !== form_data.static.email)
    ) {
      validationData['email'] = form_data.static.email;
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
            document.getElementById('email-error').innerHTML = Drupal.t('The email address %mail is not valid.', {'%mail': validationData['email']});
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
          this.updateShipping(form_data);
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
  updateShipping = (form_data) => {
    const cart_info = addShippingInCart('update shipping', form_data);
    if (cart_info instanceof Promise) {
      const { updateContactInfo } = this.context;
      cart_info
        .then((cart_result) => {
          removeFullScreenLoader();

          if (!cart_result) {
            return null;
          }

          if (cart_result.error) {
            console.error(cart_result.error_message);
            return null;
          }

          updateContactInfo(form_data.static);
          const cartData = {
            cart: cart_result,
            delivery_type: cart_result.delivery_type,
            address: form_data.store.address,
          };
          dispatchCustomEvent('refreshCartOnCnCSelect', {
            data: () => cartData,
          });
        })
        .catch((error) => {
          console.error(error);
        });
    }
  };

  render() {
    const { store } = this.props;
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
          subTitle={this.props.subTitle}
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
