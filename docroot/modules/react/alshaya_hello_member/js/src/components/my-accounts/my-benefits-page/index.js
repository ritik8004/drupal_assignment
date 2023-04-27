import HTMLReactParser from 'html-react-parser';
import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import {
  callHelloMemberApi,
  getHelloMemberCustomerInfo,
  getBenefitTag,
  getExternalBenefitText,
} from '../../../../../../js/utilities/helloMemberHelper';
import logger from '../../../../../../js/utilities/logger';
import QrCodeDisplay from '../my-membership/qr-code-display';
import getStringMessage from '../../../../../../js/utilities/strings';
import Loading from '../../../../../../js/utilities/loading';
import AddBenefitsToCart from './add-benefits-to-cart';
import { formatDate } from '../../../utilities';

class MyBenefitsPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      myBenefit: null,
      codeId: null,
      couponId: null,
      offerType: null,
      promotionType: null,
    };
  }

  async componentDidMount() {
    // Get customer info.
    const params = getHelloMemberCustomerInfo();
    if (!hasValue(params.error)) {
      const { type } = drupalSettings.helloMemberBenefits;
      params.code = drupalSettings.helloMemberBenefits.code;
      if (type === 'coupon') {
        const response = await callHelloMemberApi('helloMemberCouponPage', 'GET', params);
        if (hasValue(response.data) && !hasValue(response.data.error)) {
          const promotionType = response.data.coupons[0].promotion_type;
          const { description } = response.data.coupons[0];
          this.setState({
            myBenefit: response.data.coupons[0],
            wait: true,
            codeId: response.data.coupons[0].code,
            couponId: `${response.data.coupons[0].type}|${response.data.coupons[0].code}`,
            offerType: response.data.coupons[0].type,
            promotionType,
          });
          // Push coupon data to gtm once it is loaded.
          if (hasValue(promotionType) && hasValue(description)) {
            Drupal.alshayaSeoGtmPushBenefitsOffer({ promotionType, description });
          }
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
      wait, myBenefit, codeId, couponId, offerType, promotionType,
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

    let qrCodeTitle = getStringMessage('benefit_id_title');
    if (drupalSettings.helloMemberBenefits.type === 'coupon') {
      qrCodeTitle = getStringMessage('benefit_id_title');
    }

    const benefitTag = getBenefitTag(myBenefit);
    // Variable to catch the button text based on the tag from myBenefit response.
    const externalBenefitText = getExternalBenefitText(myBenefit);
    // Show QRCodeButton, either if response has no benefit tag or has a value '0' or 'S'.
    const showQRButton = !!(!hasValue(benefitTag)
    || (hasValue(benefitTag) && (benefitTag === 'O' || benefitTag === 'S')));
    // Show CartButton, either if response has no benefit tag or has a value '0' or 'E'.
    const showCartButton = !!(!hasValue(benefitTag)
    || (hasValue(benefitTag) && (benefitTag === 'O' || benefitTag === 'E')));

    return (
      <div className="my-benefit-page-wrapper">
        <div className="image-container">
          <img src={myBenefit.large_image} />
        </div>
        <div className="category-name">
          {myBenefit.category_name}
        </div>
        <div className="voucher-wrapper">
          <div className="title">
            {myBenefit.name}
          </div>
          <div className="info">
            {myBenefit.description}
          </div>
          <div className="expiry">
            {getStringMessage('benefit_expire', { '@expire_date': formatDate(new Date(myBenefit.expiry_date || myBenefit.end_date)) })}
          </div>
        </div>
        <div className="btn-wrapper">
          {showQRButton
            && (
              <QrCodeDisplay
                benefitName={myBenefit.description}
                benefitType={promotionType}
                memberId={myBenefit.member_identifier}
                qrCodeTitle={qrCodeTitle}
                codeId={couponId || codeId}
                width={79}
                memberTitle={getStringMessage('redeem_in_store')}
              />
            )}
          {showCartButton
            && (
              <AddBenefitsToCart
                title={myBenefit.description}
                codeId={codeId}
                offerType={offerType}
                promotionType={promotionType}
              />
            )}
          {hasValue(myBenefit.benefit_url) && hasValue(benefitTag) && hasValue(externalBenefitText)
            && (
              <a target="_blank" rel="noopener noreferrer" className="qr-code-button external-btn" href={myBenefit.benefit_url}>
                { externalBenefitText }
              </a>
            )}
        </div>
        <div className="benefit-description">
          {(hasValue(myBenefit.applied_conditions)) ? HTMLReactParser(myBenefit.applied_conditions) : ''}
        </div>
        <div className="expire-on">
          <h3>
            {getStringMessage('benefit_expire_no_date')}
            {':'}
          </h3>
          {formatDate(new Date(myBenefit.expiry_date || myBenefit.end_date))}
        </div>
        <div className="benefit-Tnc">
          {Drupal.t('Maximum one code per member, which can be used at one occasion for purchase of above stated models, directly from the online store in the UK. For more terms, please visit https://www2.hm.com/en', {}, { context: 'hello_member' })}
        </div>
      </div>
    );
  }
}

export default MyBenefitsPage;
