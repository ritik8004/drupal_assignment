import React from 'react';
import parse from 'html-react-parser';
import { renderToString } from 'react-dom/server';
import { callHelloMemberApi, isAuraIntegrationEnabled } from '../../../../../../js/utilities/helloMemberHelper';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import LoyaltySelectOption from '../loyalty-select-option';
import LoyaltyConfirmPopup from '../loyalty-confirm-popup';
import { setHelloMemberLoyaltyCard } from '../../../../../../alshaya_hello_member/js/src/hello_member_api_helper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import { isUserAuthenticated } from '../../../../../../js/utilities/helper';
import { redeemAuraPoints } from '../../../../aura-loyalty/components/utilities/checkout_helper';
import { getUserDetails } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import logger from '../../../../../../js/utilities/logger';
import { updatePriceSummaryBlock } from '../../../../utilities/egift_util';

class RegisteredUserLoyalty extends React.Component {
  constructor(props) {
    super(props);
    const {
      cart: {
        cart: {
          loyalty_type: loyaltyType,
        },
      },
    } = props;
    this.state = {
      currentOption: hasValue(loyaltyType) ? loyaltyType : 'hello_member',
      selectedOption: null,
      showLoyaltyPopup: false,
    };
  }

  componentDidMount() {
    // For registered user, we set loyalty card for the default option selected.
    // Currently, default option is hello_member
    // @todo: Update default option if cart has been updated.
    const { cart, identifierNo } = this.props;
    const {
      cart: {
        cart_id: cartId,
        loyalty_type: loyaltyType,
      },
    } = cart;
    // Set hello member loyalty when no loyalty is set in cart.
    if (!hasValue(loyaltyType) && hasValue(identifierNo)) {
      setHelloMemberLoyaltyCard(identifierNo, cartId);
    }
  }

  /**
   * Handles the state for loyalty popup block.
   *
   * @param {string} method
   *  Selected method by customer.
   */
  showLoyaltyPopup = (method) => {
    // @todo: Trigger a pop-up to confirm the loyalty option.
    // @todo: Refresh cart with the selected value.
    this.setState({
      showLoyaltyPopup: true,
      selectedOption: method,
    });
  }

  resetPopupStatus = (showLoyaltyPopup) => {
    this.setState({
      showLoyaltyPopup,
    });
  }

  /**
   * Handle change in loyalty options by customer.
   *
   * @param {string} selectedMethod
   *  Selected method by customer.
   */
  changeLoyaltyOption = (selectedMethod) => {
    // @todo: Trigger a pop-up to confirm the loyalty option.
    // @todo: Refresh cart with the selected value.
    let method = selectedMethod;
    const { cart, refreshCart, identifierNo } = this.props;
    const {
      cart: {
        cart_id: cartId,
        cart_id_int: cardIdInt,
        loyalty_card: loyaltyCard,
      },
    } = cart;
    // Unset the old loyalty card if customer switches loyalty options.s
    let requestData = {
      masked_quote_id: cartId,
    };
    // Change payload if authenticated user.
    if (isUserAuthenticated()) {
      requestData = {
        quoteId: cardIdInt,
      };
    }
    if (method === 'hello_member') {
      showFullScreenLoader();
      requestData.programCode = 'aura';
      if (loyaltyCard === 'aura') {
        // Call API to undo redeem aura points.
        const data = {
          action: 'remove points',
          userId: getUserDetails().id || 0,
          cardNumber: loyaltyCard,
        };
        redeemAuraPoints(data);
      }
      const response = setHelloMemberLoyaltyCard(identifierNo, cartId);
      response.then((result) => {
        if (result.status) {
          // Redirect to cart page.
          window.location.href = Drupal.url('cart');
        } else {
          method = 'aura';
          logger.error('Error while trying to switch to hello member loyalty card cartId: @cartId', {
            '@cartId': cartId,
            '@response': result.data.error_message,
          });
        }
        removeFullScreenLoader();
      });
    }
    if (method === 'aura') {
      requestData.programCode = 'hello_member';
      showFullScreenLoader();
      const response = callHelloMemberApi('unsetLoyaltyCard', 'POST', requestData);
      // Fetch updated cart and remove the member discount from checkout summary.
      response.then((result) => {
        if (result.status === 200 && result.data) {
          updatePriceSummaryBlock(refreshCart);
        } else {
          method = 'hello_member';
          logger.error('Error while trying to switch to aura loyalty card cartId: @cartId', {
            '@cartId': cartId,
            '@response': result.data.error_message,
          });
        }
        removeFullScreenLoader();
      });
    }

    this.setState({
      currentOption: method,
    });
    this.resetPopupStatus(false);
  }

  render() {
    const { animationDelay, helloMemberPoints, cart } = this.props;
    const { currentOption, selectedOption, showLoyaltyPopup } = this.state;

    if (!hasValue(helloMemberPoints)) {
      return null;
    }

    return (
      <div className="loyalty-options-registered">
        {showLoyaltyPopup && hasValue(selectedOption)
          && currentOption !== selectedOption
          && (
          <LoyaltyConfirmPopup
            showLoyaltyPopup={showLoyaltyPopup}
            currentOption={currentOption}
            selectedOption={selectedOption}
            changeLoyaltyOption={this.changeLoyaltyOption}
            resetPopupStatus={this.resetPopupStatus}
          />
          )}
        {isAuraIntegrationEnabled()
          && (
            <>
              <LoyaltySelectOption
                cart={cart}
                currentOption={currentOption}
                animationDelay={animationDelay}
                optionName="hello_member"
                showLoyaltyPopup={this.showLoyaltyPopup}
                helloMemberPoints={helloMemberPoints}
              />
              <LoyaltySelectOption
                cart={cart}
                currentOption={currentOption}
                animationDelay={animationDelay}
                optionName="aura"
                showLoyaltyPopup={this.showLoyaltyPopup}
                helloMemberPoints={helloMemberPoints}
              />
            </>
          )}
        {!isAuraIntegrationEnabled()
          && (
          <div className="loyalty-option hello-member-loyalty fadeInUp" style={{ animationDelay }}>
            <div className="loaylty-option-text">
              {parse(parse(Drupal.t('@hm_icon Member earns @points points', {
                '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
                '@points': helloMemberPoints,
              }, { context: 'hello_member' })))}
            </div>
          </div>
          )}
      </div>
    );
  }
}

export default RegisteredUserLoyalty;
