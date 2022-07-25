import React from 'react';
import { renderToString } from 'react-dom/server';
import parse from 'html-react-parser';
import AuraHeaderIcon from '../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { isAuraIntegrationEnabled } from '../../../../../../js/utilities/helloMemberHelper';

const GuestUserLoyalty = ({
  helloMemberPoints,
  animationDelay,
}) => {
  if (!hasValue(helloMemberPoints)) {
    return null;
  }
  return (
    <div className="loyalty-options-guest">
      <div className="loyalty-option hello-member-loyalty fadeInUp" style={{ animationDelay }}>
        <div className="loaylty-option-text">
          {parse(parse(Drupal.t('@hm_icon @login_link or Become a member to earn @points points', {
            '@login_link': `<a href="${Drupal.url('cart/login')}">${Drupal.t('Sign in')}</a>`,
            '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
            '@points': helloMemberPoints,
          })))}
        </div>
      </div>
      <ConditionalView condition={isAuraIntegrationEnabled()}>
        <div className="loyalty-option aura-loyalty fadeInUp" style={{ animationDelay }}>
          <div className="loaylty-option-text">
            {parse(parse(Drupal.t('Earn/Redeem @aura_icon Points', {
              '@aura_icon': `<span class="hello-member-aura">${renderToString(<AuraHeaderIcon />)}</span>`,
            })))}
          </div>
        </div>
      </ConditionalView>
    </div>
  );
};

export default GuestUserLoyalty;
