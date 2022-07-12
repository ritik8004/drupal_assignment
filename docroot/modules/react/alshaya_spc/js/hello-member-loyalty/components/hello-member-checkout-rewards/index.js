import React from 'react';
import { getHelloMemberCustomerData, getHelloMemberPointsToEarn } from '../../../../../alshaya_hello_member/js/src/hello_member_api_helper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';
import Loading from '../../../../../js/utilities/loading';
import SectionTitle from '../../../utilities/section-title';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import GuestUserLoyalty from './guest-user-loyalty';
import RegisteredUserLoyalty from './registered-user-loyalty';
import logger from '../../../../../js/utilities/logger';
import getCurrencyCode from '../../../../../js/utilities/util';

class HelloMemberLoyaltyOptions extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      hmPoints: null,
    };
  }

  componentDidMount() {
    // For guest user, we calculate hello member points earned from item price
    // and accrual ratio provided by dictonary api.
    // For registered user, we get hello member points earned from the api.
    if (!isUserAuthenticated()) {
      this.getHelloMemberPoints();
    } else {
      const hmCustomerData = getHelloMemberCustomerData();
      if (hmCustomerData instanceof Promise) {
        hmCustomerData.then((response) => {
          if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)) {
            // If we have apc customer data,
            // we get hello member points which can be earned by customer.
            this.getHelloMemberPoints(response.data.apc_identifier_number);
          } else if (hasValue(response.error)) {
            logger.error('Error while trying to get hello member customer data. Data: @data.', {
              '@data': JSON.stringify(response),
            });
          }
        });
      }
    }
  }

  /**
   * Call helper to invoke hello member points sales API.
   *
   * @param {string} identifierNo
   *  Customer identifier number.
   */
  getHelloMemberPoints = (identifierNo) => {
    const {
      cart: { cart: { items } },
    } = this.props;
    let hmPoints = null;
    const currencyCode = getCurrencyCode();
    if (hasValue(currencyCode)) {
      const hmPointsData = getHelloMemberPointsToEarn(items, identifierNo, currencyCode);
      if (hmPointsData instanceof Promise) {
        hmPointsData.then((response) => {
          if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)) {
            if (hasValue(response.data.hm_points)) {
              hmPoints = response.data.hm_points;
            }
          } else if (hasValue(response.error)) {
            logger.error('Error while trying to get hello member points data. Data: @data.', {
              '@data': JSON.stringify(response),
            });
          }
          this.setState({
            hmPoints,
            wait: false,
          });
        });
      }
    }
  }

  render() {
    const { wait, hmPoints } = this.state;
    const { animationDelay } = this.props;

    if (!hasValue(hmPoints)) {
      return null;
    }

    if (wait) {
      return (
        <div className="spc-hello-member-checkout-rewards-block fadeInUp">
          <Loading />
        </div>
      );
    }

    return (
      <div className="spc-hello-member-checkout-rewards-block fadeInUp">
        <SectionTitle animationDelayValue={animationDelay}>{Drupal.t('Loyalty')}</SectionTitle>
        <ConditionalView condition={!isUserAuthenticated()}>
          <GuestUserLoyalty
            animationDelay={animationDelay}
            helloMemberPoints={hmPoints}
          />
        </ConditionalView>
        <ConditionalView condition={isUserAuthenticated()}>
          <RegisteredUserLoyalty
            animationDelay={animationDelay}
            helloMemberPoints={hmPoints}
          />
        </ConditionalView>
      </div>
    );
  }
}

export default HelloMemberLoyaltyOptions;
