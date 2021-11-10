import React from 'react';
import { ClicknCollectContext } from '../../../context/ClicknCollect';
import {
  addShippingInCart, cleanMobileNumber,
  removeFullScreenLoader,
  showFullScreenLoader, validateInfo,
} from '../../../utilities/checkout_util';
import FixedFields from '../fixed-fields';
import { validateContactInfo, addressFormInlineErrorScroll } from '../../../utilities/address_util';
import { extractFirstAndLastName } from '../../../utilities/cart_customer_util';
import dispatchCustomEvent from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';
import collectionPointsEnabled from '../../../../../js/utilities/pudoAramaxCollection';

class ContactInfoForm extends React.Component {
  static contextType = ClicknCollectContext;

  handleSubmit = (e, store, handleScrollTo, errorSuccessMessage) => {
    e.preventDefault();
    if (errorSuccessMessage !== undefined && errorSuccessMessage !== null) {
      handleScrollTo();
    }

    const contactInfoError = validateContactInfo(e, (drupalSettings.user.uid === 0));
    if (contactInfoError) {
      addressFormInlineErrorScroll();
      return;
    }

    showFullScreenLoader();
    const { contactInfo: { email } } = this.context;
    const name = e.target.elements.fullname.value.trim();
    const { firstname, lastname } = extractFirstAndLastName(name);

    const formData = {
      static: {
        firstname,
        lastname,
        email: drupalSettings.user.uid > 0 ? email : e.target.elements.email.value,
        telephone: `+${drupalSettings.country_mobile_code}${cleanMobileNumber(e.target.elements.mobile.value)}`,
        country_id: drupalSettings.country_code,
      },
      shipping_type: 'click_and_collect',
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
      fullname: {
        firstname: formData.static.firstname,
        lastname: formData.static.lastname,
      },
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

        // If invalid full name.
        if (result.data.fullname === false) {
          document.getElementById('fullname-error').innerHTML = getStringMessage('form_error_full_name');
          document.getElementById('fullname-error').classList.add('error');
          isError = true;
        } else {
          // Remove error class and any error message.
          document.getElementById('fullname-error').innerHTML = '';
          document.getElementById('fullname-error').classList.remove('error');
        }

        // If invalid mobile number.
        if (result.data.mobile === false) {
          document.getElementById('mobile-error').innerHTML = getStringMessage('form_error_valid_mobile_number');
          document.getElementById('mobile-error').classList.add('error');
          isError = true;
        } else {
          // Remove error class and any error message.
          document.getElementById('mobile-error').innerHTML = '';
          document.getElementById('mobile-error').classList.remove('error');
        }

        if (result.data.email !== undefined) {
          if (result.data.email === 'invalid') {
            document.getElementById('email-error').innerHTML = getStringMessage('form_error_email_not_valid', { '%mail': validationData.email });
            document.getElementById('email-error').classList.add('error');
            isError = true;
          } else if (result.data.email === 'exists') {
            document.getElementById('email-error').innerHTML = getStringMessage('form_error_customer_exists');
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
      Drupal.logJavascriptError('Process shipping update', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
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
        .then((cartResult) => {
          removeFullScreenLoader();

          if (!cartResult) {
            return null;
          }

          // If error.
          if (cartResult.error) {
            dispatchCustomEvent('addressPopUpError', {
              type: 'error',
              message: cartResult.error_message,
              showDismissButton: false,
            });
            Drupal.logJavascriptError('update-shipping', cartResult.error_message, GTM_CONSTANTS.CHECKOUT_ERRORS);
            return null;
          }
          if (typeof cartResult.response_message !== 'undefined'
              && cartResult.response_message.status !== 'success') {
            dispatchCustomEvent('addressPopUpError', {
              type: 'error',
              message: cartResult.response_message.msg,
              showDismissButton: false,
            });
            Drupal.logJavascriptError('update-shipping', cartResult.response_message.msg, GTM_CONSTANTS.CHECKOUT_ERRORS);
            return null;
          }
          updateContactInfo(formData.static);
          dispatchCustomEvent('refreshCartOnCnCSelect', { cart: cartResult });
          return null;
        })
        .catch((error) => {
          Drupal.logJavascriptError('update-shipping', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
        });
    }
  };

  render() {
    const {
      store, subTitle, handleScrollTo, errorSuccessMessage,
    } = this.props;
    const { contactInfo } = this.context;

    return (
      <form
        className="spc-contact-form"
        onSubmit={(e) => this.handleSubmit(e, store, handleScrollTo, errorSuccessMessage)}
      >
        <FixedFields
          showEmail={drupalSettings.user.uid === 0}
          defaultVal={contactInfo ? { static: contactInfo } : []}
          subTitle={subTitle}
          type="cnc"
        />
        <div className="spc-address-form-actions">
          {collectionPointsEnabled() === true
          && (
            <div className="spc-cnc-store-actions-pudo-msg">
              {getStringMessage('cnc_valid_govtid_message')}
            </div>
          )}
          <button
            id="save-address"
            className="spc-address-form-submit"
            type="submit"
          >
            {Drupal.t('Continue')}
          </button>
        </div>
      </form>
    );
  }
}

export default ContactInfoForm;
