import React from 'react'
import FixedFields from '../fixed-fields';
import Axios from 'axios';
import { addShippingInCart, showLoader, removeLoader } from '../../../utilities/checkout_util';
import { ClicknCollectContext } from '../../.../../../context/ClicknCollect';

class ContactInfoForm extends React.Component {
  static contextType = ClicknCollectContext;

  handleSubmit = (e, store) => {
    e.preventDefault();
    showLoader();
    let form_data = {
      static: null,
      shipping_type: 'cnc',
      store: store,
      carrier_info: { ...drupalSettings.cnc_shipping },
    };
    form_data.static = {
      firstname: e.target.elements.fname.value,
      lastname: e.target.elements.lname.value,
      email: e.target.elements.email.value,
      city: '',
      telephone: e.target.elements.mobile.value,
      country_id: window.drupalSettings.country_code
    };

    this.processShippingUpdate(form_data);
  }

  processShippingUpdate = (form_data) => {
    const mobileValidationRequest = Axios.get('verify-mobile/' + form_data.static.telephone);
    const customerValidationReuest = Axios.get(window.drupalSettings.alshaya_spc.middleware_url + '/customer/' + form_data.static.email);

    // API call to validate mobile number and email address.
    return Axios.all([mobileValidationRequest, customerValidationReuest])
      .then(
        Axios.spread(
          (mobileValidate, customerEmailValidate) => {
            // Show errors if any, else call update cart api to update shipping address.
            let hasError = this.showMobileAndEmailErrors(mobileValidate, customerEmailValidate);
            if (!hasError) {
              this.updateShipping(form_data);
            }
            else {
              removeLoader();
            }
          }
        )
      ).catch(errors => {
        // React on errors.
      });
  }

  /**
   * Show errors if mobile number and customer email is not vaild.
   */
  showMobileAndEmailErrors = (mobileValidate, customerEmailValidate) => {
    // Flag to determine if there any error.
    let isError = false;

    // If invalid mobile number.
    if (mobileValidate.data.status === false) {
      document.getElementById('mobile-error').innerHTML = Drupal.t('Please enter valid mobile number.');
      document.getElementById('mobile-error').classList.add('error');
      isError = true;
    }
    else {
      // Remove error class and any error message.
      document.getElementById('mobile-error').innerHTML = '';
      document.getElementById('mobile-error').classList.remove('error');
    }

    if (customerEmailValidate.data.exists === 'wrong') {
      document.getElementById('email-error').innerHTML = Drupal.t('The email address %mail is not valid.', {'%mail': customerEmailValidate.data.email});
      document.getElementById('email-error').classList.add('error');
      isError = true;
    }
    else if (customerEmailValidate.data.exists === true) {
      document.getElementById('email-error').innerHTML = Drupal.t('Customer already exists.');
      document.getElementById('email-error').classList.add('error');
      isError = true;
    }
    else {
      document.getElementById('email-error').innerHTML = '';
      document.getElementById('email-error').classList.remove('error');
    }

    return isError;
  }

  /**
   * Update cart with shipping address.
   */
  updateShipping = (form_data) => {
    var cart_info = addShippingInCart('update shipping', form_data);
    if (cart_info instanceof Promise) {
      let { updateContactInfo } = this.context;
      cart_info.then((cart_result) => {
        updateContactInfo(form_data.static);
        let cart_data = {
          'cart': cart_result,
          'delivery_type': cart_result.delivery_method,
          'address': form_data.store.address
        }
        var event = new CustomEvent('refreshCartOnCnCSelect', {
          bubbles: true,
          detail: {
            data: () => cart_data
          }
        });
        document.dispatchEvent(event);
        removeLoader();
      });
    }
  }

  render() {
    let {store} = this.props;
    let {contactInfo} = this.context;

    return (
      <form className='spc-contact-form' onSubmit={(e) => this.handleSubmit(e, store)}>
        <FixedFields showEmail={true} default_val={ contactInfo ? {static: contactInfo} : []} />
        <div className='spc-address-form-actions'>
          <button id='save-address' className='spc-address-form-submit' type="submit">{Drupal.t('Save')}</button>
        </div>
      </form>
    );
  }
}

export default ContactInfoForm;
