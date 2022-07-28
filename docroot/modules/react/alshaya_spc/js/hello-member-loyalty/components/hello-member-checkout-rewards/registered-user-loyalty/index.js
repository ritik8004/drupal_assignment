import React from 'react';
import parse from 'html-react-parser';
import { renderToString } from 'react-dom/server';
import { callHelloMemberApi, isAuraIntegrationEnabled } from '../../../../../../js/utilities/helloMemberHelper';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';
import LoyaltySelectOption from '../loyalty-select-option';
import LoyaltyConfirmPopup from '../loyalty-confirm-popup';
import { setHelloMemberLoyaltyCard } from '../../../../../../alshaya_hello_member/js/src/hello_member_api_helper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import { fetchCartData } from '../../../../utilities/api/requests';
import { isUserAuthenticated } from '../../../../../../js/utilities/helper';
import { getHelloMemberAuraStorageKey } from '../utilities/loyalty_helper';
import { redeemAuraPoints } from '../../../../aura-loyalty/components/utilities/checkout_helper';
import { getUserDetails } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import logger from '../../../../../../js/utilities/logger';

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
        loyalty_card: loyaltyCard,
      },
    } = cart;
    // Do not set loyalty card for hello member if already set in cart.
    if (!hasValue(loyaltyCard) && loyaltyType !== 'hello_member') {
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
    const { cart } = this.props;
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
    if (selectedMethod === 'hello_member') {
      requestData.programCode = 'aura';
      // Call API to undo redeem aura points.
      const data = {
        action: 'remove points',
        userId: getUserDetails().id || 0,
        cardNumber: loyaltyCard,
      };
      redeemAuraPoints(data);
      const response = callHelloMemberApi('unsetLoyaltyCard', 'POST', requestData);
      response.then((result) => {
        if (result.status === 200) {
          if (result.data) {
            // Redirect to cart page.
            window.location.href = Drupal.url('cart');
          }
        }
      });
    }
    if (selectedMethod === 'aura') {
      requestData.programCode = 'hello_member';
      const response = callHelloMemberApi('unsetLoyaltyCard', 'POST', requestData);
      // Fetch updated cart and remove the member discount from checkout summary.
      showFullScreenLoader();
      response.then((result) => {
        if (result.status === 200) {
          if (result.data) {
            // Remove hello member discount if selected method is aura.
            const cartData = fetchCartData();
            if (cartData instanceof Promise) {
              cartData.then((cartResult) => {
                if (typeof cartResult.error === 'undefined') {
                  window.dynamicPromotion.apply(cartResult);
                }
              });
            }
          }
        } else {
          logger.error('Error while calling trying to unset hello member loyalty card cartId: @cartId', {
            '@cartId': cartId,
            '@response': result.data.error_message,
          });
        }
        removeFullScreenLoader();
      });
    }

    // If selected method is hello member, then remove aura from storage.
    // And set hello member loyalty card.
    if (selectedMethod === 'hello_member') {
      Drupal.removeItemFromLocalStorage(getHelloMemberAuraStorageKey());
    }

    this.setState({
      currentOption: selectedMethod,
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
        <ConditionalView condition={showLoyaltyPopup
          && hasValue(selectedOption) && currentOption !== selectedOption}
        >
          <LoyaltyConfirmPopup
            showLoyaltyPopup={showLoyaltyPopup}
            currentOption={currentOption}
            selectedOption={selectedOption}
            changeLoyaltyOption={this.changeLoyaltyOption}
            resetPopupStatus={this.resetPopupStatus}
          />
        </ConditionalView>
        <ConditionalView condition={isAuraIntegrationEnabled()}>
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
        </ConditionalView>
        <ConditionalView condition={!isAuraIntegrationEnabled()}>
          <div className="loyalty-option hello-member-loyalty fadeInUp" style={{ animationDelay }}>
            <div className="loaylty-option-text">
              {parse(parse(Drupal.t('@hm_icon Member earns @points points', {
                '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
                '@points': helloMemberPoints,
              })))}
            </div>
          </div>
        </ConditionalView>
      </div>
    );
  }
}

export default RegisteredUserLoyalty;
