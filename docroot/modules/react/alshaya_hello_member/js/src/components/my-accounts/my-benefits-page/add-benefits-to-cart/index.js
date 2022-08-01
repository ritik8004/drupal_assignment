import React from 'react';
import ConditionalView from '../../../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import { callHelloMemberApi, getHelloMemberCustomerInfo } from '../../../../../../../js/utilities/helloMemberHelper';
import Loading from '../../../../../../../js/utilities/loading';
import logger from '../../../../../../../js/utilities/logger';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { findArrayElement } from '../../../../utilities';

class AddBenefitsToCart extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      appliedAlready: false,
      isEmptyCart: false,
    };
  }

  async componentDidMount() {
    const cartData = Drupal.getItemFromLocalStorage('cart_data');
    // Get customer info.
    const params = getHelloMemberCustomerInfo();
    if (hasValue(cartData) && cartData.cart.cart_id !== null && !hasValue(params.error)) {
      showFullScreenLoader();
      const responseData = await callHelloMemberApi('getCartData', 'GET', params);
      if (hasValue(responseData.data) && !hasValue(responseData.data.error)) {
        const { codeId, voucherType } = this.props;
        const voucherCode = responseData.data.cart.extension_attributes.applied_hm_voucher_codes;
        const appliedOfferCode = responseData.data.cart.extension_attributes.applied_hm_offer_code;
        const voucherDiscount = findArrayElement(responseData.data.totals.total_segments, 'voucher_discount');
        if (voucherType === 'BONUS_VOUCHER'
          && hasValue(voucherCode)
          && voucherCode === codeId
          && hasValue(voucherDiscount) && voucherDiscount.value > 0) {
          this.setState({
            appliedAlready: true,
          });
        } else if (hasValue(appliedOfferCode) && appliedOfferCode === codeId) {
          this.setState({
            appliedAlready: true,
          });
        }
        removeFullScreenLoader();
        this.setState({
          wait: true,
        });
      } else {
        // If get cart API is returning Error.
        logger.error('Error while calling the get cart Api @params, @message', {
          '@params': params,
          '@message': responseData.data.message,
        });
      }
    } else {
      this.setState({
        isEmptyCart: true,
        wait: true,
      });
    }
  }

  async handleClick() {
    const { isEmptyCart } = this.state;
    if (!isEmptyCart) {
      // Get customer info.
      const params = getHelloMemberCustomerInfo();
      if (!hasValue(params.error)) {
        showFullScreenLoader();
        const { title, codeId, voucherType } = this.props;
        if (voucherType === 'BONUS_VOUCHER') {
          params.voucherCodes = [codeId];
          const response = await callHelloMemberApi('addBonusVouchersToCart', 'POST', params);
          if (hasValue(response.data) && !hasValue(response.data.error)) {
            this.setState({
              wait: true,
              appliedAlready: true,
            });
            if (response.data) {
              document.getElementById('status-msg').innerHTML = Drupal.t('Added to your bag.', { context: 'hello_member' });
              if (hasValue(title)) {
                document.getElementById('disc-title').innerHTML = Drupal.t('@disc_title', { '@disc_title': title }, { context: 'hello_member' });
              }
              document.getElementById('hm-benefit-status-info').classList.toggle('hm-benefit-status-info-active');
              setTimeout(() => {
                document.getElementById('hm-benefit-status-info').classList.remove('hm-benefit-status-info-active');
              }, 5000);
            }
            removeFullScreenLoader();
          } else {
            // If coupon details API is returning Error.
            logger.error('Error while calling the apply coupon Api @params, @message', {
              '@params': params,
              '@message': response.data.message,
            });
          }
        } else {
          params.offerCode = codeId;
          params.offerType = voucherType;
          const response = await callHelloMemberApi('addMemberOffersToCart', 'POST', params);
          if (hasValue(response.data) && !hasValue(response.data.error)) {
            this.setState({
              wait: true,
              appliedAlready: true,
            });
            if (response.data) {
              document.getElementById('status-msg').innerHTML = Drupal.t('Added to your bag.', { context: 'hello_member' });
              if (hasValue(title)) {
                document.getElementById('disc-title').innerHTML = Drupal.t('@disc_title', { '@disc_title': title }, { context: 'hello_member' });
              }
              document.getElementById('hm-benefit-status-info').classList.toggle('hm-benefit-status-info-active');
              setTimeout(() => {
                document.getElementById('hm-benefit-status-info').classList.remove('hm-benefit-status-info-active');
              }, 5000);
            }
            removeFullScreenLoader();
          } else {
            // If member offers API is returning Error.
            logger.error('Error while calling the apply member offers Api @params, @message', {
              '@params': params,
              '@message': response.data.message,
            });
          }
        }
      }
    }
  }

  render() {
    const { appliedAlready, wait, isEmptyCart } = this.state;

    if (!wait) {
      return (
        <div className="my-benefit-page-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    return (
      <>
        <ConditionalView condition={!appliedAlready && isEmptyCart}>
          <div className="button-wide inactive">
            {Drupal.t('Your cart is empty', { context: 'hello_member' })}
          </div>
        </ConditionalView>
        <ConditionalView condition={appliedAlready}>
          <div className="button-wide inactive">
            {Drupal.t('This offer has been added to your bag', { context: 'hello_member' })}
          </div>
          <div className="hm-benefit-status-info" id="hm-benefit-status-info">
            <div id="status-msg" />
            <div id="disc-title" />
            <div className="status-icon" />
          </div>
        </ConditionalView>
        <ConditionalView condition={!appliedAlready && !isEmptyCart}>
          <div className="button-wide" onClick={() => this.handleClick()}>
            {getStringMessage('benefit_add_to_bag')}
          </div>
        </ConditionalView>
      </>
    );
  }
}

export default AddBenefitsToCart;
