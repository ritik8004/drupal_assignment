import React from 'react';
import { getAPIData } from '../../../../utilities/api/fetchApiData';
import { getUserAuraTier, getUserAuraTierLabel } from '../../../../utilities/helper';
import PointsExpiryMessage
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/utilities/points-expiry-message';
import PointsUpgradeMessage
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/utilities/points-upgrade-message';
import ToolTip from '../../../../../../alshaya_spc/js/utilities/tooltip';

export default class AuraMyAccountVerifiedUser extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      points: 0,
      pointsOnHold: 0,
      upgradeMsg: '',
      expiringPoints: 0,
      expiryDate: '',
    };
  }

  componentDidMount() {
    // @TODO: API calls to get pointsOnHold, upgradeMsg.

    // API call to get customer points.
    const apiUrl = 'get/loyalty-club/get-customer-points';
    const apiData = getAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          this.setState({
            points: result.data.points,
            expiringPoints: result.data.expiredPoints,
            expiryDate: result.data.expiredPointsDate,
          });
        }
      });
    }
  }

  getToolTipContent = () => Drupal.t('Your points will be credited to your account but will be on-hold status until the return period of 14 days. After that you will be able to redeem the points.');

  render() {
    const {
      points, pointsOnHold, upgradeMsg, expiringPoints, expiryDate,
    } = this.state;

    return (
      <div className="aura-card-linked-verified-wrapper">
        <div className="aura-card-linked-verified-wrapper-content">
          <div className="aura-logo">
            <div className="aura-user-avatar" />
            <div className="aura-user-name">Aura UserName</div>
          </div>
          <div className="aura-card-linked-verified-description">
            <div className="aura-tier">
              <label>{Drupal.t('Status')}</label>
              <span className="aura-blend">{ getUserAuraTierLabel(getUserAuraTier()) }</span>
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
