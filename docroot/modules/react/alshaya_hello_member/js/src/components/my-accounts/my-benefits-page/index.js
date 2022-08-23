import HTMLReactParser from 'html-react-parser';
import moment from 'moment';
import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { callHelloMemberApi, getHelloMemberCustomerInfo } from '../../../../../../js/utilities/helloMemberHelper';
import logger from '../../../../../../js/utilities/logger';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import QrCodeDisplay from '../my-membership/qr-code-display';
import getStringMessage from '../../../../../../js/utilities/strings';
import Loading from '../../../../../../js/utilities/loading';
import AddBenefitsToCart from './add-benefits-to-cart';

class MyBenefitsPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      myBenefit: null,
      codeId: null,
      couponId: null,
      voucherType: null,
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
            codeId: response.data.coupons[0].code,
            couponId: `${response.data.coupons[0].type}|${response.data.coupons[0].code}`,
            voucherType: response.data.coupons[0].type,
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
            codeId: response.data.offers[0].code,
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
    const {
      wait, myBenefit, codeId, couponId, voucherType,
    } = this.state;

    if (!wait) {
      return (
        <div className="my-benefit-page-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    if (myBenefit === null) {
      return null;
    }

    let qrCodeTitle = getStringMessage('offer_id_title');
    if (drupalSettings.helloMemberBenefits.type === 'coupon') {
      qrCodeTitle = getStringMessage('coupon_id_title');
    }

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
            {myBenefit.description}
          </div>
          <div className="expiry">
            {getStringMessage('benefit_expire', { '@expire_date': moment(new Date(myBenefit.expiry_date || myBenefit.end_date)).format('DD MMMM YYYY') })}
          </div>
        </div>
        <div className="btn-wrapper">
          <QrCodeDisplay
            memberId={myBenefit.member_identifier}
            qrCodeTitle={qrCodeTitle}
            codeId={couponId || codeId}
            width={79}
          />
          <AddBenefitsToCart
            title={myBenefit.description}
            codeId={codeId}
            voucherType={voucherType}
          />
        </div>
        <div className="benefit-description">
          {(hasValue(myBenefit.applied_conditions)) ? HTMLReactParser(myBenefit.applied_conditions) : ''}
        </div>
        <div className="expire-on">
          <h3>
            {getStringMessage('benefit_expire_no_date')}
            {':'}
          </h3>
          {moment(new Date(myBenefit.expiry_date || myBenefit.end_date)).format('DD MMMM YYYY')}
        </div>
        <div className="benefit-Tnc">
          {Drupal.t('Maximum one code per member, which can be used at one occasion for purchase of above stated models, directly from the online store in the UK. For more terms, please visit https://www2.hm.com/en', {}, { context: 'hello_member' })}
        </div>
      </div>
    );
  }
}

export default MyBenefitsPage;
