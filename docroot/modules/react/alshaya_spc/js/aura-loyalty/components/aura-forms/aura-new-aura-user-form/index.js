import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';
import { getElementValue, showError, removeError } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../utilities/strings';
import { validateInfo } from '../../../../utilities/checkout_util';
import { getUserDetails, getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import { postAPIData } from '../../../../../../alshaya_aura_react/js/utilities/api/fetchApiData';

class AuraFormNewAuraUserModal extends React.Component {
  getNewUserFormDescription = () => [
    <span key="part1">{Drupal.t('By pressing submit, you agree to have read and accepted our')}</span>,
    <a key="part2" className="t-c-link">{Drupal.t('Terms & Conditions')}</a>,
  ];

  // Process enrollment data.
  processEnrollmentData = () => {
    let hasError = false;

    // Process full name.
    const fullname = getElementValue('new-aura-user-full-name');
    let splitedName = fullname.split(' ');
    splitedName = splitedName.filter((s) => (
      (s.trim().length > 0
      && (s !== '\\n' && s !== '\\t' && s !== '\\r'))));

    if (fullname.length === 0 || splitedName.length === 1) {
      showError('new-aura-user-full-name-error', getStringMessage('form_error_full_name'));
      hasError = true;
    } else {
      removeError('new-aura-user-full-name-error');
    }

    // Process email.
    const email = document.getElementsByName('new-aura-user-email')[0].value;
    if (email.length === 0) {
      showError('new-aura-user-email-error', getStringMessage('form_error_email'));
      hasError = true;
    } else {
      removeError('new-aura-user-email-error');
    }

    return hasError;
  };

  // Validate email.
  validateEmail = (email) => {
    let isValid = true;

    const validationRequest = validateInfo({ email });
    return validationRequest.then((result) => {
      if (result.status === 200 && result.data.status) {
        if (result.data.email !== undefined && result.data.email === 'invalid') {
          showError('new-aura-user-email-error', getStringMessage('form_error_email_not_valid', { '%mail': email }));
          isValid = false;
        } else {
          // If valid email, remove error message.
          removeError('new-aura-user-email-error');
        }
      }
      return isValid;
    });
  };

  registerUser = () => {
    const {
      closeNewUserModal,
      closeOTPModal,
    } = this.props;

    const hasError = this.processEnrollmentData();

    if (hasError) {
      return;
    }

    // Call API to check if email is valid.
    const email = document.getElementsByName('new-aura-user-email')[0].value;
    const validationRequest = this.validateEmail(email);
    if (validationRequest instanceof Promise) {
      validationRequest.then((valid) => {
        if (valid === true) {
          // API call to do quick enrollment.
          const fullname = getElementValue('new-aura-user-full-name');
          const splitedName = fullname.split(' ');
          const apiUrl = 'post/loyalty-club/quick-enrollment';
          const data = {
            uid: getUserDetails().id,
            firstname: splitedName[0],
            lastname: splitedName[1],
            email,
            mobile: document.getElementById('country_code').innerText + getElementValue('new-aura-user-mobile-number'),
          };
          const apiData = postAPIData(apiUrl, data);

          if (apiData instanceof Promise) {
            apiData.then((result) => {
              if (result.data !== undefined && result.data.error === undefined) {
                // Once we get a success response that quick enrollment is done, we close the modal.
                if (result.data.status) {
                  const { handleSignUp } = this.props;
                  handleSignUp();
                  // Close the modals.
                  closeNewUserModal();
                  closeOTPModal();
                }
              }
            });
          }
        }
      });
    }
  };

  render() {
    const {
      closeNewUserModal,
      mobileNumber,
    } = this.props;

    const {
      country_mobile_code: countryMobileCode,
    } = getAuraConfig();

    const countryMobileCodeMarkup = countryMobileCode
      ? (
        <span className="country-code" id="country_code">
          +
          {countryMobileCode}
        </span>
      )
      : '';

    const submitButtonText = Drupal.t('Submit');

    return (
      <div className="aura-new-user-form">
        <div className="aura-modal-header">
          <SectionTitle>{Drupal.t('Say hello to Aura')}</SectionTitle>
          <button type="button" className="close" onClick={() => closeNewUserModal()} />
        </div>
        <div className="aura-modal-form">
          <div className="aura-modal-form-items">
            {countryMobileCodeMarkup}
            <TextField
              type="text"
              required
              disabled
              name="new-aura-user-mobile-number"
              defaultValue={mobileNumber}
              label={Drupal.t('Mobile Number')}
            />
            <TextField
              type="text"
              required
              name="new-aura-user-full-name"
              label={Drupal.t('Full name')}
            />
            <TextField
              type="email"
              required
              name="new-aura-user-email"
              label={Drupal.t('Email address')}
            />
          </div>
          <div className="aura-modal-form-actions">
            <div className="aura-new-user-t-c aura-otp-submit-description">
              {this.getNewUserFormDescription()}
            </div>
            <div className="aura-modal-form-submit" onClick={() => this.registerUser()}>
              {submitButtonText}
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default AuraFormNewAuraUserModal;
