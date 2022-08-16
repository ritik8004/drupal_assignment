import React from 'react';
import { renderToString } from 'react-dom/server';
import parse from 'html-react-parser';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { isAuraIntegrationEnabled } from '../../../../../../js/utilities/helloMemberHelper';
import AuraLoyalty from '../aura/aura-loyalty';

const GuestUserLoyalty = ({
  helloMemberPoints,
  animationDelay,
  loyaltyCard,
  cart,
}) => {
  let open = false;
  if (!hasValue(helloMemberPoints)) {
    return null;
  }
  // Get loyalty card data from cart.
  const {
    cart: {
      loyalty_card: cardNumber,
      loyalty_type: loyaltyType,
    },
  } = cart;

  if (hasValue(loyaltyType) && hasValue(cardNumber)
    && loyaltyType === 'aura') {
    open = true;
  }

  return (
    <div className="loyalty-options-guest">
      <div className="loyalty-option hello-member-loyalty fadeInUp" style={{ animationDelay }}>
        <div className="loaylty-option-text">
          {parse(parse(Drupal.t('@hm_icon @login_link or Become a member to earn @points points', {
            '@login_link': `<a href="${Drupal.url('cart/login')}">${Drupal.t('Sign in', {}, { context: 'hello_member' })}</a>`,
            '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
            '@points': helloMemberPoints,
          }, { context: 'hello_member' })))}
        </div>
      </div>
      {isAuraIntegrationEnabled()
        && (
        <div className="loyalty-option aura-loyalty fadeInUp" style={{ animationDelay }}>
          <div className="loaylty-option-text">
            <AuraLoyalty
              optionName="aura"
              open={open}
              loyaltyCard={loyaltyCard}
              cart={cart}
            />
          </div>
        </div>
        )}
    </div>
  );
};

export default GuestUserLoyalty;
