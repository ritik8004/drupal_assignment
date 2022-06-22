import React from 'react';
import QrCodeDisplay from './qr-code-display';
import Loading from '../../../../../../js/utilities/loading';
import { getFormatedMemberId } from '../../../utilities';
import getStringMessage from '../../../../../../js/utilities/strings';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { getHelloMemberCustomerData } from '../../../hello_member_api_helper';
import TierProgress from './tier-progress';
import logger from '../../../../../../js/utilities/logger';

class MyMembership extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      myMembershipData: null,
    };
  }

  componentDidMount() {
    const helloMemberCustomerData = getHelloMemberCustomerData();
    if (helloMemberCustomerData instanceof Promise) {
      helloMemberCustomerData.then((response) => {
        let myMembershipData = null;
        if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)) {
          myMembershipData = response.data;
        } else if (hasValue(response.error)) {
          logger.error('Error while trying to get hello member customer data. Data: @data.', {
            '@data': JSON.stringify(response),
          });
        }
        this.setState({
          wait: false,
          myMembershipData,
        });
      });
    }
  }

  render() {
    const { wait, myMembershipData } = this.state;
    if (wait) {
      return (
        <div className="membership-summary-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    if (myMembershipData === null) {
      return null;
    }

    const memberId = getFormatedMemberId(myMembershipData.apc_identifier_number);

    return (
      <>
        <div className="member-name">
          {getStringMessage('hi')}
          {' '}
          {myMembershipData.apc_first_name}
          {' '}
          {myMembershipData.apc_last_name}
        </div>
        <div className="points-block">
          <div className="my-points">
            <span>{myMembershipData.apc_points}</span>
            <span>{getStringMessage('points_label')}</span>
          </div>
          <TierProgress
            myMembershipData={myMembershipData}
          />
          <QrCodeDisplay memberId={memberId} />
          <div className="my-membership-id">
            {memberId}
          </div>
        </div>
      </>
    );
  }
}

export default MyMembership;
