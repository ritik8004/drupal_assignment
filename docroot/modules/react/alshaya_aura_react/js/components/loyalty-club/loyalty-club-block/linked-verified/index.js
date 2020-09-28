import React from 'react';
import { getAPIData } from '../../../../utilities/api/fetchApiData';
import { getAuraTier } from '../../../../utilities/helper';

export default class LinkedVerified extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      points: '',
      pointsOnHold: '',
      upgradeMsg: '',
      expiringPoints: '',
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
        if (result.data.error === undefined && result.data !== undefined) {
          this.setState({
            points: result.data.points,
            expiringPoints: result.data.expiredPoints,
            expiryDate: result.data.expiredPointsDate,
          });
        }
      });
    }
  }

  render() {
    const {
      points, pointsOnHold, upgradeMsg, expiringPoints, expiryDate,
    } = this.state;

    return (
      <>
        <div className="aura-card-linked-verified-wrapper">
          <div className="aura-logo">
            AURA logo placeholder
          </div>
          <div className="aura-card-linked-verified-description">
            <div className="aura-tier">
              { getAuraTier() }
            </div>
            <div className="aura-points">
              { `${points} ${Drupal.t('POINTS')}`}
            </div>
            <div className="aura-points-on-hold">
              { `${pointsOnHold} ${Drupal.t('Points onhold')}`}
            </div>
            <div className="aura-upgrade-message">
              { upgradeMsg }
            </div>
          </div>
        </div>
        <div className="expiring-points">
          { `${Drupal.t('Your')} ${expiringPoints} ${Drupal.t('points is about to expire by')} ${expiryDate}. ${Drupal.t('Redeem it now !')}` }
        </div>
      </>
    );
  }
}
