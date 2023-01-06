import React from 'react';
import parse from 'html-react-parser';
import { renderToString } from 'react-dom/server';
import AuraHeaderIcon from '../../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';
import getStringMessage from '../../../../../../../js/utilities/strings';
import AuraFormRedeemPoints from '../../../aura-forms/aura-redeem-points';
import { getAllAuraStatus } from '../../../../../../../alshaya_aura_react/js/utilities/helper';
import ConditionalView from '../../../../../../../js/utilities/components/conditional-view';
import {
  getPointToPrice,
  getTooltipPointsOnHoldMsg,
} from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import ToolTip from '../../../../../utilities/tooltip';
import { formatDate } from '../../../../../../../alshaya_aura_react/js/utilities/reward_activity_helper';

export default class AuraLinkedCheckout extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      // Accordion should be closed by default.
      active: false,
    };
  }

  // Update the redemption accordion status.
  changeRedeemAuraAccordionStatus = () => {
    const { active } = this.state;
    this.setState({ active: !active });
  };


  render = () => {
    const {
      pointsInAccount,
      pointsToEarn,
      expiringPoints,
      expiryDate,
      cardNumber,
      totals,
      paymentMethodInCart,
      formActive,
      loyaltyStatus,
      methodActive,
      cart,
    } = this.props;
    // Prepare the props based on the state values.
    const { active } = this.state;
    const allAuraStatus = getAllAuraStatus();
    // Show accordion expandad, if payment method is active on checkout page.
    const activeClass = (active && methodActive) ? 'active' : '';
    const totalAuraMoney = getPointToPrice(pointsInAccount);

    return (
      <div>
        <div className="redeem-aura-points">
          <div className={`redeem-aura-points-header-container ${activeClass}`} onClick={() => this.changeRedeemAuraAccordionStatus()}>
            <span>
              {parse(parse(getStringMessage('redeem_aura_points_header', {
                '@aura_icon': `<span class="join-aura">${renderToString(<AuraHeaderIcon />)}</span>`,
              })))}
            </span>
            <span className="accordion-icon" />
          </div>
          <div className={`redeem-aura-points-content ${activeClass}`}>
            <div className="block-content">
              <div className="current-available-points">
                <span>
                  {parse(parse(getStringMessage('checkout_you_have_pts', {
                    '@pts': `<span class="spc-aura-highlight" data-aura-money=${totalAuraMoney}>${pointsInAccount}</span>`,
                  })))}
                  <ConditionalView condition={expiringPoints !== 0}>,</ConditionalView>
                </span>
              </div>
              <div className="points-expiring">
                <ConditionalView condition={expiringPoints !== 0}>
                  <div className="spc-aura-points-expiry-item">
                    {parse(parse(getStringMessage(
                      'checkout_point_expiry_with_date',
                      {
                        '@pts': pointsInAccount,
                        '@date': `<b>${formatDate(expiryDate, 'DD-Mon-YYYY')}</b>`,
                      },
                    )))}
                  </div>
                  {/* TO DO- Below tooltip should be replaced once we have tooltip content. */}
                  <ToolTip enable question>{ getTooltipPointsOnHoldMsg() }</ToolTip>
                </ConditionalView>
              </div>
            </div>
            {/* Registered User - Linked Card - Full Enrollment */}
            <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED}>
              <AuraFormRedeemPoints
                pointsInAccount={pointsInAccount}
                cardNumber={cardNumber}
                totals={totals}
                paymentMethodInCart={paymentMethodInCart}
                formActive={formActive}
                cart={cart}
              />
            </ConditionalView>
          </div>
        </div>
        <div className="redeem-aura-footer">
          <div className="redeem-aura-footer__text">
            {parse(parse(getStringMessage(
              'aura_checkout_reward_points_to_earn',
              {
                '@pts': `<b>${pointsToEarn}</b>`,
              },
            )))}
          </div>
        </div>
      </div>
    );
  }
}
