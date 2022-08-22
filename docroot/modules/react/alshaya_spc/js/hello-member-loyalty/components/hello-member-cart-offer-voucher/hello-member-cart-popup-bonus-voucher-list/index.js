import React from 'react';
import moment from 'moment';
import { callHelloMemberApi } from '../../../../../../js/utilities/helloMemberHelper';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import logger from '../../../../../../js/utilities/logger';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import { getDefaultErrorMessage } from '../../../../../../js/utilities/error';
import resetBenefitOptions from '../offer_voucher_helper';

const HelloMemberCartPopupBonusVouchersList = (props) => {
  const { vouchers, totals } = props;

  const handleChange = () => {
    const vouchersBonus = document.getElementsByName('vouchersBonus[]');
    resetBenefitOptions(vouchersBonus, 'benefit_voucher', 'change');
  };

  // handle submit.
  const handleSubmit = async (e) => {
    e.preventDefault();
    showFullScreenLoader();
    const seletedVouchers = [];
    // get the list of user selected vouchers from voucher form.
    Object.entries(e.target).forEach(
      ([, value]) => {
        if (value.checked) {
          seletedVouchers.push(value.value);
        }
      },
    );
    // api call to update the selected vouchers.
    const response = await callHelloMemberApi('addBonusVouchersToCart', 'POST', { voucherCodes: seletedVouchers });
    // Display the message if discount amount reached threshold and not valid.
    document.getElementById('voucher-err-msg').innerHTML = '';
    if (hasValue(response.data) && hasValue(response.data.error)) {
      // If coupon details API is returning Error.
      logger.error('Error while calling the apply coupon Api @message', {
        '@message': response.data.error_message,
      });
      // Display the error on voucher cart popup.
      document.getElementById('voucher-err-msg').innerHTML = response.data.error_message;
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
            if (result.data.totals.isHmAppliedVoucherRemoved) {
              document.getElementById('voucher-err-msg').innerHTML = Drupal.t('You have reached the maximum amount of added discounts.', { context: 'hello_member' });
            }
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
        className="hello-member-promo-vouchers-validate-form"
        method="post"
        onSubmit={(e) => handleSubmit(e)}
      >
        <div className="hello-member-promo-tab-content-list">
          <div id="voucher-err-msg" className="voucher-err-msg" />
          {vouchers.map((voucher, index) => (
            <div key={voucher.code} className="hello-member-promo-tab-cont-item">
              <input
                type="checkbox"
                id={`voucher${index}`}
                value={voucher.code}
                name="vouchersBonus[]"
                defaultChecked={typeof totals.hmAppliedVoucherCodes !== 'undefined' ? totals.hmAppliedVoucherCodes.split(',').includes(voucher.code) : false}
                onChange={handleChange}
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
        <div className="hello-member-promo-tab-cont-action">
          <input
            disabled={typeof totals.hmAppliedVoucherCodes === 'undefined'}
            type="submit"
            value={Drupal.t('APPLY VOUCHERS', {}, { context: 'hello_member' })}
            id="benefit_voucher"
          />
          <a className="clear-btn" onClick={() => onClickClearAll()}>{Drupal.t('CLEAR ALL', {}, { context: 'hello_member' })}</a>
        </div>
      </form>
    </>
  );
};

export default HelloMemberCartPopupBonusVouchersList;
