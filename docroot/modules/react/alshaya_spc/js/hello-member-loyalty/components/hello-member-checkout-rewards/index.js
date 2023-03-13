import React from 'react';
import { getHelloMemberCustomerData, getHelloMemberPointsToEarn } from '../../../../../js/utilities/helloMemberHelper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';
import Loading from '../../../../../js/utilities/loading';
import SectionTitle from '../../../utilities/section-title';
import GuestUserLoyalty from './guest-user-loyalty';
import RegisteredUserLoyalty from './registered-user-loyalty';
import logger from '../../../../../js/utilities/logger';
import getStringMessage from '../../../utilities/strings';

class HelloMemberLoyaltyOptions extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      hmPoints: null,
      identifierNo: null,
      errorResponse: false,
    };
  }

  async componentDidMount() {
    const { cart } = this.props;
    const {
      cart: {
        loyalty_card: loyaltyCard,
        loyalty_type: loyaltyType,
      },
    } = cart;
    // For guest user, we calculate hello member points earned from item price
    // and accrual ratio provided by dictonary api.
    // For registered user, we get hello member points earned from the api.
    // Skip get customer data api if identifier number already available in cart.
    let identifierNo = null;
    if (isUserAuthenticated()) {
      if (hasValue(loyaltyType) && hasValue(loyaltyCard) && loyaltyType === 'hello_member') {
        identifierNo = loyaltyCard;
      } else {
        const response = await getHelloMemberCustomerData();
        if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)) {
          // If we have apc customer data,
          // we get hello member points which can be earned by customer.
          identifierNo = response.data.apc_identifier_number;
        } else if (hasValue(response.error)) {
          // set errorResponse to true when CLM is down for registered users.
          this.setState({
            errorResponse: true,
          });
          logger.error('Error while trying to get hello member customer data. Data: @data.', {
            '@data': JSON.stringify(response),
          });
        }
      }
    }

    // Show hello member points on the basis of user logged in status.
    this.updateHelloMemberPoints(identifierNo);
    this.setState({
      identifierNo,
    });
  }

  /**
   * Call helper to invoke hello member points sales API.
   *
   * @param {string} identifierNo
   *  Customer identifier number.
   */
  updateHelloMemberPoints = async (identifierNo) => {
    const {
      cart: { cart: { items } },
    } = this.props;
    let hmPoints = null;
    const response = await getHelloMemberPointsToEarn(items, identifierNo);
    if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)) {
      if (hasValue(response.data.hm_points)) {
        hmPoints = response.data.hm_points;
      }
    } else if (hasValue(response.error)) {
      // set errorResponse to true when CLM is down for guest users.
      this.setState({
        errorResponse: true,
      });
      logger.error('Error while trying to get hello member points data. Data: @data.', {
        '@data': JSON.stringify(response),
      });
    }
    this.setState({
      hmPoints,
      wait: false,
    });
  }

  render() {
    const {
      wait, hmPoints, identifierNo, errorResponse,
    } = this.state;
    const { animationDelay, cart, refreshCart } = this.props;

    if (wait) {
      return (
        <div className="spc-hello-member-checkout-loading fadeInUp">
          <Loading />
        </div>
      );
    }

    if (errorResponse) {
      return null;
    }

    return (
      <div className="spc-hello-member-checkout-rewards-block fadeInUp">
        <SectionTitle animationDelayValue={animationDelay}>{getStringMessage('loyalty_title')}</SectionTitle>
        {!isUserAuthenticated()
          && (
          <GuestUserLoyalty
            animationDelay={animationDelay}
            helloMemberPoints={hmPoints}
            cart={cart}
          />
          )}
        {isUserAuthenticated()
          && (
          <RegisteredUserLoyalty
            identifierNo={identifierNo}
            cart={cart}
            animationDelay={animationDelay}
            helloMemberPoints={hmPoints}
            refreshCart={refreshCart}
          />
          )}
      </div>
    );
  }
}

export default HelloMemberLoyaltyOptions;
