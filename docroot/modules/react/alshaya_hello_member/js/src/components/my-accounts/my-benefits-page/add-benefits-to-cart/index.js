import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import dispatchCustomEvent from '../../../../../../../js/utilities/events';
import { callHelloMemberApi, getHelloMemberCustomerInfo } from '../../../../../../../js/utilities/helloMemberHelper';
import Loading from '../../../../../../../js/utilities/loading';
import logger from '../../../../../../../js/utilities/logger';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { findArrayElement } from '../../../../utilities';
import { getDefaultErrorMessage } from '../../../../../../../js/utilities/error';

class AddBenefitsToCart extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      appliedAlready: false,
      isEmptyCart: false,
      isAPIError: false,
    };
  }

  async componentDidMount() {
    this.getUpdatedCartInfo();
  }

  async getUpdatedCartInfo(addTobag = false) {
    const responseData = await callHelloMemberApi('getCartData', 'GET');

    if (hasValue(responseData.data)
      && !hasValue(responseData.data.error)
      && responseData.data.cart.items_count > 0
      && responseData.data.totals.grand_total > 0
    ) {
      const { codeId, promotionType } = this.props;
      let isVoucherCodeAdded = false;
      let voucherCodes = responseData.data.cart.extension_attributes.applied_hm_voucher_codes;
      if (hasValue(voucherCodes)) {
        voucherCodes = voucherCodes.split(',');
        if (addTobag) {
          return voucherCodes;
        }
        isVoucherCodeAdded = voucherCodes.find((element) => (element === codeId));
      }
      const appliedOfferCode = responseData.data.cart.extension_attributes.applied_hm_offer_code;
      const voucherDiscount = findArrayElement(responseData.data.totals.total_segments, 'voucher_discount');

      if (promotionType === 'voucher'
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
      this.setState({
        wait: true,
      });
    } else {
      this.setState({
        isEmptyCart: true,
        wait: true,
      });
    }

    return null;
  }

  async handleClick() {
    const { isEmptyCart } = this.state;
    if (!isEmptyCart) {
      // Get customer info.
      const params = getHelloMemberCustomerInfo();
      if (!hasValue(params.error)) {
        showFullScreenLoader();
        const {
          title, codeId, offerType, promotionType,
        } = this.props;
        let isAddedToBag = false;
        let benefitType = 'offer';
        let response = null;
        if (promotionType === 'voucher') {
          const voucherCodes = await this.getUpdatedCartInfo(true);
          if (hasValue(voucherCodes)) {
            params.voucherCodes = voucherCodes;
            params.voucherCodes.push(codeId);
          } else {
            params.voucherCodes = [codeId];
          }
          response = await callHelloMemberApi('addBonusVouchersToCart', 'POST', params);
          if (hasValue(response.data) && !hasValue(response.data.error)) {
            isAddedToBag = true;
          } else {
            benefitType = 'coupon';
          }
        } else {
          params.offerCode = codeId;
          params.offerType = offerType;
          response = await callHelloMemberApi('addMemberOffersToCart', 'POST', params);
          if (hasValue(response.data) && !hasValue(response.data.error)) {
            isAddedToBag = true;
          }
        }
        // Display status message if Offer/Voucher is added successfully.
        if (isAddedToBag) {
          this.setState({
            wait: true,
            appliedAlready: true,
          });

          document.getElementById('status-msg').innerHTML = Drupal.t('Added to your bag.', {}, { context: 'hello_member' });
          if (hasValue(title)) {
            document.getElementById('disc-title').innerHTML = Drupal.t('@disc_title', { '@disc_title': title }, { context: 'hello_member' });
          }
          document.getElementById('hello-member-benefit-status-info').classList.toggle('hello-member-benefit-status-info-active');
          setTimeout(() => {
            document.getElementById('hello-member-benefit-status-info').classList.remove('hello-member-benefit-status-info-active');
          }, 5000);

          // Push add to basket data to gtm.
          Drupal.alshayaSeoGtmPushBenefitAddToBag({ title, promotionType });
          const cartData = window.commerceBackend.getCart(true);
          if (cartData instanceof Promise) {
            cartData.then((result) => {
              if (result.status !== 200
                && result.data === undefined
                && result.data.error !== undefined) {
                dispatchCustomEvent('spcCartMessageUpdate', {
                  type: 'error',
                  message: getDefaultErrorMessage(),
                });
              } else {
                // Calling refresh mini cart event so that storage is updated.
                dispatchCustomEvent('refreshMiniCart', {
                  data: () => result.data,
                });
                // Calling refresh cart event so that cart components
                // are refreshed.
                dispatchCustomEvent('refreshCart', {
                  data: () => result.data,
                });
              }
            });
          }
          removeFullScreenLoader();
        } else {
          // If coupon/offer API is returning Error.
          logger.error('Error while calling the apply @type Api @params, @message', {
            '@type': benefitType,
            '@params': params,
            '@message': (hasValue(response)) ? response.data.error_message : '',
          });

          this.setState({
            wait: true,
            isAPIError: true,
          });

          document.getElementById('error-msg').innerHTML = (hasValue(response)) ? response.data.error_message : '';
          document.getElementById('hello-member-benefit-status-info').classList.toggle('hello-member-benefit-status-info-active');
          setTimeout(() => {
            document.getElementById('hello-member-benefit-status-info').classList.remove('hello-member-benefit-status-info-active');
          }, 5000);
          removeFullScreenLoader();
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
            {getStringMessage('your_cart_empty')}
          </div>
        )}
        {(appliedAlready) && (
          <>
            <div className="button-wide inactive">
              {getStringMessage('offer_added_to_bag')}
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
