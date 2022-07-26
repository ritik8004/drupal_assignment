import React from 'react';
import getStringMessage from '../../../../../../../js/utilities/strings';
import AuraSendOTP from '../aura-send-otp';

class AuraRedeemPoints extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      redeemPoints: false,
      customerVerified: false,
    };
  }

  componentDidMount() {
    document.addEventListener('onCustomerVerification', this.updateRedeemFormStatus, false);
  }

  updateRedeemFormStatus = (e) => {
    if (e.detail) {
      this.setState({
        customerVerified: true,
      });
    }
  }

  redeemPoints = () => {
    this.setState({
      redeemPoints: true,
    });
  }

  render() {
    const {
      redeemPoints, customerVerified,
    } = this.state;

    const { mobile } = this.props;

    return (
      <>
        {!customerVerified
        && (
        <div className="aura-redeem-points">
          {!redeemPoints
            && (
            <div className="aura-redeem-button">
              { getStringMessage('redeem_points_message') }
              <button
                type="submit"
                className="spc-aura-redeem-card spc-aura-button"
                onClick={() => this.redeemPoints()}
              >
                { getStringMessage('redeem_points_button') }
              </button>
            </div>
            )}
          {redeemPoints
            && (
            <AuraSendOTP
              mobile={mobile}
            />
            )}
        </div>
        )}
        {/* @todo Work on aura redeeem form later. */}
        {customerVerified
        && <div className="aura-redeem-form" />}
      </>
    );
  }
}

export default AuraRedeemPoints;
