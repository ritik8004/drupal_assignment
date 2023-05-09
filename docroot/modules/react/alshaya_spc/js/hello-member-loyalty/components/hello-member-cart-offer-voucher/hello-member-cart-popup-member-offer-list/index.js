import React from 'react';
import moment from 'moment';
import { callHelloMemberApi } from '../../../../../../js/utilities/helloMemberHelper';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import logger from '../../../../../../js/utilities/logger';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import { getDefaultErrorMessage } from '../../../../../../js/utilities/error';
import resetBenefitOptions from '../offer_voucher_helper';

const HelloMemberCartPopupMemberOfferList = (props) => {
  const { offers, totals } = props;
  // Get formatted expiry date.
  moment.locale(drupalSettings.path.currentLanguage);

  const handleChange = (e) => {
    const memberOffers = document.getElementsByName('radios');
    resetBenefitOptions(memberOffers, 'benefit_offer', 'change');
    if (e.target.type === 'radio' && e.target.checked === true) {
      Drupal.alshayaSeoGtmPushVoucherOfferSelect(e.target.getAttribute('offerDescription'), 'selected-offer-voucher');
    }
  };

  // handle submit.
  const handleSubmit = async (e) => {
    e.preventDefault();
    let seletedOffer = '';
    const offerDescriptionData = [];
    showFullScreenLoader();
    // get the list of user selected vouchers from voucher form.
    Object.entries(e.target).forEach(
      ([, value]) => {
        if (value.checked) {
          seletedOffer = value;
          offerDescriptionData.push(value.getAttribute('offerDescription'));
        }
      },
    );

    // api call to update the selected offers.
    const response = await callHelloMemberApi('addMemberOffersToCart', 'POST', {
      offerCode: seletedOffer.value,
      offerType: seletedOffer.getAttribute('data-offer') !== 'offer' ? seletedOffer.getAttribute('data-offer') : '',
    });
    // Display the message if discount amount reached threshold and not valid.
    document.getElementById('offer-err-msg').innerHTML = '';
    if (hasValue(response.data) && hasValue(response.data.error)) {
      // If coupon details API is returning Error.
      logger.error('Error while calling the apply coupon Api @message', {
        '@message': response.data.error_message,
      });
      // Display the error on offer cart popup.
      document.getElementById('offer-err-msg').innerHTML = response.data.error_message;
    } else {
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
            Drupal.alshayaSeoGtmPushVoucherOfferSelectedApply(offerDescriptionData.join(' | '), 'applied-offer-voucher');
          }
        });
      }
    }
    removeFullScreenLoader();
  };

  // On click clear all offers applied.
  const onClickClearAll = async () => {
    const { promotionType } = props;
    await promotionType('helloMemberRemoveOffers');
  };

  return (
    <>
      <form
        className="hello-member-promo-offers-validate-form"
        method="post"
        onSubmit={(e) => handleSubmit(e, props)}
      >
        <div className="hello-member-promo-tab-content-list radio-btn-list">
          <div id="offer-err-msg" className="offer-err-msg" />
          {offers.map((offer, index) => (
            // List offers excluding In-store offers on Discounts & Vouchers popup on cart page.
            hasValue(offer.tag)
              && (offer.tag === 'E' || offer.tag === 'O')
              && (
              <div key={offer.code} className="hello-member-promo-tab-cont-item">
                <input
                  type="radio"
                  id={`offer${index}`}
                  data-offer={hasValue(offer.type) ? offer.type : 'offer'}
                  name="radios"
                  value={offer.code}
                  defaultChecked={hasValue(totals.hmOfferCode)
                    ? totals.hmOfferCode === offer.code
                    : false}
                  onChange={handleChange}
                />
                <label htmlFor={`offer${index}`} className="radio-sim radio-label">
                  <div className="item-title">
                    <span className="title-text">{offer.description}</span>
                    <span className="item-sub-title">
                      {Drupal.t(
                        'Expires on @expiryDate',
                        { '@expiryDate': moment(new Date(hasValue(offer.end_date) ? offer.end_date : offer.expiry_date)).format('DD MMMM YYYY') },
                        { context: 'hello_member' },
                      )}
                    </span>
                  </div>
                </label>
              </div>
              )
          ))}
        </div>
        <div className="hello-member-promo-tab-cont-action">
          <input
            disabled={typeof totals.hmOfferCode === 'undefined'}
            type="submit"
            value={Drupal.t('APPLY OFFERS', {}, { context: 'hello_member' })}
            id="benefit_offer"
          />
          <a className="clear-btn" onClick={() => onClickClearAll()}>{Drupal.t('CLEAR ALL', {}, { context: 'hello_member' })}</a>
        </div>
      </form>
    </>
  );
};

export default HelloMemberCartPopupMemberOfferList;
