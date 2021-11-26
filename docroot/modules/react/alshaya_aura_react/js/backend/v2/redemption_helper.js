import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { getErrorResponse } from '../../../../js/utilities/error';
import logger from '../../../../js/utilities/logger';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';

/**
 * Prepare data based on action for redeem points.
 *
 * @param {object} data
 *   Object containing "action" and other values.
 * @param {string} cartId
 *   User's cart id.
 *
 * @returns {Object}
 *   The processed data on success or error in case of failure.
 */
const prepareRedeemPointsData = (data, cartId) => {
  let processedData = {};

  if (!hasValue(data.action)) {
    return processedData;
  }

  if (data.action === 'remove points') {
    processedData = {
      redeemPoints: {
        action: 'remove points',
        quote_id: hasValue(cartId) ? cartId : '',
      },
    };
  } else if (data.action === 'set points') {
    processedData = {
      redeemPoints: {
        action: 'set points',
        quote_id: hasValue(cartId) ? cartId : '',
        redeem_points: hasValue(data.redeemPoints) ? data.redeemPoints : '',
        converted_money_value: hasValue(data.moneyValue) ? data.moneyValue : '',
        currencyCode: hasValue(data.currencyCode) ? data.currencyCode : '',
        payment_method: 'aura_payment',
      },
    };

    // Check if required data is present in request.
    if (!hasValue(processedData.redeemPoints.redeem_points)
      || !hasValue(processedData.redeemPoints.converted_money_value)
      || !hasValue(processedData.redeemPoints.currencyCode)) {
      const message = 'Error while trying to redeem aura points. Redeem Points, Converted Money Value and Currency Code is required.';
      logger.error(`${message} . Data: @request_data`, {
        '@request_data': JSON.stringify(data),
      });
      return getErrorResponse(message, 404);
    }
  }

  return processedData;
};

/**
 * Redeem points.
 *
 * @param {string} cardNumber
 *   User's AURA card number.
 * @param {object} data
 *   Data to send to the API.
 *
 * @returns {Object}
 *   Points and other data in case of success or error in case of failure.
 */
const redeemPoints = (cardNumber, data) => callMagentoApi(`/V1/apc/${cardNumber}/redeem-points`, 'POST', data).then((response) => {
  if (hasValue(response.data.error)) {
    return response.data;
  }

  const responseData = {
    status: true,
    data: {
      paidWithAura: 0,
      balancePayable: 0,
      balancePoints: 0,
    },
  };

  if (hasValue(response.data.redeem_response)) {
    responseData.data.paidWithAura = hasValue(response.data.redeem_response.cashback_deducted_value)
      ? response.data.redeem_response.cashback_deducted_value
      : responseData.data.paidWithAura;

    responseData.data.balancePayable = hasValue(response.data.redeem_response.balance_payable)
      ? response.data.redeem_response.balance_payable
      : responseData.data.balancePayable;

    responseData.data.balancePoints = hasValue(response.data.redeem_response.house_hold_balance)
      ? response.data.redeem_response.house_hold_balance
      : responseData.data.balancePoints;
  }

  return responseData;
});

export {
  prepareRedeemPointsData,
  redeemPoints,
};
