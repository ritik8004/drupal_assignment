import React from 'react';
import parse from 'html-react-parser';
import getStringMessage from '../../../utilities/strings';
import TextField from '../../../utilities/textfield';
import { cleanMobileNumber } from '../../../utilities/checkout_util';

class PaymentMethodCheckoutComUpapiFawry extends React.Component {
  componentDidMount() {
    // We dont need error validations so remove the error classes.
    document.getElementById('fawry-email-error').classList.remove('error');
    document.getElementById('fawry-mobile-number-error').classList.remove('error');
  }

  render() {
    // Get email and mobile number from billing address.
    let emailAddress;
    let mobileNumber;
    const {
      cart: {
        cart: {
          billing_address: {
            email,
            telephone,
          },
        },
      },
    } = this.props;
    emailAddress = email;
    mobileNumber = telephone;

    // If user is authenticated user then get
    // email and mobile number from profile.
    const { isCustomer, uid } = drupalSettings.user;
    if (isCustomer && uid !== undefined && uid > 0) {
      const { userEmailID, userPhone } = drupalSettings.userDetails;
      emailAddress = (userEmailID !== '') ? userEmailID : email;
      mobileNumber = (userPhone !== '') ? userPhone : telephone;
    }

    return (
      <div className="payment-form-wrapper">
        <div className="fawry-prefix-description">
          {getStringMessage('fawry_payment_option_prefix_description')}
        </div>
        <TextField
          type="email"
          name="fawry-email"
          disabled
          defaultValue={emailAddress !== '' ? emailAddress : ''}
          className={emailAddress !== '' && emailAddress !== '' ? 'focus' : ''}
          label={getStringMessage('fawry_email_label')}
        />
        <TextField
          type="tel"
          name="fawry-mobile-number"
          disabled
          defaultValue={mobileNumber !== '' ? cleanMobileNumber(mobileNumber) : ''}
          className={mobileNumber !== '' && mobileNumber !== '' ? 'focus' : ''}
          label={getStringMessage('fawry_mobile_number')}
        />
        <div className="fawry-suffix-description">
          {parse(getStringMessage('fawry_payment_option_suffix_description'))}
        </div>
      </div>
    );
  }
}

export default PaymentMethodCheckoutComUpapiFawry;
