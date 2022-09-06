import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { getErrorResponse } from '../../../../js/utilities/error';
import { isUserAuthenticated } from '../../../../js/utilities/helper';
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
    return getErrorResponse('Action value is required.', 404);
  }

  const cartIdKey = isUserAuthenticated() ? 'quote_id' : 'masked_quote_id';

  if (data.action === 'remove points') {
    processedData = {
      redeemPoints: {
        action: 'remove points',
        [cartIdKey]: hasValue(cartId) ? cartId : '',
      },
    };
  } else if (data.action === 'set points') {
    processedData = {
      redeemPoints: {
        action: 'set points',
        [cartIdKey]: hasValue(cartId) ? cartId : '',
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
      return getErrorResponse(
        'Error while trying to prepare data to redeem aura points. Redeem Points, Converted Money Value and Currency Code is required.',
        404,
      );
    }
  } else {
    return getErrorResponse(
      `Error while trying to prepare data to redeem aura points. Action value "${data.action}" is not supported.`,
      404,
    );
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
const redeemPoints = (cardNumber, data) => callMagentoApi(isUserAuthenticated() ? `/V1/apc/${cardNumber}/redeem-points` : `/V1/guest/${cardNumber}/redeem-points`, 'POST', data).then((response) => {
  if (hasValue(response.data.error)) {
    return response.data;
  }

  const responseData = {
    status: true,
    data: {
      paidWithAura: 0,
      balancePayable: 0,
      balancePoints: 0,
      // Adding an extra total balance payable attribute, so that we can use this
      // in egift.
      // Doing this because while removing AURA points, we remove the Balance
      // Payable attribute from cart total.
      totalBalancePayable: 0,
    },
  };

  if (hasValue(response.data.redeem_response)) {
    const redeemResponseData = response.data.redeem_response;
    responseData.data.paidWithAura = hasValue(redeemResponseData.cashback_deducted_value)
      ? redeemResponseData.cashback_deducted_value
      : responseData.data.paidWithAura;

    responseData.data.balancePayable = hasValue(redeemResponseData.balance_payable)
      ? redeemResponseData.balance_payable
      : responseData.data.balancePayable;

    responseData.data.balancePoints = hasValue(redeemResponseData.house_hold_balance)
      ? redeemResponseData.house_hold_balance
      : responseData.data.balancePoints;
  }

  // Adding an extra total balance payable attribute, so that we can use this
  // in egift.
  // Doing this because while removing AURA points, we remove the Balance
  // Payable attribute from cart total.
  if (hasValue(response.data.totals)) {
    response.data.totals.total_segments.forEach((element) => {
      if (element.code === 'balance_payable') {
        responseData.data.totalBalancePayable = element.value;
      }
    });
  }

  return responseData;
});

export {
  prepareRedeemPointsData,
  redeemPoints,
};
