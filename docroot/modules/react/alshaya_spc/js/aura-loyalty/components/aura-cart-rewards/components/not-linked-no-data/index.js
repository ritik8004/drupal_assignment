import React from 'react';
import Popup from 'reactjs-popup';
import PointsToEarnMessage from '../../../utilities/points-to-earn';
import { handleSignUp } from '../../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import SignUpOtpModal from '../../../../../../../alshaya_aura_react/js/components/header/sign-up-otp-modal';
import ToolTip from '../../../../../utilities/tooltip';
import getStringMessage from '../../../../../utilities/strings';
import AuraHeaderIcon from '../../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';
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

  setChosenCountryCode = (code) => {
    this.setState({
      chosenCountryCode: code,
    });
  };

  openOTPModal = () => {
    const { isLinkCardModalOpen } = this.state;
    // Close link card modal if open.
    if (isLinkCardModalOpen) {
      this.setState({
        isLinkCardModalOpen: false,
      });
    }
    this.setState({
      isOTPModalOpen: true,
    });
  };

  closeOTPModal = () => {
    this.setState({
      isOTPModalOpen: false,
    });
  };

  openLinkCardModal = () => {
    this.setState({
      isLinkCardModalOpen: true,
    });
  };

  closeLinkCardModal = () => {
    this.setState({
      isLinkCardModalOpen: false,
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
            <AuraHeaderIcon />
          </div>
          <div className="spc-aura-cart-content">
            <span className="spc-join-aura-link-wrapper submit">
              <a
                className="spc-join-aura-link"
                onClick={() => this.openOTPModal()}
              >
                {getStringMessage('aura_join_aura')}
              </a>
            </span>
            <span className="spc-aura-or-text">{getStringMessage('aura_or')}</span>
            <ConditionalView condition={isUserAuthenticated()}>
              <span className="spc-link-aura-link-wrapper submit">
                <a
                  className="spc-link-aura-link"
                /** @todo: We need to change this to open the link aura form. */
                  onClick={() => this.openOTPModal()}
                >
                  {getStringMessage('aura_link_aura')}
                </a>
              </span>
            </ConditionalView>
            <ConditionalView condition={!isUserAuthenticated()}>
              <span className="spc-link-aura-link-wrapper submit">
                <a
                  className="spc-link-aura-link"
                /** @todo: We need to change this to open sign in aura form. */
                  onClick={() => this.openOTPModal()}
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
            {/* @todo below change is temporary,*/}
            {/* should be removed while integration with new component.*/}
            <div className="spc-sign-in-wrapper submit">
              <a
                className="spc-sign-in-link"
                onClick={() => this.openLinkCardModal()}
              >
                {Drupal.t('Sign in')}
              </a>
            </div>
          </div>
        </div>

        <SignUpOtpModal
          isOTPModalOpen={isOTPModalOpen}
          closeOTPModal={this.closeOTPModal}
          handleSignUp={handleSignUp}
        />
        <Popup
          className="aura-modal-form link-card-otp-modal"
          open={isLinkCardModalOpen}
          closeOnEscape={false}
          closeOnDocumentClick={false}
        >
          <AuraFormLinkCardOTPModal
            closeLinkCardOTPModal={() => this.closeLinkCardModal()}
            openOTPModal={() => this.openOTPModal()}
            setChosenCountryCode={this.setChosenCountryCode}
            chosenCountryCode={chosenCountryCode}
            modalHeaderTitle={Drupal.t('Experience Aura')}
            modalBodyTitle={(
              <div className="modal-body-title">
                <span>{Drupal.t('Are you a member already?')}</span>
                <span>{Drupal.t('Enter your details to earn points for this order.')}</span>
              </div>
            )}
            linkCardWithoutOTP
            showJoinAuraLink
          />
        </Popup>
      </>
    );
  }
}

export default AuraNotLinkedNoData;
