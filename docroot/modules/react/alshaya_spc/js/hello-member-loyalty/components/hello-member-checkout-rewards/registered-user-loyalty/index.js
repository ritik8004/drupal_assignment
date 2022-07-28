import React from 'react';
import parse from 'html-react-parser';
import { renderToString } from 'react-dom/server';
import { isAuraIntegrationEnabled } from '../../../../../../js/utilities/helloMemberHelper';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';
import LoyaltySelectOption from '../loyalty-select-option';
import LoyaltyConfirmPopup from '../loyalty-confirm-popup';
import { setHelloMemberLoyaltyCard } from '../../../../../../alshaya_hello_member/js/src/hello_member_api_helper';

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
    if (!hasValue(loyaltyCard) && loyaltyType === 'hello_member') {
      setHelloMemberLoyaltyCard(identifierNo, cartId);
    }
    // @todo: Handle update cart event on setting hello member loyalty.
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
   * @param {string} method
   *  Selected method by customer.
   */
  changeLoyaltyOption = (method) => {
    // @todo: Trigger a pop-up to confirm the loyalty option.
    // @todo: Refresh cart with the selected value.
    if (method === 'hello_member') {
      const { cart, identifierNo } = this.props;
      const cartId = cart.cart.cart_id;
      setHelloMemberLoyaltyCard(identifierNo, cartId);
      // @todo: Handle update cart event on setting hello member loyalty.
    } else if (method === 'aura') {
      // @todo: Refresh cart with the selected value.
      // @todo: Open aura loyalty form and set aura loyalty after aura sign in validation.
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
