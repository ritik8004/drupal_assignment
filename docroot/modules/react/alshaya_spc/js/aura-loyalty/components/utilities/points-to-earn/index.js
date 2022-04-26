import React from 'react';
import parse from 'html-react-parser';
import { renderToString } from 'react-dom/server';
import ToolTip from '../../../../utilities/tooltip';
import { getAllAuraStatus, getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import getStringMessage from '../../../../utilities/strings';
import Loading from '../../../../utilities/loading';
import AuraHeaderIcon from '../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';
import { getTooltipPointsOnHoldMsg } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { isUserAuthenticated } from '../../../../../../js/utilities/helper';

const PointsToEarnMessage = (props) => {
  const { pointsToEarn, loyaltyStatus, wait } = props;
  const allAuraStatus = getAllAuraStatus();

  // Guest User & No card.
  if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
    || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U) {
    return (
      <span className="spc-aura-points-to-earn">
        {getStringMessage(
          'cart_to_earn_with_points',
          { '@pts': pointsToEarn },
        )}
      </span>
    );
  }

  // Guest user & Linked card. This will display when a guest user has Aura card
  // signed up for Aura irrepective of fully verified or partially verified.
  if (!isUserAuthenticated()
    && (loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED
    || loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED)) {
    return (
      <>
        <div className="spc-aura-cart-icon">
          <AuraHeaderIcon />
        </div>
        <div className="spc-aura-cart-content">
          <span className="spc-aura-points-to-earn">
            {/* We are using !pts as it does not encode the html into string
            while using this in Drupal t() function. */}
            {parse(getStringMessage('cart_earn_with_this_purchase', {
              '!pts': wait ? renderToString(<Loading />) : pointsToEarn,
            }))}
            <ToolTip enable question>{getTooltipPointsOnHoldMsg()}</ToolTip>
          </span>
        </div>
      </>
    );
  }

  // Registered User & Linked card. This will display when a logged in user has
  // Aura card signed up that is partially verified.
  if (isUserAuthenticated()
    && loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED) {
    const {
      appStoreLink: appleAppStoreLink,
      googlePlayLink: googlePlayStoreLink,
    } = getAuraConfig();

    return (
      <>
        <div className="spc-aura-cart-icon">
          <AuraHeaderIcon />
        </div>
        <div className="spc-aura-cart-content">
          <span className="spc-aura-points-to-earn">
            {parse(getStringMessage('cart_redeem_points_msg', {
              '!pts': wait ? renderToString(<Loading />) : pointsToEarn,
            }))}
            <div>
              <a
                className="spc-link-play-store"
                href={appleAppStoreLink}
                target="_blank"
                rel="noopener noreferrer"
              >
                {getStringMessage('app_store_link_text')}
              </a>
              <span className="spc-aura-or-text">/</span>
              <a
                className="spc-link-play-store"
                href={googlePlayStoreLink}
                target="_blank"
                rel="noopener noreferrer"
              >
                {getStringMessage('play_store_link_text')}
              </a>
              <ToolTip enable question>{getTooltipPointsOnHoldMsg()}</ToolTip>
            </div>
          </span>
        </div>
      </>
    );
  }

  // Registered User & Linked card. This will display when a logged in user has
  // Aura card signed up that is fully verified.
  if (isUserAuthenticated()
    && loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED) {
    return (
      <>
        <div className="spc-aura-cart-icon">
          <AuraHeaderIcon />
        </div>
        <div className="spc-aura-cart-content">
          <span className="spc-aura-points-to-earn">
            {/* We are using !pts as it does not encode the html into string
            while using this in Drupal t() function. */}
            {parse(getStringMessage('cart_earn_with_this_purchase', {
              '!pts': wait ? renderToString(<Loading />) : pointsToEarn,
            }))}
            <ToolTip enable question>{getTooltipPointsOnHoldMsg()}</ToolTip>
          </span>
        </div>
      </>
    );
  }

  // Registered User & UnLinked card.
  if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA) {
    return (
      <>
        <div className="spc-aura-cart-icon">
          <AuraHeaderIcon />
        </div>
        <div className="spc-aura-cart-content">
          <div className="spc-aura-points-to-earn">
            <span className="spc-link-aura-link-wrapper submit">
              <a
                className="spc-link-aura-link"
                /** @todo: We need to change this to open the link aura form. */
              >
                {getStringMessage('aura_link_aura')}
              </a>
            </span>
            {getStringMessage(
              'cart_to_earn_with_points',
              { '@pts': pointsToEarn },
            )}
            <ToolTip enable question>{getStringMessage('checkout_earn_and_redeem_tooltip')}</ToolTip>
          </div>
        </div>
      </>
    );
  }

  return (
    <span className="spc-aura-points-to-earn" />
  );
};

export default PointsToEarnMessage;
