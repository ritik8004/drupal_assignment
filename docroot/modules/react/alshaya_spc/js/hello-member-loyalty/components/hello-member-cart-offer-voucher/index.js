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


class HelloMemberCartOffersVouchers extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openModal: false,
      vouchers: [],
      Offers: [],
    };
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
    await this.getCustomerOffersAndVouchers();
    this.setState({
      openModal,
    });
  };

  // on click close symbol close the popup.
  onClickClosePopup = async (openModal) => {
    this.setState({
      openModal,
      vouchers: [],
      Offers: [],
    });
  };

  render() {
    const {
      openModal,
      vouchers,
      Offers,
    } = this.state;
    const { totals } = this.props;
    const forceRenderTabPanel = true;

    return (
      <>
        <div className="hello-member-promo-section">
          <a className="hm-promo-pop-link" onClick={() => this.onClickOpenPopup(true)}>
            {Drupal.t('Discounts & Vouchers', {}, { context: 'hello_member' })}
            <span className="promo-notification" />
          </a>
          {openModal
          && (
            <div className="popup-container">
              <Popup
                open={openModal}
                closeOnDocumentClick={false}
                closeOnEscape={false}
              >
                <a className="close-modal" onClick={() => this.onClickClosePopup(false)} />
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
                      />
                    </TabPanel>
                    <TabPanel>
                      <HelloMemberCartPopupMemberOfferList
                        offers={Offers}
                        totals={totals}
                      />
                    </TabPanel>
                  </Tabs>
                </div>
              </Popup>
            </div>
          )}
        </div>
      </>
    );
  }
}

export default HelloMemberCartOffersVouchers;
