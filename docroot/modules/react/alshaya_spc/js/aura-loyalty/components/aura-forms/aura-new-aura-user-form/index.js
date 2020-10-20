import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';
import { getElementValue, showError, removeError } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../utilities/strings';
import { validateInfo } from '../../../../utilities/checkout_util';
import { getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import { postAPIData } from '../../../../../../alshaya_aura_react/js/utilities/api/fetchApiData';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../../js/utilities/showRemoveFullScreenLoader';

class AuraFormNewAuraUserModal extends React.Component {
  getNewUserFormDescription = () => [
    <span key="part1">{Drupal.t('By pressing submit, you agree to have read and accepted our')}</span>,
    <a key="part2" className="t-c-link">{Drupal.t('Terms & Conditions')}</a>,
  ];

  getCountryMobileCode = () => {
    const {
      country_mobile_code: countryMobileCode,
    } = getAuraConfig();

    return countryMobileCode;
  }

  // Validate enrollment data.
  validateEnrollmentData = (enrollmentData) => {
    let hasError = false;

    // Validate mobile.
    if (enrollmentData.mobile.length === 0 || enrollmentData.mobile.match(/^[0-9]+$/) === null) {
      showError('new-aura-user-mobile-number', getStringMessage('form_error_mobile_number'));
      hasError = true;
    } else {
      removeError('new-aura-user-mobile-number');
    }

    // Validate full name.
    if (enrollmentData.fullname.length === 0) {
      showError('new-aura-user-full-name-error', getStringMessage('form_error_full_name'));
      hasError = true;
    } else {
      let splitedName = enrollmentData.fullname.split(' ');
      splitedName = splitedName.filter((s) => (
        (s.trim().length > 0
        && (s !== '\\n' && s !== '\\t' && s !== '\\r'))));

      if (splitedName.length === 1) {
        showError('new-aura-user-full-name-error', getStringMessage('form_error_full_name'));
        hasError = true;
      } else {
        removeError('new-aura-user-full-name-error');
      }
    }

    // Validate email.
    if (enrollmentData.email.length === 0 || enrollmentData.email.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i) === null) {
      showError('new-aura-user-email-error', getStringMessage('form_error_email'));
      hasError = true;
    } else {
      removeError('new-aura-user-email-error');
    }

    return hasError;
  };

  getEnrollmentData = () => {
    const enrollmentData = {
      fullname: getElementValue('new-aura-user-full-name'),
      mobile: getElementValue('new-aura-user-mobile-number'),
      email: document.getElementsByName('new-aura-user-email')[0].value,
    };

    return enrollmentData;
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

    const enrollmentData = this.getEnrollmentData();

    const hasError = this.validateEnrollmentData(enrollmentData);

    if (hasError) {
      return;
    }

    // API call to do quick enrollment.
    const apiUrl = 'post/loyalty-club/sign-up';
    const splitedName = enrollmentData.fullname.split(' ');
    const data = {
      firstname: splitedName[0],
      lastname: splitedName[1],
      email: enrollmentData.email,
      mobile: `+${this.getCountryMobileCode()}${enrollmentData.mobile}`,
    };
    const apiData = postAPIData(apiUrl, data);
    showFullScreenLoader();

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
        removeFullScreenLoader();
      });
    }
  };

  render() {
    const {
      closeNewUserModal,
      mobileNumber,
    } = this.props;

    const countryMobileCode = this.getCountryMobileCode();
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
