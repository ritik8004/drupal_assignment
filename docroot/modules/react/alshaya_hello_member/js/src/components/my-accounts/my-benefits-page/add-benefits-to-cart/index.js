import React from 'react';
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
      voucherCodes: '',
      isAPIError: false,
    };
  }

  async componentDidMount() {
    const responseData = await callHelloMemberApi('getCartData', 'GET');
    if (hasValue(responseData.data)
      && !hasValue(responseData.data.error)
      && responseData.data.cart.items_count > 0
      && responseData.data.totals.grand_total > 0
    ) {
      const { codeId, voucherType } = this.props;
      let isVoucherCodeAdded = false;
      let voucherCodes = responseData.data.cart.extension_attributes.applied_hm_voucher_codes;
      if (hasValue(voucherCodes)) {
        voucherCodes = voucherCodes.split(',');
        isVoucherCodeAdded = voucherCodes.find((element) => (element === codeId));
        this.setState({ voucherCodes });
      }
      const appliedOfferCode = responseData.data.cart.extension_attributes.applied_hm_offer_code;
      const voucherDiscount = findArrayElement(responseData.data.totals.total_segments, 'voucher_discount');

      if (voucherType === 'BONUS_VOUCHER'
        && hasValue(isVoucherCodeAdded)
        && hasValue(voucherDiscount)) {
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
          const { voucherCodes } = this.state;
          if (voucherCodes !== '') {
            params.voucherCodes = voucherCodes;
            params.voucherCodes.push(codeId);
          } else {
            params.voucherCodes = [codeId];
          }
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
              document.getElementById('hello-member-benefit-status-info').classList.toggle('hello-member-benefit-status-info-active');
              setTimeout(() => {
                document.getElementById('hello-member-benefit-status-info').classList.remove('hello-member-benefit-status-info-active');
              }, 5000);
            }
            removeFullScreenLoader();
          } else {
            // If coupon details API is returning Error.
            logger.error('Error while calling the apply coupon Api @params, @message', {
              '@params': params,
              '@message': response.data.error_message,
            });
            this.setState({
              wait: true,
              isAPIError: true,
            });
            document.getElementById('error-msg').innerHTML = response.data.error_message;
            document.getElementById('hello-member-benefit-status-info').classList.toggle('hello-member-benefit-status-info-active');
            setTimeout(() => {
              document.getElementById('hello-member-benefit-status-info').classList.remove('hello-member-benefit-status-info-active');
            }, 5000);
            removeFullScreenLoader();
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
              document.getElementById('hello-member-benefit-status-info').classList.toggle('hello-member-benefit-status-info-active');
              setTimeout(() => {
                document.getElementById('hello-member-benefit-status-info').classList.remove('hello-member-benefit-status-info-active');
              }, 5000);
            }
            removeFullScreenLoader();
          } else {
            // If member offers API is returning Error.
            logger.error('Error while calling the apply member offers Api @params, @message', {
              '@params': params,
              '@message': response.data.message,
            });
            this.setState({
              wait: true,
              isAPIError: true,
            });
            document.getElementById('error-msg').innerHTML = response.data.error_message;
            document.getElementById('hello-member-benefit-status-info').classList.toggle('hello-member-benefit-status-info-active');
            setTimeout(() => {
              document.getElementById('hello-member-benefit-status-info').classList.remove('hello-member-benefit-status-info-active');
            }, 5000);
            removeFullScreenLoader();
          }
        }
      }
    }
  }

  render() {
    const {
      appliedAlready, wait, isEmptyCart, isAPIError,
    } = this.state;

    if (!wait) {
      return (
        <div className="my-benefit-page-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    return (
      <>
        {(!appliedAlready && isEmptyCart) && (
          <div className="button-wide inactive">
            {Drupal.t('Your cart is empty', { context: 'hello_member' })}
          </div>
        )}
        {(appliedAlready) && (
          <>
            <div className="button-wide inactive">
              {Drupal.t('This offer has been added to your bag', { context: 'hello_member' })}
            </div>
            <div className="hello-member-benefit-status-info" id="hello-member-benefit-status-info">
              <div id="status-msg" />
              <div id="disc-title" />
              <div className="status-icon" />
            </div>
          </>
        )}
        {(isAPIError) && (
          <div className="hello-member-benefit-status-info" id="hello-member-benefit-status-info">
            <div className="error" id="error-msg" />
          </div>
        )}
        {(!appliedAlready && !isEmptyCart) && (
          <div className="button-wide" onClick={() => this.handleClick()}>
            {getStringMessage('benefit_add_to_bag')}
          </div>
        )}
      </>
    );
  }
}

export default AddBenefitsToCart;
