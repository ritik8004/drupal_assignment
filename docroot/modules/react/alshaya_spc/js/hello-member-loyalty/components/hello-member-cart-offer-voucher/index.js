import React from 'react';
import Popup from 'reactjs-popup';
import {
  Tab,
  Tabs,
  TabList,
  TabPanel,
} from 'react-tabs';
import { callHelloMemberApi, displayErrorMessage } from '../../../../../js/utilities/helloMemberHelper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import logger from '../../../../../js/utilities/logger';
import HelloMemberCartPopupBonusVouchersList from './hello-member-cart-popup-bonus-voucher-list';
import HelloMemberCartPopupMemberOfferList from './hello-member-cart-popup-member-offer-list';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';
import BecomeHelloMember from '../../../../../js/utilities/components/become-hello-member';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import { getDefaultErrorMessage } from '../../../../../js/utilities/error';
import resetBenefitOptions from './offer_voucher_helper';

class HelloMemberCartOffersVouchers extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openModal: false,
      vouchers: [],
      Offers: [],
      isAnonymous: true,
      isVoucherRemoved: false,
      errorMessage: null,
    };
  }

  componentDidMount() {
    // Event listener on removing of vouchers and offers.
    document.addEventListener('clearAllPromotions', this.onClickClosePopup);
    // Listen to `helloMemberPointsLoaded` event which will update points summary block.
    document.addEventListener('refreshCart', this.handleErrorsOnBenefitsCartPopup, false);
  }

  // Handle the errors coming from API response and reset the options.
  handleErrorsOnBenefitsCartPopup = async (e) => {
    const data = e.detail.data();
    // Handle errors from bonus voucher section.
    const vouchers = document.getElementsByName('vouchersBonus[]');
    if (document.getElementById('voucher-err-msg') !== null
      && typeof data.totals.hmAppliedVoucherCodes === 'undefined') {
      document.getElementById('voucher-err-msg').innerHTML = null;
      resetBenefitOptions(vouchers, 'benefit_voucher', 'submit');
    } else if (data.totals.isHmAppliedVoucherRemoved) {
      // Unset all coupon vouchers if reached to max limit.
      resetBenefitOptions(vouchers, 'benefit_voucher', 'submit');
      document.getElementById('offer-err-msg').innerHTML = Drupal.t('You have reached the maximum amount of added discounts.', { context: 'hello_member' });
      this.setState({
        isVoucherRemoved: true,
      });
    } else if (document.getElementById('offer-err-msg') !== null
      && !hasValue(data.totals.hmOfferCode)) {
      // Handle errors from offer section.
      const { isVoucherRemoved } = this.state;
      const offers = document.getElementsByName('radios');
      document.getElementById('offer-err-msg').innerHTML = null;
      if (!isVoucherRemoved) {
        resetBenefitOptions(offers, 'benefit_offer', 'submit');
        this.setState({
          isVoucherRemoved: false,
        });
      }
    }
  };

  /**
   * Helper function to get the customer offers and voucher.
   */
  getCustomerOffersAndVouchers = async () => {
    const { vouchers, Offers } = this.state;
    let errorMessage = null;

    // Get coupons list.
    const couponResponse = await callHelloMemberApi('helloMemberCouponsList', 'GET');
    if (hasValue(couponResponse.data) && !hasValue(couponResponse.data.error)) {
      couponResponse.data.coupons.forEach((coupon) => {
        if (coupon.promotion_type === 'voucher') {
          vouchers.push(coupon);
        } else {
          Offers.push(coupon);
        }
      });
    } else if (hasValue(couponResponse.data.error) && couponResponse.data.error_code === 503) {
      // If coupons API is returning Error.
      errorMessage = couponResponse.data.error_message;
      logger.error('Error while calling the coupons Api  @message', {
        '@message': couponResponse.data.error_message,
      });
    }

    // Get offers list.
    const offerResponse = await callHelloMemberApi('helloMemberOffersList', 'GET');
    if (hasValue(offerResponse.data) && !hasValue(offerResponse.data.error)) {
      Offers.push(...offerResponse.data.offers);
    } else if (hasValue(offerResponse.data.error) && offerResponse.data.error_code === 503) {
      // If offers API is returning Error.
      errorMessage = offerResponse.data.error_message;
      logger.error('Error while calling the offers Api @message', {
        '@message': offerResponse.data.error_message,
      });
    }

    this.setState({
      vouchers,
      Offers,
      errorMessage,
    });
  }

  // On click link call offer and voucher api and open popup.
  onClickOpenPopup = async (openModal) => {
    showFullScreenLoader();
    if (isUserAuthenticated()) {
      await this.getCustomerOffersAndVouchers();
      this.setState({
        isAnonymous: false,
      });
    }

    this.setState({
      openModal,
    });
    removeFullScreenLoader();
    Drupal.alshayaSeoGtmPushVoucherLinkClick();
  };

  // on click close symbol close the popup.
  onClickClosePopup = async () => {
    this.setState({
      openModal: false,
      vouchers: [],
      Offers: [],
    });
  };

  // On click clear all offers applied.
  removeAppliedPromotions = async (promotionType) => {
    showFullScreenLoader();
    // Remove applied offers from customer.
    const response = await callHelloMemberApi(promotionType, 'DELETE');
    if (hasValue(response.data) && !hasValue(response.data.error)) {
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

            // Calling clear all promotion event to remove promotions from cart components.
            dispatchCustomEvent('clearAllPromotions', true);
          }
        });
      }
    } else {
      // If promotion delete API is returning Error.
      logger.error('Error while calling the apply coupon Api @message', {
        '@message': response.data.message,
      });
      dispatchCustomEvent('spcCartMessageUpdate', {
        type: 'error',
        message: getDefaultErrorMessage(),
      });
    }
    removeFullScreenLoader();
  };

  render() {
    const {
      openModal,
      vouchers,
      Offers,
      isAnonymous,
      errorMessage,
    } = this.state;
    const { totals } = this.props;
    const forceRenderTabPanel = true;
    // Count of Vouchers and offers applied.
    let DiscountVoucher = null;
    let notificationClass = 'show-note';
    // Add class to show applied voucher and offer message in popup.
    const additionalClasses = hasValue(totals.hmOfferCode) || hasValue(totals.hmAppliedVoucherCodes) ? 'error' : '';
    let appliedVouchers = '';
    // Applied voucher codes message in popup.
    if (hasValue(totals.hmAppliedVoucherCodes)) {
      appliedVouchers = (
        <span>
          {Drupal.t(
            'Bonus Voucher Applied (@hmVoucherCount)',
            { '@hmVoucherCount': totals.hmAppliedVoucherCodes.split(',').length },
            { context: 'hello_member' },
          )}
        </span>
      );
      DiscountVoucher = totals.hmAppliedVoucherCodes.split(',').length;
      notificationClass = '';
    }

    // Applied offer codes message in popup.
    let appliedOffers = '';
    if (hasValue(totals.hmOfferCode)) {
      appliedOffers = (
        <span>
          {Drupal.t(
            'Member Offers Applied (@hmOfferCount)',
            { '@hmOfferCount': totals.hmOfferCode.split(',').length },
            { context: 'hello_member' },
          )}
        </span>
      );
      DiscountVoucher += totals.hmOfferCode.split(',').length;
      notificationClass = '';
    }
    // if no vouchers or offer are applied show only text.
    let DiscountVouchersText = (
      <span>
        {Drupal.t('Discounts & Vouchers', {}, { context: 'hello_member' })}
      </span>
    );
    // if vouchers or offer are applied show text with count.
    if (hasValue(DiscountVoucher)) {
      DiscountVouchersText = (
        <span>
          {Drupal.t(
            'Discounts & Vouchers : @DiscountVoucher',
            { '@DiscountVoucher': DiscountVoucher },
            { context: 'hello_member' },
          )}
        </span>
      );
    }

    return (
      <>
        <div className="hello-member-promo-section clearfix">
          <a className="hello-member-promo-pop-link" onClick={() => this.onClickOpenPopup(true)}>
            {DiscountVouchersText}
            <span className={`promo-notification ${notificationClass}`} />
          </a>
          {openModal
          && (
            <div className="popup-container">
              <Popup
                open={openModal}
                closeOnDocumentClick={false}
                closeOnEscape={false}
              >
                <a className="close-modal" onClick={() => this.onClickClosePopup()} />
                {isAnonymous
                && (
                  <BecomeHelloMember destination={Drupal.url('cart')} />
                )}
                {!isAnonymous
                && (
                  <span>
                    <div className="hello-member-promo-modal-title">{Drupal.t('Discount', {}, { context: 'hello_member' })}</div>
                    {hasValue(errorMessage) ? (
                      <div className="clm-down-error-message">{displayErrorMessage(errorMessage)}</div>
                    ) : (
                      <div className="hello-member-promo-modal-content">
                        <div className={`error-info-section ${additionalClasses}`}>
                          {appliedOffers}
                          {appliedVouchers}
                        </div>
                        <Tabs forceRenderTabPanel={forceRenderTabPanel}>
                          <TabList>
                            <Tab>{Drupal.t('Bonus Vouchers', {}, { context: 'hello_member' })}</Tab>
                            <Tab>{Drupal.t('Member Offers', {}, { context: 'hello_member' })}</Tab>
                          </TabList>
                          <TabPanel>
                            <HelloMemberCartPopupBonusVouchersList
                              vouchers={vouchers}
                              totals={totals}
                              promotionType={this.removeAppliedPromotions}
                            />
                          </TabPanel>
                          <TabPanel>
                            <HelloMemberCartPopupMemberOfferList
                              offers={Offers}
                              totals={totals}
                              promotionType={this.removeAppliedPromotions}
                            />
                          </TabPanel>
                        </Tabs>
                      </div>
                    )}
                  </span>
                )}
              </Popup>
            </div>
          )}
        </div>
      </>
    );
  }
}

export default HelloMemberCartOffersVouchers;
