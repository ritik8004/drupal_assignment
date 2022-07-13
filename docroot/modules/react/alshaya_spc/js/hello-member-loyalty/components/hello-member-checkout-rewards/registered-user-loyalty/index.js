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
import logger from '../../../../../../js/utilities/logger';

class RegisteredUserLoyalty extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      currentOption: 'hello_member_loyalty',
      selectedOption: null,
      showLoyaltyPopup: false,
    };
  }

  componentDidMount() {
    // For registered user, we set loyalty card for the default option selected.
    // Currently, default option is hello_member_loyalty
    // @todo: Update default option if cart has been updated.
    this.setHelloMemberLoyalty();
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

  resetPopupStatus = () => {
    this.setState({
      showLoyaltyPopup: false,
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
    if (method === 'hello_member_loyalty') {
      this.setHelloMemberLoyalty();
    } else if (method === 'aura_loyalty') {
      // @todo: Refresh cart with the selected value.
      // @todo: Open aura loyalty form and set aura loyalty after aura sign in validation.
    }
    this.setState({
      currentOption: method,
    });
    this.resetPopupStatus();
  }

  /**
   * Select hello member loyalty for the customer.
   */
  setHelloMemberLoyalty = () => {
    const { cart, identifierNo } = this.props;
    const cartId = cart.cart.cart_id;
    const setHmLoyaltyCard = setHelloMemberLoyaltyCard(identifierNo, cartId);
    if (setHmLoyaltyCard instanceof Promise) {
      setHmLoyaltyCard.then((response) => {
        if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)) {
          // @todo: Handle update cart event.
        } else if (hasValue(response.error)) {
          logger.error('Error while trying to get hello member customer data. Data: @data.', {
            '@data': JSON.stringify(response),
          });
        }
      });
    }
  }

  render() {
    const { animationDelay, helloMemberPoints } = this.props;
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
            currentOption={currentOption}
            selectedOption={selectedOption}
            changeLoyaltyOption={this.changeLoyaltyOption}
            resetPopupStatus={this.resetPopupStatus}
          />
        </ConditionalView>
        <ConditionalView condition={isAuraIntegrationEnabled()}>
          <LoyaltySelectOption
            animationDelay={animationDelay}
            selectedOption={selectedOption}
            optionName="hello_member_loyalty"
            showLoyaltyPopup={this.showLoyaltyPopup}
            helloMemberPoints={helloMemberPoints}
          />
          <LoyaltySelectOption
            animationDelay={animationDelay}
            selectedOption={selectedOption}
            optionName="aura_loyalty"
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
