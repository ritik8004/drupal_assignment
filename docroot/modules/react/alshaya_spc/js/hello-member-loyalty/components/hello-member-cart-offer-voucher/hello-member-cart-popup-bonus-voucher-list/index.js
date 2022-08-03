import React from 'react';
import moment from 'moment';
import { callHelloMemberApi } from '../../../../../../js/utilities/helloMemberHelper';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import logger from '../../../../../../js/utilities/logger';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import { getDefaultErrorMessage } from '../../../../../../js/utilities/error';

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
    }
    removeFullScreenLoader();
  };

  // On click clear all vouchers applied.
  const onClickClearAll = async () => {
    const { promotionType } = props;
    await promotionType('helloMemberRemovebonusVouchers');
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
          <a className="clear-btn" onClick={() => onClickClearAll()}>{Drupal.t('CLEAR ALL', {}, { context: 'hello_member' })}</a>
        </div>
      </form>
    </>
  );
};

export default HelloMemberCartPopupBonusVouchersList;
