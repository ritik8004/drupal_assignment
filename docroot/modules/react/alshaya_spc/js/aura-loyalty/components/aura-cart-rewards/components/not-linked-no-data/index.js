import React from 'react';
import PointsToEarnMessage from '../../../utilities/points-to-earn';
import { handleSignUp } from '../../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import SignUpOtpModal from '../../../../../../../alshaya_aura_react/js/components/header/sign-up-otp-modal';
import ToolTip from '../../../../../utilities/tooltip';
import getStringMessage from '../../../../../utilities/strings';
import AuraHeaderIcon from '../../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';
import { isUserAuthenticated } from '../../../../../../../js/utilities/helper';
import ConditionalView from '../../../../../../../js/utilities/components/conditional-view';

class AuraNotLinkedNoData extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isOTPModalOpen: false,
    };
  }

  openOTPModal = () => {
    this.setState({
      isOTPModalOpen: true,
    });
  };

  closeOTPModal = () => {
    this.setState({
      isOTPModalOpen: false,
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
            <br />
            <PointsToEarnMessage
              pointsToEarn={pointsToEarn}
              loyaltyStatus={loyaltyStatus}
              wait={wait}
            />
            <ToolTip enable question>{getStringMessage('checkout_earn_and_redeem_tooltip')}</ToolTip>
          </div>
        </div>

        <SignUpOtpModal
          isOTPModalOpen={isOTPModalOpen}
          closeOTPModal={this.closeOTPModal}
          handleSignUp={handleSignUp}
        />
      </>
    );
  }
}

export default AuraNotLinkedNoData;
