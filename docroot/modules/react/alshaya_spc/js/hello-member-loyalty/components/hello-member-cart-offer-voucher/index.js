import React from 'react';
import Popup from 'reactjs-popup';
import {
  Tab,
  Tabs,
  TabList,
  TabPanel,
} from 'react-tabs';
import { callHelloMemberApi } from '../../../../../js/utilities/helloMemberHelper';
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
    if (document.getElementById('voucher-err-msg').innerHTML !== ''
      && typeof data.totals.hmAppliedVoucherCodes === 'undefined') {
      document.getElementById('voucher-err-msg').innerHTML = '';
      const vouchers = document.getElementsByName('vouchersBonus[]');
      resetBenefitOptions(vouchers, 'benefit_voucher', 'submit');
    }
    // Handle errors from offer section.
    if (document.getElementById('offer-err-msg').innerHTML !== ''
      && typeof data.totals.applied_hm_offer_code === 'undefined') {
      document.getElementById('offer-err-msg').innerHTML = '';
      const offers = document.getElementsByName('radios');
      resetBenefitOptions(offers, 'benefit_offer', 'submit');
    }
  };

  /**
   * Helper function to get the customer offers and voucher.
   */
  getCustomerOffersAndVouchers = async () => {
    const { vouchers, Offers } = this.state;

    // Get coupons list.
    const couponResponse = await callHelloMemberApi('helloMemberCouponsList', 'GET');
    if (hasValue(couponResponse.data) && !hasValue(couponResponse.data.error)) {
      couponResponse.data.coupons.forEach((coupon) => {
        if (coupon.type === 'BONUS_VOUCHER') {
          vouchers.push(coupon);
        } else {
          Offers.push(coupon);
        }
      });
    } else {
      vouchers.push({ error_message: couponResponse.data.message });
      // If coupons API is returning Error.
      logger.error('Error while calling the coupons Api  @message', {
        '@message': couponResponse.data.message,
      });
    }

    // Get offers list.
    const offerResponse = await callHelloMemberApi('helloMemberOffersList', 'GET');
    if (hasValue(offerResponse.data) && !hasValue(offerResponse.data.error)) {
      Offers.push(...offerResponse.data.offers);
    } else {
      Offers.push({ error_message: offerResponse.data.message });
      // If offers API is returning Error.
      logger.error('Error while calling the offers Api @message', {
        '@message': offerResponse.data.message,
      });
    }
    this.setState({
      vouchers,
      Offers,
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
        <div className="hello-member-promo-section">
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
                  <BecomeHelloMember destination="cart" />
                )}
                {!isAnonymous
                && (
                  <span>
                    <div className="hello-member-promo-modal-title">{Drupal.t('Discount', {}, { context: 'hello_member' })}</div>
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
