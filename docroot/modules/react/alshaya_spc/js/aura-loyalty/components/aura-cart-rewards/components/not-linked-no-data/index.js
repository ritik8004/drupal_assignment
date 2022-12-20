import React from 'react';
import Popup from 'reactjs-popup';
import PointsToEarnMessage from '../../../utilities/points-to-earn';
import { handleSignUp } from '../../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import SignUpOtpModal from '../../../../../../../alshaya_aura_react/js/components/header/sign-up-otp-modal';
import ToolTip from '../../../../../utilities/tooltip';
import getStringMessage from '../../../../../utilities/strings';
import AuraHorizontalIcon from '../../../../../../../alshaya_aura_react/js/svg-component/aura-horizontal-icon';
import { isUserAuthenticated } from '../../../../../../../js/utilities/helper';
import ConditionalView from '../../../../../../../js/utilities/components/conditional-view';
import AuraFormLinkCardOTPModal from '../../../aura-forms/aura-link-card-otp-modal-form';

class AuraNotLinkedNoData extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isOTPModalOpen: false,
      isLinkCardModalOpen: false,
      chosenCountryCode: null,
    };
  }

  /**
   * Sets the Country code for Mobile field.
   */
  setChosenCountryCode = (code) => {
    this.setState({
      chosenCountryCode: code,
    });
  };

  /**
   * Toggles OTP Modal visibility based on passed value.
   *
   * @param toggle
   *   True will show the OTP Modal.
   *   And will hide Sign in/ Link Now Modal.
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
   */
  toggleLinkCardModal = (toggle) => {
    this.setState({
      isLinkCardModalOpen: toggle,
    });
  };

  render() {
    const {
      pointsToEarn,
      loyaltyStatus,
      wait,
    } = this.props;

    const {
      isOTPModalOpen,
      isLinkCardModalOpen,
      chosenCountryCode,
    } = this.state;

    return (
      <>
        <div className="block-content guest-user">
          <div className="spc-aura-cart-icon">
            <AuraHorizontalIcon />
          </div>
          <div className="spc-aura-cart-content">
            <span className="spc-join-aura-link-wrapper submit">
              <a
                className="spc-join-aura-link"
                onClick={() => {
                  this.toggleOTPModal(true);
                  Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_UP', label: 'initiated' });
                }}
              >
                {getStringMessage('aura_join_aura')}
              </a>
            </span>
            <span className="spc-aura-or-text">{getStringMessage('aura_or')}</span>
            <ConditionalView condition={isUserAuthenticated()}>
              <span className="spc-link-aura-link-wrapper submit">
                <a
                  className="spc-link-aura-link"
                  onClick={() => {
                    this.toggleLinkCardModal(true);
                    Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_LINK_YOUR_CARD', label: 'initiated' });
                  }}
                >
                  {getStringMessage('aura_link_aura')}
                </a>
              </span>
            </ConditionalView>
            <ConditionalView condition={!isUserAuthenticated()}>
              <span className="spc-link-aura-link-wrapper submit">
                <a
                  className="spc-link-aura-link"
                  onClick={() => {
                    this.toggleLinkCardModal(true);
                    Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_IN_ALREADY_MEMBER', label: 'initiated' });
                  }}
                >
                  {getStringMessage('aura_sign_in')}
                </a>
              </span>
            </ConditionalView>
            <div>
              <PointsToEarnMessage
                pointsToEarn={pointsToEarn}
                loyaltyStatus={loyaltyStatus}
                wait={wait}
              />
              <ToolTip enable question>{getStringMessage('checkout_earn_and_redeem_tooltip')}</ToolTip>
            </div>
          </div>
        </div>

        <SignUpOtpModal
          isOTPModalOpen={isOTPModalOpen}
          closeOTPModal={() => this.setState({
            isOTPModalOpen: false,
          })}
          handleSignUp={handleSignUp}
          openOTPModal={() => this.toggleOTPModal(true)}
        />
        <Popup
          className="aura-modal-form link-card-otp-modal"
          open={isLinkCardModalOpen}
          closeOnEscape={false}
          closeOnDocumentClick={false}
        >
          <AuraFormLinkCardOTPModal
            closeLinkCardOTPModal={() => this.toggleLinkCardModal(false)}
            openOTPModal={() => this.toggleOTPModal(true)}
            setChosenCountryCode={this.setChosenCountryCode}
            chosenCountryCode={chosenCountryCode}
          />
        </Popup>
      </>
    );
  }
}

export default AuraNotLinkedNoData;
