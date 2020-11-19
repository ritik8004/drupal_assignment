import React from 'react';
import AuraFormLinkCard from '../../../aura-forms/aura-link-card-textbox';
import PointsString from '../../../utilities/points-string';

class AuraNotLinkedNoDataCheckout extends React.Component {
  getMembersToEarnMessage = (points) => {
    const toEarnMessageP1 = `${Drupal.t('Members will earn')} `;
    const toEarnMessageP2 = ` ${Drupal.t('with this purchase')}`;

    return (
      <span className="spc-checkout-aura-points-to-earn">
        { toEarnMessageP1 }
        <PointsString points={points} />
        { toEarnMessageP2 }
      </span>
    );
  };

  render() {
    const { pointsToEarn } = this.props;
    return (
      <div className="block-content guest-user">
        <div className="title">
          <div className="subtitle-1">{ Drupal.t('Earn and redeem as you shop ') }</div>
          <div className="subtitle-2">{ this.getMembersToEarnMessage(pointsToEarn) }</div>
        </div>
        <div className="spc-aura-link-card-form">
          <div className="label">{ Drupal.t('Already an Aura member?') }</div>
          <div className="item-wrapper">
            <AuraFormLinkCard />
          </div>
        </div>
      </div>
    );
  }
}

export default AuraNotLinkedNoDataCheckout;
