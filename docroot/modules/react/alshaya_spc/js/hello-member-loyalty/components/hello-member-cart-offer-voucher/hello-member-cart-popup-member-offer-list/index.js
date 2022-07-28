import React from 'react';
import { callHelloMemberApi } from '../../../../../../js/utilities/helloMemberHelper';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import logger from '../../../../../../js/utilities/logger';
import { fetchCartData } from '../../../../utilities/api/requests';

const HelloMemberCartPopupMemberOfferList = (props) => {
  const { offers, totals } = props;

  // handle submit.
  const handleSubmit = async (e) => {
    e.preventDefault();
    let seletedOffer = '';
    showFullScreenLoader();
    // get the list of user seleted vouchers from voucher form.
    Object.entries(e.target).forEach(
      ([, value]) => {
        if (value.checked) {
          seletedOffer = value;
        }
      },
    );
    // api call to update the seleted offers.
    const response = await callHelloMemberApi('addMemberOffersToCart', 'POST', {
      offerCode: seletedOffer.value,
      offerType: seletedOffer.getAttribute('data-offer') !== 'offer' ? seletedOffer.getAttribute('data-offer') : '',
    });
    if (!hasValue(response.data) && hasValue(response.data.error)) {
      // If coupon details API is returning Error.
      logger.error('Error while calling the apply coupon Api @message', {
        '@message': response.data.message,
      });
    } else {
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
        });
      }
    }
    removeFullScreenLoader();
  };

  return (
    <>
      <form
        className="hm-promo-offers-validate-form"
        method="post"
        id="hm-promo-offers-val-form"
        onSubmit={(e) => handleSubmit(e, props)}
      >
        <div className="hm-promo-tab-content-list radio-btn-list">
          {offers.map((offer, index) => (
            <div key={offer.code} className="hm-promo-tab-cont-item">
              <input
                type="radio"
                id={`offer${index}`}
                data-offer={typeof offer.type !== 'undefined' ? offer.type : 'offer'}
                name="radios"
                value={offer.code}
                defaultChecked={typeof totals.hmOfferCode !== 'undefined' ? totals.hmOfferCode === offer.code : false}
              />
              <label htmlFor={`offer${index}`} className="radio-sim radio-label">
                <div className="item-title">
                  <span className="title-text">{offer.category_name}</span>
                  <span className="item-sub-title">expires</span>
                </div>
              </label>
            </div>
          ))}
        </div>
        <div className="hm-promo-tab-cont-action">
          <input type="submit" value="APPLY OFFERS" />
          <a href="" className="clear-btn">{Drupal.t('CLEAR ALL')}</a>
        </div>
      </form>
    </>
  );
};

export default HelloMemberCartPopupMemberOfferList;
