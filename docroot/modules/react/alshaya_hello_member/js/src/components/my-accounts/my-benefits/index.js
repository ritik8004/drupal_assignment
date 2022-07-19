import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { callHelloMemberApi, getHelloMemberCustomerInfo } from '../../../../../../js/utilities/helloMemberHelper';
import Loading from '../../../../../../js/utilities/loading';
import logger from '../../../../../../js/utilities/logger';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import MyOffersAndVouchers from './my-offers-vouchers';

class MyBenefits extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      myCouponsList: null,
      myOffersList: null,
    };
  }

  async componentDidMount() {
    // Get customer info.
    const params = getHelloMemberCustomerInfo();
    if (!hasValue(params.error)) {
      showFullScreenLoader();
      // Get coupons list.
      const couponResponse = await callHelloMemberApi('helloMemberCouponsList', 'GET', params);
      if (hasValue(couponResponse.data) && !hasValue(couponResponse.data.error)) {
        this.setState({
          myCouponsList: couponResponse.data.coupons,
          wait: true,
        });
        removeFullScreenLoader();
      } else {
        // If coupons API is returning Error.
        logger.error('Error while calling the coupons Api @params, @message', {
          '@params': params,
          '@message': couponResponse.data.message,
        });
      }

      // Get offers list.
      const offerResponse = await callHelloMemberApi('helloMemberOffersList', 'GET', params);
      if (hasValue(offerResponse.data) && !hasValue(offerResponse.data.error)) {
        this.setState({
          myOffersList: offerResponse.data.offers,
          wait: true,
        });
        removeFullScreenLoader();
      } else {
        // If offers API is returning Error.
        logger.error('Error while calling the offers Api @params, @message', {
          '@params': params,
          '@message': offerResponse.data.message,
        });
      }
    }
  }

  render() {
    const { wait, myOffersList, myCouponsList } = this.state;

    if (!wait) {
      return (
        <div className="my-benefit-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    if (myCouponsList === null && myOffersList === null) {
      return null;
    }

    // Prepare the benefits list to render.
    let myBenefitsList = null;
    if (myCouponsList !== null && myOffersList !== null) {
      myBenefitsList = [...myCouponsList, ...myOffersList];
    } else if (myCouponsList !== null || myOffersList !== null) {
      myBenefitsList = myCouponsList || myOffersList;
    }

    return (
      <MyOffersAndVouchers myBenefitsList={myBenefitsList} />
    );
  }
}

export default MyBenefits;
