import React from 'react';
import AuraHeaderIcon from '../../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';
import getStringMessage from '../../../../../../../js/utilities/strings';
import PointsString from '../../../utilities/points-string';
import AuraFormRedeemPoints from '../../../aura-forms/aura-redeem-points';
import PointsExpiryMessage from '../../../utilities/points-expiry-message';
import { getAllAuraStatus } from '../../../../../../../alshaya_aura_react/js/utilities/helper';
import ConditionalView from '../../../../../common/components/conditional-view';
import { getTooltipPointsOnHoldMsg } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import ToolTip from '../../../../../utilities/tooltip';

export default class AuraLinkedCheckout extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      active: true,
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
    } = this.props;
    // Prepare the props based on the state values.
    const { active } = this.state;
    const allAuraStatus = getAllAuraStatus();
    const activeClass = active ? 'active' : '';

    return (
      <div className="redeem-aura-points">
        <div className={`redeem-aura-points-header-container ${activeClass}`} onClick={() => this.changeRedeemAuraAccordionStatus()}>
          <span>
            { getStringMessage('redeem') }
          </span>
          <span className="join-aura"><AuraHeaderIcon /></span>
          <span>{ getStringMessage('points') }</span>
          <span className="accordion-icon" />
        </div>
        <div className={`redeem-aura-points-content ${activeClass}`}>
          <div className="block-content">
            <div className="current-available-points">
              <span className="spc-aura-redeem-text">{ getStringMessage('checkout_you_have') }</span>
              <span className="spc-aura-highlight">
                <PointsString points={pointsInAccount} />
                ,
              </span>
            </div>
            <div className="points-expiring">
              <PointsExpiryMessage points={expiringPoints} date={expiryDate} />
              {/* TO DO- Below tooltip should be replaced once we have tooltip content. */}
              <ToolTip enable question>{ getTooltipPointsOnHoldMsg() }</ToolTip>
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
            />
          </ConditionalView>
        </div>
        <div className="redeem-aura-footer">
          <span className="before-text">{ getStringMessage('checkout_you_will_earn') }</span>
          <span className="points-earned-with-purchase">{pointsToEarn}</span>
          <span className="after-text">{ getStringMessage('checkout_aura_points_with_purchase') }</span>
        </div>
      </div>
    );
  }
}
