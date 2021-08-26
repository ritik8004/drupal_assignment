import React from 'react';
import { ClicknCollectContext } from '../../../context/ClicknCollect';
import {
  addShippingInCart, cleanMobileNumber,
  removeFullScreenLoader,
  showFullScreenLoader, validateInfo,
} from '../../../utilities/checkout_util';
import FixedFields from '../fixed-fields';
import PudoCollectorFields from '../pudo-collector-fields';
import { validateContactInfo, addressFormInlineErrorScroll } from '../../../utilities/address_util';
import { extractFirstAndLastName } from '../../../utilities/cart_customer_util';
import dispatchCustomEvent from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';
import { collectionPointsEnabled } from '../../../utilities/cnc_util';

class ContactInfoForm extends React.Component {
  static contextType = ClicknCollectContext;

  handleSubmit = (e, store) => {
    const { showCollectorForm } = this.context;
    e.preventDefault();

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

    // If collection point feature is enabled, add collectors information.
    if (collectionPointsEnabled() && showCollectorForm === true) {
      const collectorName = extractFirstAndLastName(
        e.target.elements.collectorFullname.value.trim(),
      ) || null;
      formData.static.collector_firstname = collectorName.firstname || '';
      formData.static.collector_lastname = collectorName.lastname || '';
      formData.static.collector_email = e.target.elements.collectorEmail.value || '';
      formData.static.collector_telephone = e.target.elements.collectorMobile.value || '';
    }

    this.processShippingUpdate(formData);
  };

  /**
   * Validate mobile number and email address and on success process shipping address update.
   */
  processShippingUpdate = (formData) => {
    const { showCollectorForm } = this.context;
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

    // If collection point feature is enabled, validate collector's information.
    if (collectionPointsEnabled() && showCollectorForm === true) {
      validationData.pudo_collector_tel = formData.static.collector_telephone;
      validationData.pudo_collector_email = formData.static.collector_email;
      validationData.pudo_fullname = {
        firstname: formData.static.collector_firstname,
        lastname: formData.static.collector_lastname,
      };
    }

    const validationRequest = validateInfo(validationData);
    // API call to validate mobile number and email address.
    return validationRequest.then((result) => {
      if (result.status === 200 && result.data.status) {
        // Show errors if any, else call update cart api to update shipping address.
        // Flag to determine if there any error.
        let isError = false;

        // Validate PUDO collector info.
        if (collectionPointsEnabled() && showCollectorForm === true) {
          // If invalid full name.
          if (result.data.pudo_fullname === false) {
            document.getElementById('collectorFullname-error').innerHTML = getStringMessage('form_error_full_name');
            document.getElementById('collectorFullname-error').classList.add('error');
            isError = true;
          } else {
            // Remove error class and any error message.
            document.getElementById('collectorFullname-error').innerHTML = '';
            document.getElementById('collectorFullname-error').classList.remove('error');
          }
          // If invalid email.
          if (result.data.pudo_collector_email !== undefined) {
            if (result.data.pudo_collector_email === 'invalid') {
              document.getElementById('collectorEmail-error').innerHTML = getStringMessage('form_error_email_not_valid', { '%mail': validationData.pudo_collector_email });
              document.getElementById('collectorEmail-error').classList.add('error');
              isError = true;
            } else {
              document.getElementById('collectorEmail-error').innerHTML = '';
              document.getElementById('collectorEmail-error').classList.remove('error');
            }
          }

          // If invalid mobile number.
          if (result.data.pudo_collector_tel === false) {
            document.getElementById('collectorMobile-error').innerHTML = getStringMessage('form_error_valid_collector_mobile_number');
            document.getElementById('collectorMobile-error').classList.add('error');
            isError = true;
          } else {
            // Remove error class and any error message.
            document.getElementById('collectorMobile-error').innerHTML = '';
            document.getElementById('collectorMobile-error').classList.remove('error');
          }
        }

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
      const { updateContactInfo, updateCollectorInfo, showCollectorForm } = this.context;
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

          // If collection point feature is enabled, update collector's information.
          if (collectionPointsEnabled() && showCollectorForm === true) {
            updateCollectorInfo(formData.static);
          }

          dispatchCustomEvent('refreshCartOnCnCSelect', { cart: cartResult });
          return null;
        })
        .catch((error) => {
          Drupal.logJavascriptError('update-shipping', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
        });
    }
  };

  render() {
    const { store, subTitle } = this.props;
    const {
      contactInfo, showCollectorForm, updateCollectorFormVisibility, collectorInfo,
    } = this.context;

    return (
      <form
        className="spc-contact-form"
        onSubmit={(e) => this.handleSubmit(e, store)}
      >
        <FixedFields
          showEmail={drupalSettings.user.uid === 0}
          defaultVal={contactInfo ? { static: contactInfo } : []}
          subTitle={subTitle}
          type="cnc"
          showCollectorForm={showCollectorForm}
          updateCollectorFormVisibility={updateCollectorFormVisibility}
        />
        {collectionPointsEnabled() === true
          && showCollectorForm === true
          && (
            <PudoCollectorFields
              defaultVal={collectorInfo ? { static: collectorInfo } : []}
              showCollectorForm={showCollectorForm}
            />
          )}
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
