import React from 'react';
import parse from 'html-react-parser';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';
import AuraMobileNumberField from '../aura-mobile-number-field';
import { showError } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../utilities/strings';
import { getAuraConfig, getUserDetails } from '../../../../../../alshaya_aura_react/js/utilities/helper';
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
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { isUserAuthenticated } from '../../../../../../js/utilities/helper';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';

class AuraFormNewAuraUserModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      messageType: null,
      messageContent: null,
      // Flag to determine if we have to show "Already a member?" link or not.
      // We only show this link in popup,
      // - If user is an authenticate
      // - Guest user has an active cart
      showAlreadyMember: false,
    };
  }

  componentDidMount() {
    // Check if user is authenticated then show "Already a member?" link.
    if (isUserAuthenticated()) {
      this.setState({
        showAlreadyMember: true,
      });
    } else {
      // If user is a guest user and user has cart then show "Already a member?"
      // link.
      const userHasCart = window.commerceBackend.getCartId();
      if (hasValue(userHasCart)) {
        this.setState({
          showAlreadyMember: true,
        });
      }
    }
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
    if (!validateElementValueByType('signUpMobile')) {
      hasError = true;
    }

    // Validate full name.
    if (!validateElementValueByType('fullName')) {
      hasError = true;
    } else {
      Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_UP', label: 'name' });
    }

    // Validate email.
    if (!validateElementValueByType('signUpEmail')) {
      hasError = true;
    } else {
      Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_UP', label: 'email' });
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

    const splitName = getElementValueByType('fullName').split(' ');
    const data = {
      firstname: splitName[0],
      lastname: splitName[1],
      email: getElementValueByType('signUpEmail'),
      mobile: `+${this.getCountryMobileCode()}${getElementValueByType('signUpMobile')}`,
    };
    const apiData = window.auraBackend.loyaltyClubSignUp(data);
    showFullScreenLoader();

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined) {
          if (result.data.error === undefined) {
            // Once we get a success response that quick enrollment is done, we close the modal.
            if (result.data.status) {
              handleSignUp(result.data);
              try {
                // Update localstorage with the latest aura details before pushing success event.
                Drupal.alshayaSeoGtmPushAuraCommonData(
                  {
                    tier: result.data.data.tier_code || '',
                    points: result.data.data.apc_points || 0,
                  },
                  parseInt(result.data.data.apc_link || '0', 10),
                  false,
                );
                // Push success event.
                Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_UP', label: 'success' });
              } catch (e) {
                Drupal.logJavascriptError('error-push-aura-sign-up-event', e);
              }
              // Close the modals.
              closeNewUserModal();
            }
            removeFullScreenLoader();
            return;
          }

          Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_UP', label: 'fail' });

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

          let message = getStringMessage(result.data.error_message);
          message = hasValue(message) ? message : getStringMessage('form_error_sign_up_failed_message');
          this.setState({
            messageType: 'error',
            messageContent: message,
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
      openLinkCardModal,
    } = this.props;

    const {
      messageType,
      messageContent,
      showAlreadyMember,
    } = this.state;

    const submitButtonText = Drupal.t('Submit');

    const email = getUserDetails().email || '';
    const { signUpTermsAndConditionsLink } = getAuraConfig();

    return (
      <div className="aura-new-user-form">
        <div className="aura-modal-header">
          <SectionTitle>{Drupal.t('Join Aura', {}, { context: 'aura' })}</SectionTitle>
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
            <AuraMobileNumberField
              isDisabled
              name="new-aura-user"
              countryMobileCode={chosenCountryCode}
              defaultValue={chosenUserMobile}
            />
          </div>
          <div className="aura-modal-form-actions">
            <div className="aura-new-user-t-c aura-otp-submit-description">
              {parse(getStringMessage('tnc_description_text', {
                '!tncLink': signUpTermsAndConditionsLink,
              }))}
            </div>
            <div className="aura-modal-form-submit" onClick={() => this.registerUser()}>
              {submitButtonText}
            </div>
            <ConditionalView condition={hasValue(showAlreadyMember)}>
              <div className="aura-modal-form-footer">
                <div
                  className="already-a-member-link"
                  onClick={() => {
                    Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_IN_ALREADY_MEMBER', label: 'initiated' });
                    openLinkCardModal();
                  }}
                >
                  {Drupal.t('Already a member?', {}, { context: 'aura' })}
                </div>
              </div>
            </ConditionalView>
          </div>
        </div>
      </div>
    );
  }
}

export default AuraFormNewAuraUserModal;
