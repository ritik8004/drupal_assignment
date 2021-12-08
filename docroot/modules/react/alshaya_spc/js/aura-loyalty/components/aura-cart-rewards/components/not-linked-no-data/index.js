import React from 'react';
import PointsToEarnMessage from '../../../utilities/points-to-earn';
import { handleSignUp } from '../../../../../../../alshaya_aura_react/js/utilities/cta_helper';
import SignUpOtpModal from '../../../../../../../alshaya_aura_react/js/components/header/sign-up-otp-modal';
import ToolTip from '../../../../../utilities/tooltip';
import getStringMessage from '../../../../../utilities/strings';

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
          <PointsToEarnMessage
            pointsToEarn={pointsToEarn}
            loyaltyStatus={loyaltyStatus}
            wait={wait}
          />
          <div className="actions">
            <div className="spc-join-aura-link-wrapper submit">
              <a
                className="spc-join-aura-link"
                onClick={() => this.openOTPModal()}
              >
                {Drupal.t('Join now')}
              </a>
              <ToolTip enable question>{ getStringMessage('checkout_earn_and_redeem_tooltip') }</ToolTip>
            </div>
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
