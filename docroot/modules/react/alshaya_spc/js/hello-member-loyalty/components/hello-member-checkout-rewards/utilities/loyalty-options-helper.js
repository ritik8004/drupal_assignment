import React from 'react';
import { renderToString } from 'react-dom/server';
import parse from 'html-react-parser';
import AuraHeaderIcon from '../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

function getCheckoutLoginLink() {
  if (hasValue(drupalSettings.checkout_login_link)) {
    return drupalSettings.checkout_login_link;
  }
  return '';
}

/**
 * Helper function to get hello member text for guest user.
 */
function getHelloMemberTextForGuestUser(helloMemberPoints) {
  return parse(parse(Drupal.t('@hm_icon @login_link or Become a member to earn @points points', {
    '@login_link': getCheckoutLoginLink(),
    '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
    '@points': helloMemberPoints,
  })));
}

/**
 * Helper function to get hello member text for logged in user.
 */
function getHelloMemberTextForRegisteredUser(helloMemberPoints) {
  return parse(parse(Drupal.t('@hm_icon Member earns @points points', {
    '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
    '@points': helloMemberPoints,
  })));
}

/**
 * Helper function to get aura points redeem text.
 */
function getAuraRedeemText() {
  return parse(parse(Drupal.t('Earn/Redeem @aura_icon Points', {
    '@aura_icon': `<span class="hello-member-aura">${renderToString(<AuraHeaderIcon />)}</span>`,
  })));
}

export {
  getHelloMemberTextForGuestUser,
  getHelloMemberTextForRegisteredUser,
  getAuraRedeemText,
};
