import React from 'react';
import QrCodeDisplay from './qr-code-display';
import Loading from '../../../../../../js/utilities/loading';
import { getFormatedMemberId } from '../../../utilities';
import getStringMessage from '../../../../../../js/utilities/strings';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { getHelloMemberCustomerData } from '../../../hello_member_api_helper';
import TierProgress from './tier-progress';
import logger from '../../../../../../js/utilities/logger';
import dispatchCustomEvent from '../../../../../../js/utilities/events';

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
          // Dispatch event when hello member points are loaded on my account points block.
          dispatchCustomEvent('helloMemberPointsLoaded', response.data.extension_attributes);
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
          {Drupal.t('@hello_text @first_name',
            { '@hello_text': getStringMessage('hi'), '@first_name': myMembershipData.apc_first_name }, { context: 'hello_member' })}
        </div>
        <div className="points-block">
          <div className="my-points">
            <span>{myMembershipData.apc_points}</span>
            <span>{getStringMessage('points_label')}</span>
          </div>
          <TierProgress
            myMembershipData={myMembershipData}
          />
          <QrCodeDisplay memberId={memberId} width={200} />
          <div className="my-membership-id">
            {memberId}
          </div>
        </div>
      </>
    );
  }
}

export default MyMembership;
