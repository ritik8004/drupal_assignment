import React from 'react';
import moment from 'moment';
import { callHelloMemberApi } from '../../../../../../js/utilities/helloMemberHelper';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import logger from '../../../../../../js/utilities/logger';
import { fetchCartData } from '../../../../utilities/api/requests';

const HelloMemberCartPopupBonusVouchersList = (props) => {
  const { vouchers, totals } = props;

  // handle submit.
  const handleSubmit = async (e) => {
    e.preventDefault();
    showFullScreenLoader();
    const seletedVouchers = [];
    // get the list of user seleted vouchers from voucher form.
    Object.entries(e.target).forEach(
      ([, value]) => {
        if (value.checked) {
          seletedVouchers.push(value.value);
        }
      },
    );
    // api call to update the seleted vouchers.
    const response = await callHelloMemberApi('addBonusVouchersToCart', 'POST', { voucherCodes: seletedVouchers });
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
        className="hm-promo-vouchers-validate-form"
        method="post"
        id="hm-promo-vouchers-val-form"
        onSubmit={(e) => handleSubmit(e)}
      >
        <div className="hm-promo-tab-content-list">
          {vouchers.map((voucher, index) => (
            <div key={voucher.code} className="hm-promo-tab-cont-item">
              <input
                type="checkbox"
                id={`voucher${index}`}
                value={voucher.code}
                defaultChecked={typeof totals.hmAppliedVoucherCodes !== 'undefined' ? totals.hmAppliedVoucherCodes.split(',').includes(voucher.code) : false}
              />
              <label htmlFor={`voucher${index}`} className="checkbox-sim checkbox-label">
                <div className="item-title">
                  <span className="title-text">{voucher.description}</span>
                  <span className="item-sub-title">
                    {Drupal.t(
                      'Expires on @expiryDate',
                      { '@expiryDate': moment(new Date(voucher.expiry_date)).format('DD MMMM YYYY') },
                      { context: 'hello_member' },
                    )}
                  </span>
                </div>
              </label>
            </div>
          ))}
        </div>
        <div className="hm-promo-tab-cont-action">
          <input type="submit" value={Drupal.t('APPLY VOUCHERS', {}, { context: 'hello_member' })} />
          <a href="" className="clear-btn">{Drupal.t('CLEAR ALL', {}, { context: 'hello_member' })}</a>
        </div>
      </form>
    </>
  );
};

export default HelloMemberCartPopupBonusVouchersList;
