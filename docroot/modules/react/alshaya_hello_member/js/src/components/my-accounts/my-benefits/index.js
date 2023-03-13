import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { callHelloMemberApi, getHelloMemberCustomerInfo } from '../../../../../../js/utilities/helloMemberHelper';
import Loading from '../../../../../../js/utilities/loading';
import logger from '../../../../../../js/utilities/logger';
import MyOffersAndVouchers from './my-offers-vouchers';
import HappyBirthdayPopup from '../happy-birthday-popup';

class MyBenefits extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      myBenefitsList: null,
    };
  }

  async componentDidMount() {
    // Get customer info.
    const params = getHelloMemberCustomerInfo();
    if (!hasValue(params.error)) {
      // Get Coupon and Offer List.
      Promise.all([
        await callHelloMemberApi('helloMemberCouponsList', 'GET', params),
        await callHelloMemberApi('helloMemberOffersList', 'GET', params),
      ]).then(([couponResponse, offerResponse]) => {
        let myCouponsList = null;
        let myOffersList = null;
        if (hasValue(couponResponse.data) && !hasValue(couponResponse.data.error)) {
          myCouponsList = couponResponse.data.coupons;
        } else {
          // If coupons API is returning Error.
          logger.error('Error while calling the coupons Api @params, @message', {
            '@params': params,
            '@message': couponResponse.data.message,
          });
        }
        if (hasValue(offerResponse.data) && !hasValue(offerResponse.data.error)) {
          myOffersList = offerResponse.data.offers;
        } else {
          // If offers API is returning Error.
          logger.error('Error while calling the offers Api @params, @message', {
            '@params': params,
            '@message': offerResponse.data.message,
          });
        }

        // Prepare the benefits list to render.
        let myBenefitsList = null;
        if (myCouponsList !== null && myOffersList !== null) {
          myBenefitsList = [...myCouponsList, ...myOffersList];
        } else if (myCouponsList !== null || myOffersList !== null) {
          myBenefitsList = myCouponsList || myOffersList;
        }
        this.setState({
          myBenefitsList,
          wait: true,
        });
      });
    } else {
      // Set wait to true, and remove loader.
      this.setState({
        wait: true,
      });
    }
  }

  render() {
    const { wait, myBenefitsList } = this.state;
    const { currentPath } = drupalSettings.path;
    // Show Block title.
    document.querySelector('#my-accounts-hello-member').closest('.block').classList.remove('no-benefits');
    if (!wait) {
      return (
        <div className="my-benefit-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    if (myBenefitsList === null) {
      // Hide Block title.
      document.querySelector('#my-accounts-hello-member').closest('.block').classList.add('no-benefits');
      // Hide 'Benefits' title block when CLM return null.
      document.querySelector('#block-myaccountshellomemberblock').previousElementSibling.classList.add('hide-benefits-title');
      return null;
    }

    return (
      <>
        {currentPath.includes('user/') && (
          <HappyBirthdayPopup myBenefitsList={myBenefitsList} />
        )}
        <MyOffersAndVouchers myBenefitsList={myBenefitsList} />
      </>
    );
  }
}

export default MyBenefits;
