import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../../../../js/utilities/strings';
import AuraFormRedeemPoints from '../../../../../aura-loyalty/components/aura-forms/aura-redeem-points';
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
    const { cart } = this.props;
    const { totals } = cart.cart;

    // If amount paid with aura is undefined or null, we calculate and
    // refill redemption input elements and return.
    if (!hasValue(totals.paidWithAura)) {
      return;
    }

    this.setState({
      customerVerified: true,
    });
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

    const {
      mobile, pointsInAccount, cardNumber, formActive, cart,
    } = this.props;

    return (
      <>
        {!customerVerified
        && (
        <div className="aura-redeem-points">
          {!redeemPoints
            && (
              <>
                <div className="redeem-msg">{ getStringMessage('redeem_points_message') }</div>
                <button
                  type="submit"
                  className="spc-aura-redeem-card spc-aura-button"
                  onClick={() => this.redeemPoints()}
                >
                  { getStringMessage('redeem_points_button') }
                </button>
              </>
            )}
          {redeemPoints
            && (
            <AuraSendOTP
              mobile={mobile}
            />
            )}
        </div>
        )}
        {customerVerified
          && (
          <AuraFormRedeemPoints
            context="hello_member"
            pointsInAccount={pointsInAccount}
            cardNumber={cardNumber}
            totals={cart.cart.totals}
            paymentMethodInCart={cart.cart.payment.method || ''}
            formActive={formActive}
          />
          )}
      </>
    );
  }
}

export default AuraRedeemPoints;
