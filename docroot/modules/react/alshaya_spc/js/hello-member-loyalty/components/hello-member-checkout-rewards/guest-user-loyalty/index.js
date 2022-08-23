import React from 'react';
import { renderToString } from 'react-dom/server';
import parse from 'html-react-parser';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { isAuraIntegrationEnabled } from '../../../../../../js/utilities/helloMemberHelper';
import AuraLoyalty from '../aura/aura-loyalty';
import getStringMessage from '../../../../utilities/strings';
import AuraPointsToEarn from '../aura/aura-points-to-earn';

export default class GuestUserLoyalty extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
  }

  componentDidMount() {
    const { cart } = this.props;
    // Get loyalty card data from cart.
    const {
      cart: {
        loyalty_card: cardNumber,
        loyalty_type: loyaltyType,
      },
    } = cart;

    if (hasValue(loyaltyType) && hasValue(cardNumber)
      && loyaltyType === 'aura') {
      this.setState({
        open: true,
      });
    }
  }

  showAuraPoints = () => {
    this.setState({
      open: true,
    });
  }

  hideAuraPoints = () => {
    this.setState({
      open: false,
    });
  }


  render() {
    const { open } = this.state;
    const { cart, animationDelay, helloMemberPoints } = this.props;

    if (!hasValue(helloMemberPoints)) {
      return null;
    }

    return (
      <div className="loyalty-options-guest">
        <div className="loyalty-option hello-member-loyalty fadeInUp" style={{ animationDelay }}>
          <div className="loaylty-option-text">
            {parse(parse(getStringMessage('hello_member_guest_login', {
              '@login_link': `<a href="${Drupal.url('cart/login')}">${Drupal.t('Sign in', {}, { context: 'hello_member' })}</a>`,
              '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
              '@points': helloMemberPoints,
            })))}
          </div>
        </div>
        {isAuraIntegrationEnabled()
          && (
          <div className="loyalty-option aura-loyalty fadeInUp" style={{ animationDelay }}>
            <div className="loaylty-option-text">
              <AuraLoyalty
                optionName="aura"
                open={open}
                cart={cart}
                showAuraPoints={this.showAuraPoints}
                hideAuraPoints={this.hideAuraPoints}
              />
            </div>
          </div>
          )}
        {open && (
        <div className="aura-earned-points">
          <AuraPointsToEarn cart={cart} />
        </div>
        )}
      </div>
    );
  }
}
