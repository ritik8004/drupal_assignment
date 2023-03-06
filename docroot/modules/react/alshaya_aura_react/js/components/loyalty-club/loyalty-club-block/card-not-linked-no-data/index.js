import React from 'react';
import Popup from 'reactjs-popup';
import AuraLogo from '../../../../svg-component/aura-logo';
import ConditionalView
  from '../../../../../../alshaya_spc/js/common/components/conditional-view';
import AuraFormSignUpOTPModal
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-otp-modal-form';
import AuraFormNewAuraUserModal
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-new-aura-user-form';
import AuraFormLinkCardOTPModal
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-link-card-otp-modal-form';
import { isUserAuthenticated } from '../../../../../../js/utilities/helper';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { isMyAuraContext } from '../../../../utilities/aura_utils';

class AuraMyAccountNoLinkedCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isOTPModalOpen: false,
      isNewUserModalOpen: false,
      chosenCountryCode: null,
      chosenUserMobile: null,
      isLinkCardModalOpen: false,
    };
  }

  setChosenCountryCode = (code) => {
    this.setState({
      chosenCountryCode: code,
    });
  };

  setChosenUserMobile = (code) => {
    this.setState({
      chosenUserMobile: code,
    });
  };

  /**
   * Toggles OTP/Join now Modal visibility based on passed value.
   *
   * @param toggle
   *   True will show the OTP/Join now Modal.
   *   And hide the Link card Modal.
   */
  toggleOTPModal = (toggle) => {
    this.setState({
      isOTPModalOpen: toggle,
      isLinkCardModalOpen: !toggle,
    });
  };

  /**
   * Toggles Link card Modal visibility based on passed value.
   *
   * @param toggle
   *   True will show the link card Modal.
   *   And hide the New User Modal.
   */
  toggleLinkCardModal = (toggle) => {
    this.setState({
      isLinkCardModalOpen: toggle,
      isNewUserModalOpen: !toggle,
    });
  };

  /**
   * Toggles new user Modal visibility based on passed value.
   *
   * @param toggle
   *   True will show the new user Modal.
   */
  toggleNewUserModal = (toggle) => {
    this.setState({
      isNewUserModalOpen: toggle,
    });
  };

  render() {
    const {
      isOTPModalOpen,
      isNewUserModalOpen,
      chosenCountryCode,
      chosenUserMobile,
      isLinkCardModalOpen,
    } = this.state;

    return (
      <div className="aura-myaccount-no-linked-card-wrapper no-card-found fadeInUp">
        <div className="aura-logo">
          {isMyAuraContext() && (
            <>
              <ConditionalView condition={window.innerWidth > 1024}>
                <AuraLogo stacked="vertical" />
              </ConditionalView>
              <ConditionalView condition={window.innerWidth < 1025}>
                <AuraLogo stacked="horizontal" />
              </ConditionalView>
            </>
          )}
          {(!hasValue(drupalSettings.aura.context)) && (
            <AuraLogo />
          )}
        </div>
        <div className="aura-myaccount-no-linked-card-description no-card-found">
          {isMyAuraContext() && (
            <div className="banner-title">
              { Drupal.t('Join Aura to earn and spend points while you shop and enjoy exclusive benefits.') }
            </div>
          )}
          <div className="action-wrapper">
            <ConditionalView condition={isUserAuthenticated()}>
              <div className="link-your-card">
                { Drupal.t('Already an Aura member?') }
                <div
                  className="btn"
                  onClick={() => {
                    this.toggleLinkCardModal(true);
                    Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_IN_ALREADY_MEMBER', label: 'initiated' });
                  }}
                >
                  { Drupal.t('Link your account') }
                </div>
              </div>
            </ConditionalView>
            <div className="sign-up">
              { Drupal.t('Ready to be rewarded?') }
              <div
                className="btn"
                onClick={() => {
                  this.toggleOTPModal(true);
                  Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_UP', label: 'initiated' });
                }}
              >
                { Drupal.t('Join now') }
              </div>
              <Popup
                className="aura-modal-form otp-modal"
                open={isOTPModalOpen}
                closeOnEscape={false}
                closeOnDocumentClick={false}
              >
                <AuraFormSignUpOTPModal
                  closeOTPModal={() => this.setState({
                    isOTPModalOpen: false,
                  })}
                  openNewUserModal={() => this.toggleNewUserModal(true)}
                  setChosenCountryCode={this.setChosenCountryCode}
                  setChosenUserMobile={this.setChosenUserMobile}
                  chosenCountryCode={chosenCountryCode}
                />
              </Popup>
              <Popup
                className="aura-modal-form new-aura-user"
                open={isNewUserModalOpen}
                closeOnEscape={false}
                closeOnDocumentClick={false}
              >
                <AuraFormNewAuraUserModal
                  chosenCountryCode={chosenCountryCode}
                  chosenUserMobile={chosenUserMobile}
                  closeNewUserModal={() => this.toggleNewUserModal(false)}
                  openLinkCardModal={() => this.toggleLinkCardModal(true)}
                />
              </Popup>
              <Popup
                className="aura-modal-form link-card-otp-modal"
                open={isLinkCardModalOpen}
                closeOnEscape={false}
                closeOnDocumentClick={false}
              >
                <AuraFormLinkCardOTPModal
                  closeLinkCardOTPModal={() => this.setState({
                    isLinkCardModalOpen: false,
                  })}
                  setChosenCountryCode={this.setChosenCountryCode}
                  chosenCountryCode={chosenCountryCode}
                  openOTPModal={() => this.toggleOTPModal(true)}
                />
              </Popup>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
export default AuraMyAccountNoLinkedCard;
