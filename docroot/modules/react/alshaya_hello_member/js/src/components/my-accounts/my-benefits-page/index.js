import HTMLReactParser from 'html-react-parser';
import moment from 'moment';
import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { callHelloMemberApi, getHelloMemberCustomerInfo } from '../../../../../../js/utilities/helloMemberHelper';
import logger from '../../../../../../js/utilities/logger';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import QrCodeDisplay from '../my-membership/qr-code-display';
import getStringMessage from '../../../../../../js/utilities/strings';
import { getFormatedMemberId } from '../../../utilities';

class MyBenefitsPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      myBenefit: null,
    };
  }

  async componentDidMount() {
    // Get customer info.
    const params = getHelloMemberCustomerInfo();
    if (!hasValue(params.error)) {
      showFullScreenLoader();
      const { type } = drupalSettings.helloMemberBenefits;
      params.code = drupalSettings.helloMemberBenefits.code;
      if (type === 'coupon') {
        const response = await callHelloMemberApi('helloMemberCouponPage', 'GET', params);
        if (hasValue(response.data) && !hasValue(response.data.error)) {
          this.setState({
            myBenefit: response.data.coupons[0],
            wait: true,
          });
          removeFullScreenLoader();
        } else {
          // If coupon details API is returning Error.
          logger.error('Error while calling the coupon details Api @params, @message', {
            '@params': params,
            '@message': response.data.message,
          });
        }
      } else if (type === 'offer') {
        const response = await callHelloMemberApi('helloMemberOfferPage', 'GET', params);
        if (hasValue(response.data) && !hasValue(response.data.error)) {
          this.setState({
            myBenefit: response.data.offers[0],
            wait: true,
          });
          removeFullScreenLoader();
        } else {
          // If offer details API is returning Error.
          logger.error('Error while calling the offer details Api @params, @message', {
            '@params': params,
            '@message': response.data.message,
          });
        }
      }
    }
  }

  render() {
    const { wait, myBenefit } = this.state;

    if (!wait && myBenefit === null) {
      return null;
    }

    const memberId = getFormatedMemberId(myBenefit.member_identifier);

    return (
      <div className="my-benefit-page-wrapper">
        <div className="image-container">
          <img src={myBenefit.large_image} />
        </div>
        <div className="voucher-wrapper">
          <div className="title">
            {myBenefit.name}
          </div>
          <div className="info">
            {myBenefit.short_description}
          </div>
          <div className="expiry">
            {getStringMessage('benefit_expire')}
            {' '}
            {moment(new Date(myBenefit.expiry_date || myBenefit.end_date)).format('DD MMMM YYYY')}
          </div>
        </div>
        <div className="btn-wrapper">
          <QrCodeDisplay memberId={memberId} />
          <div className="button-wide">{getStringMessage('benefit_add_to_bag')}</div>
        </div>
        <div className="benefit-description">
          {HTMLReactParser(myBenefit.description)}
        </div>
        <div className="expire-on">
          <h3>
            {getStringMessage('benefit_expire')}
            {':'}
          </h3>
          {moment(new Date(myBenefit.expiry_date || myBenefit.end_date)).format('DD MMMM YYYY')}
        </div>
        <div className="benefit-Tnc">
          {myBenefit.temrs_and_conditions}
        </div>
      </div>
    );
  }
}

export default MyBenefitsPage;
