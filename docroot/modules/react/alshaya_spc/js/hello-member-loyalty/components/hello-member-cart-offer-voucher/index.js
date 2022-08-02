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
import ConditionalView from '../../../common/components/conditional-view';
import BecomeHelloMember from '../../../../../alshaya_hello_member/js/src/components/become-hello-member';
import { fetchCartData } from '../../../utilities/api/requests';

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
  }

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
      window.commerceBackend.removeCartDataFromStorage();
      const cartData = fetchCartData();
      if (cartData instanceof Promise) {
        cartData.then((result) => {
          if (result === 'Request aborted') {
            return;
          }
          // Store info in storage.
          window.commerceBackend.setCartDataInStorage({ cart: result });
          // Trigger event so that data can be passed to other components.
          const event = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => result } });
          document.dispatchEvent(event);
          // Trigger event to close the popup.
          const clearEvent = new CustomEvent('clearAllPromotions', { bubbles: true, detail: true });
          document.dispatchEvent(clearEvent);
        });
      }
    } else {
      // If coupon details API is returning Error.
      logger.error('Error while calling the apply coupon Api @message', {
        '@message': response.data.message,
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

    return (
      <>
        <div className="hello-member-promo-section">
          <a className="hm-promo-pop-link" onClick={() => this.onClickOpenPopup(true)}>
            {Drupal.t('Discounts & Vouchers', {}, { context: 'hello_member' })}
            <span className="promo-notification show-note" />
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
                <ConditionalView condition={isAnonymous}>
                  <BecomeHelloMember destination="cart" />
                </ConditionalView>
                <ConditionalView condition={!isAnonymous}>
                  <div className="hm-promo-modal-title">{Drupal.t('Discount', {}, { context: 'hello_member' })}</div>
                  <div className="hm-promo-modal-content">
                    <div className="error-info-section">&nbsp;</div>
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
                </ConditionalView>
              </Popup>
            </div>
          )}
        </div>
      </>
    );
  }
}

export default HelloMemberCartOffersVouchers;
