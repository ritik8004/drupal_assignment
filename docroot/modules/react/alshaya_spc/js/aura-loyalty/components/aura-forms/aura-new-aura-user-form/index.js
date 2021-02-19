import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';
import AuraMobileNumberField from '../aura-mobile-number-field';
import { showError } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../utilities/strings';
import { getAuraConfig, getUserDetails } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import { postAPIData } from '../../../../../../alshaya_aura_react/js/utilities/api/fetchApiData';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import AuraFormModalMessage from '../aura-form-modal-message';
import { handleSignUp } from '../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import {
  getElementValueByType,
  getInlineErrorSelector,
} from '../../utilities/link_card_sign_up_modal_helper';
import { validateElementValueByType } from '../../utilities/validation_helper';

class AuraFormNewAuraUserModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      messageType: null,
      messageContent: null,
    };
  }

  getNewUserFormDescription = () => {
    const { signUpTermsAndConditionsLink } = getAuraConfig();

    return [
      <span key="part1">{Drupal.t('By clicking submit, you agree to have read and accepted our')}</span>,
      <a key="part2" href={signUpTermsAndConditionsLink} className="t-c-link">{Drupal.t('Terms & Conditions')}</a>,
    ];
  }

  getCountryMobileCode = () => {
    const {
      country_mobile_code: countryMobileCode,
    } = getAuraConfig();

    return countryMobileCode;
  }

  // Validate enrollment data.
  validateEnrollmentData = () => {
    let hasError = false;

    // Validate mobile.
    if (validateElementValueByType('signUpMobile') === false) {
      hasError = true;
    }

    // Validate full name.
    if (validateElementValueByType('fullName') === false) {
      hasError = true;
    }

    // Validate email.
    if (validateElementValueByType('signUpEmail') === false) {
      hasError = true;
    }

    return hasError;
  };

  registerUser = () => {
    const {
      closeNewUserModal,
    } = this.props;

    const hasError = this.validateEnrollmentData();

    if (hasError) {
      return;
    }

    // API call to do quick enrollment.
    const apiUrl = 'post/loyalty-club/sign-up';
    const splitName = getElementValueByType('fullName').split(' ');
    const data = {
      firstname: splitName[0],
      lastname: splitName[1],
      email: getElementValueByType('signUpEmail'),
      mobile: `+${this.getCountryMobileCode()}${getElementValueByType('signUpMobile')}`,
    };
    const apiData = postAPIData(apiUrl, data);
    showFullScreenLoader();

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined) {
          if (result.data.error === undefined) {
            // Once we get a success response that quick enrollment is done, we close the modal.
            if (result.data.status) {
              handleSignUp(result.data);
              // Close the modals.
              closeNewUserModal();
            }
            removeFullScreenLoader();
            return;
          }

          if (result.data.error_code === 'mobile_already_registered') {
            showError(getInlineErrorSelector('signUpMobile').signUpMobile, getStringMessage(result.data.error_message));
            removeFullScreenLoader();
            return;
          }

          if (result.data.error_code === 'email_already_registered') {
            showError(getInlineErrorSelector('signUpEmail').signUpEmail, getStringMessage(result.data.error_message));
            removeFullScreenLoader();
            return;
          }

          this.setState({
            messageType: 'error',
            messageContent: result.data.error_message,
          });
          removeFullScreenLoader();
          return;
        }

        this.setState({
          messageType: 'error',
          messageContent: getStringMessage('form_error_sign_up_failed_message'),
        });

        removeFullScreenLoader();
      });
    }
  };

  render() {
    const {
      closeNewUserModal,
      chosenCountryCode,
      chosenUserMobile,
    } = this.props;

    const {
      messageType,
      messageContent,
    } = this.state;

    const submitButtonText = Drupal.t('Submit');

    const email = getUserDetails().email || '';

    return (
      <div className="aura-new-user-form">
        <div className="aura-modal-header">
          <SectionTitle>{Drupal.t('Say hello to AURA')}</SectionTitle>
          <button type="button" className="close" onClick={() => closeNewUserModal()} />
        </div>
        <div className="aura-modal-form">
          <div className="aura-modal-form-items">
            <div className="aura-form-messages-container">
              <AuraFormModalMessage
                messageType={messageType}
                messageContent={messageContent}
              />
            </div>
            <AuraMobileNumberField
              isDisabled
              name="new-aura-user"
              countryMobileCode={chosenCountryCode}
              defaultValue={chosenUserMobile}
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
              defaultValue={email}
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
