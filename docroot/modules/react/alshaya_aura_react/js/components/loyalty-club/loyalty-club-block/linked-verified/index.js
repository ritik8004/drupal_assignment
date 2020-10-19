import React from 'react';
import { getUserProfileInfo } from '../../../../utilities/helper';
import PointsExpiryMessage
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/utilities/points-expiry-message';
import PointsUpgradeMessage
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/utilities/points-upgrade-message';
import ToolTip from '../../../../../../alshaya_spc/js/utilities/tooltip';

export default class AuraMyAccountVerifiedUser extends React.Component {
  /**
   * Get tooltip content.
   */
  getToolTipContent = () => Drupal.t('Your points will be credited to your account but will be on-hold status until the return period of 14 days. After that you will be able to redeem the points.');

  render() {
    const {
      tier, points, pointsOnHold, upgradeMsg, expiringPoints, expiryDate,
    } = this.props;

    const profileInfo = getUserProfileInfo();

    return (
      <div className="aura-card-linked-verified-wrapper fadeInUp">
        <div className="aura-card-linked-verified-wrapper-content">
          <div className="aura-logo">
            <div className="aura-user-avatar">{ profileInfo.avatar }</div>
            <div className="aura-user-name">{ profileInfo.profileName }</div>
          </div>
          <div className="aura-card-linked-verified-description">
            <div className="aura-tier">
              <label>{Drupal.t('Status')}</label>
              <span className="aura-blend">{ tier }</span>
            </div>
            <div className="aura-points">
              <label>{Drupal.t('Point balance')}</label>
              <span className="aura-blend">{ `${points} ${Drupal.t('pts')}`}</span>
            </div>
            <div className="aura-points-on-hold">
              <label>
                {Drupal.t('Points on hold')}
                <ToolTip enable question>{ this.getToolTipContent() }</ToolTip>
              </label>
              <span className="aura-blend">{ `${pointsOnHold} ${Drupal.t('pts')}`}</span>
            </div>
          </div>
        </div>
        <PointsUpgradeMessage msg={upgradeMsg} />
        <PointsExpiryMessage points={expiringPoints} date={expiryDate} />
      </div>
    );
  }
}
