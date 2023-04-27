import {
  getApiEndpoint,
  isUserAuthenticated,
  removeCartIdFromStorage,
  isRequestFromSocialAuthPopup,
} from './utility';
import logger from '../../../../js/utilities/logger';
import {
  getDefaultErrorMessage,
  getExceptionMessageType,
} from '../../../../js/utilities/error';
import {
  hasValue,
  isObject,
} from '../../../../js/utilities/conditionsUtility';
import getAgentDataForExtension from './smartAgent';
import collectionPointsEnabled from '../../../../js/utilities/pudoAramaxCollection';
import isAuraEnabled from '../../../../js/utilities/helper';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import { isEgiftCardEnabled } from '../../../../js/utilities/util';
import { cartContainsOnlyVirtualProduct } from '../../utilities/egift_util';
import { getTopUpQuote } from '../../../../js/utilities/egiftCardHelper';
import isHelloMemberEnabled, { isAuraIntegrationEnabled } from '../../../../js/utilities/helloMemberHelper';
import { isFreeGiftProduct } from '../../../../js/utilities/price';
import dispatchCustomEvent from '../../../../js/utilities/events';

window.authenticatedUserCartId = 'NA';

window.commerceBackend = window.commerceBackend || {};

/**
 * Stores the raw cart data object into the storage.
 *
 * @param {object} data
 *   The raw cart data object.
 */
window.commerceBackend.setRawCartDataInStorage = (data) => {
  Drupal.alshayaSpc.staticStorage.set('cart_raw', data);
};

/**
 * Fetches the raw cart data object from the static storage.
 */
window.commerceBackend.getRawCartDataFromStorage = () => Drupal.alshayaSpc.staticStorage.get('cart_raw');

/**
 * Stores skus and quantities.
 */
const staticStockMismatchSkusData = [];

/**
 * Sets the static array so that it can be processed later.
 *
 * @param {string} sku
 *   The SKU value.
 * @param {integer} quantity
 *   The quantity of the SKU.
 */
const matchStockQuantity = (sku, quantity = 0) => {
  staticStockMismatchSkusData[sku] = quantity;
};

/**
 * Gets the cart data.
 *
 * @returns {object|null}
 *   Processed cart data else null.
 */
window.commerceBackend.getCartDataFromStorage = () => Drupal.alshayaSpc.staticStorage.get('cart');

/**
 * Sets the cart data to storage.
 *
 * @param data
 *   The cart data.
 */
window.commerceBackend.setCartDataInStorage = (data) => {
  const cartInfo = { ...data };
  cartInfo.last_update = new Date().getTime();
  Drupal.alshayaSpc.staticStorage.set('cart', cartInfo);

  // Store masked cart id for Global-e integration for checkout page.
  // We need to keep this data on a dedicated key because cart_data is
  // not available in local storage on checkout page.
  if (hasValue(cartInfo.cart)
    && hasValue(cartInfo.cart.ge_cart_id)
  ) {
    Drupal.addItemInLocalStorage(
      'ge_cart_id',
      cartInfo.cart.ge_cart_id,
      parseInt(drupalSettings.alshaya_spc.cart_storage_expiration, 10) * 60,
    );
    // Delete from cart object.
    delete (cartInfo.cart.ge_cart_id);
  }

  // @todo find better way to get this using commerceBackend.
  // As of now it not possible to get it on page load before all
  // other JS is executed and for all other JS refactoring
  // required is huge.
  Drupal.addItemInLocalStorage(
    'cart_data',
    cartInfo,
    parseInt(drupalSettings.alshaya_spc.cart_storage_expiration, 10) * 60,
  );
};

/**
 * Removes the cart data from storage.
 *
 * @param {boolean}
 *  Whether we should remove all items.
 */
window.commerceBackend.removeCartDataFromStorage = (resetAll = false) => {
  Drupal.alshayaSpc.staticStorage.clear();

  Drupal.removeItemFromLocalStorage('cart_data');

  // Remove Add to cart PDP count.
  Drupal.removeItemFromLocalStorage('skus_added_from_pdp');

  // Remove last selected payment on page load.
  // We use this to ensure we trigger events for payment method
  // selection at-least once and not more than once.
  Drupal.removeItemFromLocalStorage('last_selected_payment');

  if (resetAll) {
    removeCartIdFromStorage();
  }
};

/**
 * Global constants.
 */

// Magento method, to set for 2d vault (tokenized card) transaction.
// @See CHECKOUT_COM_VAULT_METHOD in \App\Service\CheckoutCom\APIWrapper
const checkoutComVaultMethod = () => 'checkout_com_cc_vault';

// Magento method, to append for UAPAPI vault (tokenized card) transaction.
// @See CHECKOUT_COM_UPAPI_VAULT_METHOD in \App\Service\CheckoutCom\APIWrapper
const checkoutComUpapiVaultMethod = () => 'checkout_com_upapi_vault';

/**
 * Format the cart data to have better structured array.
 *
 * @param {object} cartData
 *   Cart response from Magento.
 *
 * @return {object}
 *   Formatted / processed cart.
 */
const formatCart = (cartData) => {
  // As of now we don't need deep clone of the passed object.
  // As Method calls are storing the result on the same object.
  // For ex - response.data = formatCart(response.data);
  // if in future, method call is storing result on any other object.
  // Clone of the argument passed, will be needed which can be achieved using.
  // const data = JSON.parse(JSON.stringify(cartData));
  const data = cartData;
  // Check if there is no cart data.
  if (!hasValue(data.cart) || !isObject(data.cart)) {
    return data;
  }

  // Move customer data to root level.
  if (hasValue(data.cart.customer)) {
    data.customer = data.cart.customer;
    delete data.cart.customer;
  }

  // Format addresses.
  if (hasValue(data.customer) && hasValue(data.customer.addresses)) {
    data.customer.addresses = data.customer.addresses.map((address) => {
      const item = { ...address };
      delete item.id;
      item.region = address.region_id;
      item.customer_address_id = address.id;
      return item;
    });
  }

  // Format shipping info.
  if (hasValue(data.cart.extension_attributes)) {
    if (hasValue(data.cart.extension_attributes.shipping_assignments)) {
      if (hasValue(data.cart.extension_attributes.shipping_assignments[0].shipping)) {
        data.shipping = data.cart.extension_attributes.shipping_assignments[0].shipping;
        delete data.cart.extension_attributes.shipping_assignments;
      }
    }
  } else {
    data.shipping = {};
  }

  let shippingMethod = '';
  if (hasValue(data.shipping)) {
    if (hasValue(data.shipping.method)) {
      shippingMethod = data.shipping.method;
    }
    if (hasValue(shippingMethod) && shippingMethod.indexOf('click_and_collect') >= 0) {
      data.shipping.type = 'click_and_collect';
    } else if (isUserAuthenticated()
      && (typeof data.shipping.address.customer_address_id === 'undefined' || !(data.shipping.address.customer_address_id))) {
      // Ignore the address if not available from address book for customer.
      data.shipping = {};
    } else {
      data.shipping.type = 'home_delivery';
    }
  }

  if (hasValue(data.shipping) && hasValue(data.shipping.extension_attributes)) {
    const extensionAttributes = data.shipping.extension_attributes;
    if (hasValue(extensionAttributes.click_and_collect_type)) {
      data.shipping.clickCollectType = extensionAttributes.click_and_collect_type;
    }
    if (hasValue(extensionAttributes.store_code)) {
      data.shipping.storeCode = extensionAttributes.store_code;
    }

    // Check if inter country transfer feature is enabled and have delivery date;
    if (hasValue(extensionAttributes.oms_lead_time)) {
      data.shipping.ictDate = extensionAttributes.oms_lead_time;
    }

    // If collection point feature is enabled, extract collection point details
    // from shipping data.
    if (collectionPointsEnabled()) {
      data.shipping.collection_point = extensionAttributes.collection_point;
      data.shipping.pickup_date = extensionAttributes.pickup_date;
      data.shipping.price_amount = extensionAttributes.price_amount;
      data.shipping.pudo_available = extensionAttributes.pudo_available;
    }

    delete data.shipping.extension_attributes;
  }

  // Initialise payment data holder.
  data.payment = {};

  // When shipping method is empty, Set shipping and billing info to empty,
  // so that we can show empty shipping and billing component in react
  // to allow users to fill addresses.
  if (shippingMethod === '') {
    data.shipping = {};
    // Bypass this empty settings of billing address for egift enabled and
    // containing only virtual item in cart.
    if (!cartContainsOnlyVirtualProduct(data.cart)) {
      data.cart.billing_address = {};
    }
  }
  return data;
};

/**
 * Static cache for getProductStatus().
 *
 * @type {object}
 */
let staticProductStatus = {};

/**
 * Get data related to product status.
 * @todo Allow bulk requests, see CORE-32123
 *
 * @param {Promise<string|null>} sku
 *  The sku for which the status is required.
 * @param {string} parentSKU
 *  The parent sku value.
 */
const getProductStatus = async (sku, parentSKU) => {
  if (typeof sku === 'undefined' || !sku) {
    return null;
  }

  // Return from static, if available.
  if (Drupal.hasValue(staticProductStatus) && Drupal.hasValue(staticProductStatus[sku])) {
    return staticProductStatus[sku];
  }

  staticProductStatus[sku] = await window.commerceBackend.getProductStatus(sku, parentSKU);

  return staticProductStatus[sku];
};

/**
 * Clears static cache for product status data.
 */
const clearProductStatusStaticCache = () => {
  staticProductStatus = {};
};

/**
 * Transforms cart data to match the data structure from middleware.
 *
 * @param {object} cartData
 *   The cart data object.
 *
 * @returns {object}
 *   The processed cart data.
 */
const getProcessedCartData = async (cartData) => {
  // In case of errors, return the error object.
  if (hasValue(cartData) && hasValue(cartData.error) && cartData.error) {
    return cartData;
  }

  // If the cart object is empty, return null.
  if (!hasValue(cartData) || !hasValue(cartData.cart)) {
    return null;
  }

  const cartId = window.commerceBackend.getCartId();
  const data = {
    cart_id: cartId,
    cart_id_int: cartData.cart.id,
    ge_cart_id: cartData.cart.extension_attributes.cart_id,
    uid: (window.drupalSettings.user.uid) ? window.drupalSettings.user.uid : 0,
    langcode: window.drupalSettings.path.currentLanguage,
    customer: cartData.customer,
    coupon_code: typeof cartData.totals.coupon_code !== 'undefined' ? cartData.totals.coupon_code : '',
    // Promotion rule ids applicable for the cart items,
    // Which is used to show cart message that explains how to avail the discounts in the cart.
    appliedRules: cartData.cart.applied_rule_ids,
    // Discounts applied rule ids, because of these rules the price discounts present in the cart,
    // Which used to show the discounts tooltip.
    appliedRulesWithDiscount: typeof cartData.cart.extension_attributes !== 'undefined' ? cartData.cart.extension_attributes.applied_rule_ids_with_discount : '',
    items_qty: cartData.cart.items_qty,
    cart_total: 0,
    minicart_total: 0,
    surcharge: cartData.cart.extension_attributes.surcharge,
    response_message: null,
    in_stock: true,
    is_error: false,
    // Flag to not show the dynamic promotions on cart page, if exclusive promo/coupon
    // is applied we will get the has_exclusive_coupon flag value as true from MDC,
    // and we will not render the dynamic promos.
    has_exclusive_coupon: (typeof cartData.cart.extension_attributes.has_exclusive_coupon !== 'undefined') ? cartData.cart.extension_attributes.has_exclusive_coupon : false,
    // Free shipping text which is used to show the message on free delivery
    // usp banner in the cart, if free delivery usp is enabled. If the key
    // "free_shipping_text" is either missing or has an empty value in magento api,
    // we consider that free delivery usp feature is disabled from MDC.
    free_shipping_text: (typeof cartData.cart.extension_attributes.free_shipping_text !== 'undefined') ? cartData.cart.extension_attributes.free_shipping_text : null,
    stale_cart: (typeof cartData.stale_cart !== 'undefined') ? cartData.stale_cart : false,
    totals: {
      subtotal_incl_tax: cartData.totals.subtotal_incl_tax,
      base_grand_total: cartData.totals.base_grand_total,
      base_grand_total_without_surcharge: cartData.totals.base_grand_total,
      discount_amount: cartData.totals.discount_amount,
      surcharge: 0,
      items: cartData.totals.items,
      allExcludedForAdcard: cartData.totals.extension_attributes.is_all_items_excluded_for_adv_card,
    },
    items: [],
    ...(collectionPointsEnabled() && hasValue(cartData.shipping))
    && { collection_charge: cartData.shipping.price_amount || '' },
  };

  // Add loyalty card and loyalty type for hello member loyalty.
  if (isHelloMemberEnabled()) {
    data.loyalty_card = cartData.cart.extension_attributes.loyalty_card || '';
    data.loyalty_type = cartData.cart.extension_attributes.loyalty_type || '';
  }

  // Totals.
  if (typeof cartData.totals.base_grand_total !== 'undefined') {
    data.cart_total = cartData.totals.base_grand_total;
    data.minicart_total = cartData.totals.base_grand_total;
  }

  // Total segments.
  cartData.totals.total_segments.forEach((element) => {
    // If subtotal order total available.
    if (drupalSettings.alshaya_spc.subtotal_after_discount
      && element.code === 'subtotal_with_discount_incl_tax') {
      data.totals.subtotalWithDiscountInclTax = element.value;
    }
    // If Aura enabled, add aura related details.
    // If Egift card is enabled get balance_payable.
    if (isAuraEnabled() || isEgiftCardEnabled() || isAuraIntegrationEnabled()) {
      if (element.code === 'balance_payable') {
        data.totals.balancePayable = element.value;
        // Adding an extra total balance payable attribute, so that we can use
        // this in egift.
        // Doing this because while removing AURA points, we remove the Balance
        // Payable attribute from cart total.
        data.totals.totalBalancePayable = element.value;
      }
      if (element.code === 'aura_payment') {
        data.totals.paidWithAura = element.value;
      }
    }

    // if the hello member feature is enabled, add hm_voucher_discount from
    // total segments to hmVoucherDiscount for showing in order summary block
    // on cart and checkout pages. This will be visible in order summary only if
    // extension_attributes.applied_hm_voucher_codes does have value(s).
    if (isHelloMemberEnabled() && element.code === 'voucher_discount') {
      data.totals.hmVoucherDiscount = element.value;
    }
  });

  if (isAuraEnabled()) {
    data.loyaltyCard = cartData.cart.extension_attributes.loyalty_card || '';
  }

  // Check if online booking is enabled and have confirmation number.
  if (hasValue(cartData.cart.extension_attributes)
    && hasValue(cartData.cart.extension_attributes.hfd_hold_confirmation_number)) {
    data.hfd_hold_confirmation_number = cartData
      .cart.extension_attributes.hfd_hold_confirmation_number;
  }

  // Check if COD payment mobile verification flag is present in cart extensions.
  if (hasValue(cartData.cart.extension_attributes)
    && typeof cartData
      .cart.extension_attributes.mobile_number_verified !== 'undefined') {
    data.cod_mobile_number_verified = cartData
      .cart.extension_attributes.mobile_number_verified;
  }

  // Check if inter country transfer feature is enabled and have delivery date.
  if (hasValue(cartData.shipping) && hasValue(cartData.shipping.ictDate)) {
    data.ictDate = cartData.shipping.ictDate;
  }

  // If egift card enabled, add the hps_redeemed_amount
  // add hps_redemption_type to cart.
  if (isEgiftCardEnabled()) {
    data.totals.egiftRedeemedAmount = 0;
    data.totals.egiftRedemptionType = '';
    data.totals.egiftCardNumber = '';
    data.totals.egiftCurrentBalance = 0;
    if (hasValue(cartData.totals.extension_attributes.hps_redeemed_amount)) {
      data.totals.egiftRedeemedAmount = cartData.totals.extension_attributes.hps_redeemed_amount;
    }
    if (hasValue(cartData.totals.extension_attributes.hps_redemption_type)) {
      data.totals.egiftRedemptionType = cartData.totals.extension_attributes.hps_redemption_type;
    }
    if (hasValue(cartData.cart.extension_attributes.hps_redemption_card_number)) {
      data.totals.egiftCardNumber = cartData.cart.extension_attributes.hps_redemption_card_number;
    }
    if (hasValue(cartData.totals.extension_attributes.hps_current_balance)) {
      data.totals.egiftCurrentBalance = cartData.totals.extension_attributes.hps_current_balance;
    }
  }

  // Check if the hello member feature is enabled and add below hello member
  // extension_attributes to the totals for display info under order summary
  // block on cart, checkout pages.
  // - applied_hm_voucher_codes
  // - applied_hm_offer_code
  if (isHelloMemberEnabled() && hasValue(cartData.cart.extension_attributes)) {
    // Add applied_hm_voucher_codes and hm_voucher_discount to totals.
    if (hasValue(cartData.cart.extension_attributes.applied_hm_voucher_codes)) {
      // eslint-disable-next-line max-len
      data.totals.hmAppliedVoucherCodes = cartData.cart.extension_attributes.applied_hm_voucher_codes;
    }
    // Add is_hm_applied_voucher_removed to totals.
    if (typeof cartData.totals.extension_attributes.is_hm_applied_voucher_removed !== 'undefined') {
      // eslint-disable-next-line max-len
      data.totals.isHmAppliedVoucherRemoved = cartData.totals.extension_attributes.is_hm_applied_voucher_removed;
    }

    // Add applied_hm_voucher_codes and hm_voucher_discount to totals.
    if (hasValue(cartData.cart.extension_attributes.applied_hm_offer_code)) {
      data.totals.hmOfferCode = cartData.cart.extension_attributes.applied_hm_offer_code;
    }
  }

  if (!hasValue(cartData.shipping) || !hasValue(cartData.shipping.method)) {
    // We use null to show "Excluding Delivery".
    data.totals.shipping_incl_tax = null;
  } else if (cartData.shipping.type !== 'click_and_collect') {
    // For click_n_collect we don't want to show this line at all.
    data.totals.shipping_incl_tax = (hasValue(cartData.totals.shipping_incl_tax))
      ? cartData.totals.shipping_incl_tax
      : 0;
  }

  if (typeof cartData.cart.extension_attributes.surcharge !== 'undefined' && cartData.cart.extension_attributes.surcharge.amount > 0 && cartData.cart.extension_attributes.surcharge.is_applied) {
    data.totals.surcharge = cartData.cart.extension_attributes.surcharge.amount;
    // We don't show surcharge amount on cart total and on mini cart.
    data.totals.base_grand_total_without_surcharge -= data.totals.surcharge;
    data.minicart_total -= data.totals.surcharge;
  }

  if (typeof cartData.response_message[1] !== 'undefined') {
    data.response_message = {
      status: cartData.response_message[1],
      msg: cartData.response_message[0],
    };
  }

  if (typeof cartData.cart.items !== 'undefined' && cartData.cart.items.length > 0) {
    data.items = {};
    for (let i = 0; i < cartData.cart.items.length; i++) {
      const item = cartData.cart.items[i];
      const hasParentSku = hasValue(item.extension_attributes)
        && hasValue(item.extension_attributes.parent_product_sku);
      const parentSKU = (item.product_type === 'configurable' && hasParentSku)
        ? item.extension_attributes.parent_product_sku
        : null;
      // @todo check why item id is different from v1 and v2 for
      // https://local.alshaya-bpae.com/en/buy-21st-century-c-1000mg-prolonged-release-110-tablets-red.html

      // Set isEgiftCard for virtual product.
      let isEgiftCard = false;
      let itemKey = item.sku;
      if (isEgiftCardEnabled() && item.product_type !== 'undefined' && item.product_type === 'virtual') {
        isEgiftCard = true;
        // to show multiple egift product in cart seperately changing this to item_id,
        // as sku will be the same.
        itemKey = item.item_id;
      }
      data.items[itemKey] = {
        id: item.item_id,
        title: item.name,
        qty: item.qty,
        price: item.price,
        sku: item.sku,
        freeItem: false,
        finalPrice: item.price,
        parentSKU,
        isEgiftCard,
      };

      // Get stock data on cart and checkout pages.
      const spcPageType = window.spcPageType || '';
      // No need of stock data for egift card products.
      if ((spcPageType === 'cart' || spcPageType === 'checkout') && !isEgiftCard) {
        // Suppressing the lint error for now.
        // eslint-disable-next-line no-await-in-loop
        const stockInfo = await getProductStatus(item.sku, parentSKU);

        // Do not show the products which are not available in
        // system but only available in cart.
        if (!hasValue(stockInfo) || hasValue(stockInfo.error)) {
          logger.warning('Product not available in system but available in cart. SKU: @sku, CartId: @cartId, StockInfo: @stockInfo.', {
            '@sku': item.sku,
            '@cartId': data.cart_id_int,
            '@stockInfo': JSON.stringify(stockInfo || {}),
          });

          delete data.items[itemKey];
          // eslint-disable-next-line no-continue
          continue;
        }

        data.items[itemKey].in_stock = stockInfo.in_stock;
        data.items[itemKey].stock = stockInfo.stock;

        // If any item is OOS.
        if (!hasValue(stockInfo.in_stock) || !hasValue(stockInfo.stock)) {
          data.in_stock = false;
        }
      }

      if (typeof item.extension_attributes !== 'undefined') {
        if (typeof item.extension_attributes.error_message !== 'undefined') {
          data.items[itemKey].error_msg = item.extension_attributes.error_message;
          data.is_error = true;
        }

        if (typeof item.extension_attributes.promo_rule_id !== 'undefined') {
          data.items[itemKey].promoRuleId = item.extension_attributes.promo_rule_id;
        }
        // Extension attributes information for eGift products.
        if (isEgiftCard && typeof item.extension_attributes.is_egift !== 'undefined' && item.extension_attributes.is_egift) {
          if (typeof item.extension_attributes.egift_options !== 'undefined') {
            data.items[itemKey].egiftOptions = item.extension_attributes.egift_options;
          }
          if (typeof item.extension_attributes.product_media[0] !== 'undefined') {
            data.items[itemKey].media = item.extension_attributes.product_media[0].file;
          }

          // Get eGift product name for GTM datalayer.
          // Since, we need to pass data to GTM only in English translation
          // we use 'topup_card_name_en' field for eGift card topup and for
          // eGift card use 'item_name_en'.
          // See [CORE-42487] for API updates reference.
          if (hasValue(item.extension_attributes.is_topup)
            && item.extension_attributes.is_topup === '1') {
            data.items[itemKey].itemGtmName = hasValue(item.extension_attributes.topup_card_name_en)
              ? item.extension_attributes.topup_card_name_en : '';
          } else {
            data.items[itemKey].itemGtmName = hasValue(item.extension_attributes.item_name_en)
              ? item.extension_attributes.item_name_en : '';
          }

          // If eGift product is top-up card add check to the product item.
          if (typeof item.extension_attributes.is_topup !== 'undefined' && item.extension_attributes.is_topup) {
            data.items[itemKey].isTopUp = (item.extension_attributes.is_topup === '1');
          }

          // If item is a top-up card add the card number used for top-up.
          data.items[itemKey].topupCardNumber = (
            hasValue(item.extension_attributes.topup_card_number)
          ) ? item.extension_attributes.topup_card_number : null;

          // If item is a top-up card add the product name used for top-up.
          data.items[itemKey].productName = (
            hasValue(item.extension_attributes.topup_card_name)
          ) ? item.extension_attributes.topup_card_name : null;
        }

        // Add all size attributes from cart response in size group.
        if (hasValue(drupalSettings.alshaya_spc.sizeGroupAttribute)) {
          let sizeGroup = '';
          Object.keys(item.extension_attributes).forEach((key) => {
            const sizeGroupAlternates = drupalSettings.alshaya_spc.sizeGroupAlternates[key];
            if (hasValue(sizeGroupAlternates)) {
              sizeGroup += `${item.extension_attributes[key]}(${sizeGroupAlternates}) `;
            }
          });
          data.items[itemKey].sizeGroup = sizeGroup;
        }
      }

      // This is to determine whether item to be shown free or not in cart.
      cartData.totals.items.forEach((totalItem) => {
        // If total price of item matches discount, we mark as free.
        if (item.item_id === totalItem.item_id) {
          // Final price to use.
          // For the free gift the key 'price_incl_tax' is missing.
          if (typeof totalItem.price_incl_tax !== 'undefined') {
            data.items[itemKey].finalPrice = totalItem.price_incl_tax;
          } else {
            data.items[itemKey].finalPrice = totalItem.base_price;
          }

          // Free Item is only for free gift products which are having
          // price 0/0.01, rest all are free but still via different rules.
          if (isFreeGiftProduct(totalItem.base_price) && typeof totalItem.extension_attributes !== 'undefined' && typeof totalItem.extension_attributes.amasty_promo !== 'undefined') {
            data.items[itemKey].freeItem = true;
          }
        }
      });
    }
  } else {
    data.items = [];
  }
  return data;
};

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
const isAnonymousUserWithoutCart = () => {
  const cartId = window.commerceBackend.getCartId();
  if (cartId === null || typeof cartId === 'undefined') {
    if (window.drupalSettings.userDetails.customerId === 0) {
      return true;
    }
  }
  return false;
};

const clearInvalidCart = () => {
  const isAssociatingCart = Drupal.alshayaSpc.staticStorage.get('associating_cart') || false;
  if (window.commerceBackend.getCartIdFromStorage() && !isAssociatingCart) {
    logger.warning('Removing cart from local storage and reloading.');

    // Remove cart_id from storage.
    removeCartIdFromStorage();

    // Reload the page now that we have removed cart id from storage.
    // eslint-disable-next-line no-self-assign
    window.location.href = window.location.href;
  }
};

/**
 * Calls the cart get API.
 *
 * @param {boolean} force
 *   Flag for static/fresh cartData.
 *
 * @returns {Promise<AxiosPromise<object>>|null}
 *   A promise object containing the cart or null.
 */
const getCart = async (force = false) => {
  // If request is from SocialAuth Popup, restrict further processing.
  // we don't want magento API calls happen on popup, As this is causing issues
  // in processing parent pages.
  if (isRequestFromSocialAuthPopup()) {
    return null;
  }

  if (!force && window.commerceBackend.getRawCartDataFromStorage() !== null) {
    return { data: window.commerceBackend.getRawCartDataFromStorage() };
  }

  if (isAnonymousUserWithoutCart()) {
    return null;
  }

  const cartId = window.commerceBackend.getCartId();
  const response = await callMagentoApi(getApiEndpoint('getCart', { cartId }), 'GET', {});

  response.data = response.data || {};

  if (hasValue(response.data.error)) {
    if ((hasValue(response.status) && response.status === 404)
        || (hasValue(response.data) && response.data.error_code === 404)
        || (hasValue(response.data.message) && response.data.error_message.indexOf('No such entity with cartId') > -1)
    ) {
      logger.warning('getCart() returned error: @errorCode.', {
        '@errorCode': response.data.error_code,
      });

      clearInvalidCart();

      // If cart is no longer available, no need to return any error.
      return null;
    }

    return {
      data: {
        error: response.data.error,
        error_code: response.data.error_code,
        error_message: getDefaultErrorMessage(),
      },
    };
  }

  // If no error and no response, consider that as 404.
  if (!hasValue(response.data)) {
    clearInvalidCart();
    return null;
  }

  // Format data.
  response.data = formatCart(response.data);

  if (!isUserAuthenticated()) {
    // Set guest cart details in local storage to use with merge guest cart API.
    Drupal.addItemInLocalStorage('guestCartForMerge', {
      active_quote: (typeof response.data.cart !== 'undefined') ? response.data.cart.id : null,
      store_id: (typeof response.data.cart !== 'undefined') ? response.data.cart.store_id : null,
    });
  }

  // Store the formatted data.
  window.commerceBackend.setRawCartDataInStorage(response.data);

  // Return formatted cart.
  return response;
};

/**
 * Adds a customer to cart.
 *
 * @returns {Promise<object/boolean>}
 *   Returns the updated cart or false.
 */
const associateCartToCustomer = async (guestCartId) => {
  // Prepare params.
  const params = { cartId: guestCartId };

  // Associate cart to customer.
  const response = await callMagentoApi(getApiEndpoint('associateCart'), 'POST', params);

  // It's possible that page got reloaded quickly after login.
  // For example on social login.
  if (response.message === 'Request aborted') {
    return;
  }

  if (response.status !== 200) {
    logger.warning('Error while associating cart: @cartId to customer: @customerId. Response: @response.', {
      '@cartId': guestCartId,
      '@customerId': window.drupalSettings.userDetails.customerId,
      '@response': JSON.stringify(response),
    });

    // Clear local storage and let the customer continue without association.
    removeCartIdFromStorage();
    Drupal.alshayaSpc.staticStorage.clear();
    return;
  }

  logger.notice('Guest Cart @guestCartId associated to customer @customerId.', {
    '@customerId': window.drupalSettings.userDetails.customerId,
    '@guestCartId': guestCartId,
  });

  // Clear local storage.
  removeCartIdFromStorage();
  Drupal.alshayaSpc.staticStorage.clear();

  // Reload cart.
  const cartData = await getCart(true);
  if (hasValue(cartData)
    && hasValue(cartData.data)
    && hasValue(cartData.data.cart)
    && hasValue(cartData.data.cart.id)
    && isUserAuthenticated()
  ) {
    // store authenticated user cart id in 'user_cart_id';
    Drupal.addItemInLocalStorage('user_cart_id', cartData.data.cart.id);
  }
};

/**
 * Merge guest cart to customer.
 *
 * @returns {Promise<object/boolean>}
 *   Returns the updated cart or false.
 */
const mergeGuestCartToCustomer = async () => {
  // Get guest cart details.
  const guestCartData = Drupal.getItemFromLocalStorage('guestCartForMerge');

  if (typeof guestCartData === 'undefined' || guestCartData === null) {
    return;
  }

  const endPointParams = {
    customerId: drupalSettings.userDetails.customerId,
    activeQuote: guestCartData.active_quote,
    storeId: guestCartData.store_id,
  };

  // Merge guest cart to customer.
  const response = await callMagentoApi(getApiEndpoint('mergeGuestCart', endPointParams), 'PUT');

  // It's possible that page got reloaded quickly after login.
  // For example on social login.
  if (response.message === 'Request aborted') {
    return;
  }

  if (response.status !== 200) {
    logger.warning('Error while merging guest cart: @cartId to customer: @customerId having store: @storeId. Response: @response.', {
      '@cartId': endPointParams.activeQuote,
      '@customerId': endPointParams.customerId,
      '@storeId': endPointParams.storeId,
      '@response': JSON.stringify(response),
    });

    // Clear local storage and let the customer continue without association.
    removeCartIdFromStorage();
    Drupal.alshayaSpc.staticStorage.clear();

    // Dispatch event with error details on cart merge.
    dispatchCustomEvent('onCartMergeError', response);

    return;
  }

  logger.notice('Guest Cart @guestCartId merged to customer @customerId. having store id: @storeId', {
    '@customerId': window.drupalSettings.userDetails.customerId,
    '@guestCartId': endPointParams.activeQuote,
    '@storeId': endPointParams.storeId,
  });

  // Clear local storage.
  removeCartIdFromStorage();
  Drupal.alshayaSpc.staticStorage.clear();

  // Reload cart.
  await getCart(true);
};

/**
 * Format the cart data to have better structured array.
 * This is the equivalent to CartController:getCart().
 *
 * @param {boolean} force
 *   Force refresh cart data from magento.
 *
 * @returns {Promise<AxiosPromise<object>>}
 *   A promise object.
 */
const getCartWithProcessedData = async (force = false) => {
  // @todo implement missing logic, see CartController:getCart().
  const cart = await getCart(force);
  if (!hasValue(cart) || !hasValue(cart.data)) {
    return null;
  }

  cart.data = await getProcessedCartData(cart.data);

  if (hasValue(cart.data) && !hasValue(cart.data.items)) {
    logger.error('Error updating cart. cartData.items is undefined. cart: @cartData', {
      '@cartData': cart,
    });
  }

  return cart;
};

/**
 * Check if user is authenticated and without cart.
 *
 * @returns bool
 */
const isAuthenticatedUserWithoutCart = async () => {
  const response = await getCart();
  if (!hasValue(response)
    || !hasValue(response.data)
    || !hasValue(response.data.cart)
    || !hasValue(response.data.cart.id)
  ) {
    return true;
  }
  return false;
};

/**
 * Return customer id from current session.
 *
 * @returns {Promise<integer|null>}
 *   Return customer id or null.
 */
const getCartCustomerId = async () => {
  const response = await getCart();
  if (!hasValue(response) || !hasValue(response.data)) {
    return null;
  }

  const cart = response.data;
  if (hasValue(cart) && hasValue(cart.customer) && hasValue(cart.customer.id)) {
    return parseInt(cart.customer.id, 10);
  }
  return null;
};

/**
 * Validate arguments and returns the respective error code.
 *
 * @param {object} request
 *  The request data.
 *
 * @returns {Promise<integer>}
 *   Promise containing the error code.
 */
const validateRequestData = async (request) => {
  // Return error response if not valid data.
  // Setting custom error code for bad response so that
  // we could distinguish this error.
  if (!hasValue(request)) {
    logger.error('Cart update operation not containing any data.');
    return 500;
  }

  // If action info or cart id not available.
  if (!hasValue(request.extension) || !hasValue(request.extension.action)) {
    logger.error('Cart update operation not containing any action. Data: @data.', {
      '@data': JSON.stringify(request),
    });
    return 400;
  }

  // For any cart update operation, cart should be available in session.
  if (window.commerceBackend.getCartId() === null) {
    logger.warning('Trying to do cart update operation while cart is not available in session. Data: @data.', {
      '@data': JSON.stringify(request),
    });
    return 404;
  }

  // If it's a topup operation then return 200 as we are using guest update cart
  // endpoint, So we will not get customer id from cart.
  if (getTopUpQuote() !== null) {
    return 200;
  }
  // Backend validation.
  const cartCustomerId = await getCartCustomerId();
  if (drupalSettings.userDetails.customerId > 0) {
    if (!hasValue(cartCustomerId)) {
      // @todo Check if we should associate cart and proceed.
      // Todo copied from middleware.
      return 400;
    }

    // This is serious.
    if (cartCustomerId !== drupalSettings.userDetails.customerId) {
      logger.error('Mismatch session customer id: @customerId and cart customer id: @cartCustomerId.', {
        '@customerId': drupalSettings.userDetails.customerId,
        '@cartCustomerId': cartCustomerId,
      });
      return 400;
    }
  }

  return 200;
};

/**
 * Runs validations before updating cart.
 *
 * @param {object} request
 *  The request data.
 *
 * @returns {Promise<object|boolean>}
 *   Returns true if the data is valid or an object in case of error.
 */
const preUpdateValidation = async (request) => {
  const validationResponse = await validateRequestData(request);
  if (validationResponse !== 200) {
    return {
      error: true,
      error_code: validationResponse,
      error_message: getDefaultErrorMessage(),
      response_message: {
        status: '',
        msg: getDefaultErrorMessage(),
      },
    };
  }
  return true;
};

/**
 * Calls the update cart API and returns the updated cart.
 *
 * @param {object} postData
 *  The data to send.
 *
 * @returns {Promise<AxiosPromise<object>>}
 *   A promise object with cart data.
 */
const updateCart = async (postData) => {
  // If request is from SocialAuth Popup, restrict further processing.
  // we don't want magento API calls happen on popup, As this is causing issues
  // in processing parent pages.
  if (isRequestFromSocialAuthPopup()) {
    return false;
  }

  const data = { ...postData };
  const cartId = window.commerceBackend.getCartId();

  let action = '';
  data.extension = data.extension || {};
  if (hasValue(data.extension.action)) {
    action = data.extension.action;
  }

  // Add Smart Agent data to extension.
  data.extension = Object.assign(data.extension, getAgentDataForExtension());

  // Validate params before updating the cart.
  const validationResult = await preUpdateValidation(data);
  if (hasValue(validationResult.error) && validationResult.error) {
    return new Promise((resolve, reject) => reject(validationResult));
  }

  logger.debug('Updating Cart. CartId: @cartId, Action: @action, Request: @request.', {
    '@cartId': cartId,
    '@request': JSON.stringify(data),
    '@action': action,
  });

  // As we are using guest cart update in case of Topup, we will not pass
  // bearerToken.
  let useBearerToken = true;
  if ((action === 'update billing'
    || action === 'update payment')
    && getTopUpQuote()) {
    useBearerToken = false;
  }
  return callMagentoApi(getApiEndpoint('updateCart', { cartId }), 'POST', JSON.stringify(data), useBearerToken)
    .then((response) => {
      if (!hasValue(response.data)
        || (hasValue(response.data.error) && response.data.error)) {
        return response;
      }

      // Format data.
      response.data = formatCart(response.data);

      // Update the cart data in storage.
      window.commerceBackend.setRawCartDataInStorage(response.data);

      return response;
    })
    .catch((response) => {
      logger.warning('Error while updating cart on MDC for action: @action. Error message: @message, Code: @errorCode', {
        '@action': action,
        '@message': response.error.message,
        '@errorCode': response.error.error_code,
      });
      // @todo add error handling, see try/catch block in Cart:updateCart().
      return response;
    });
};

window.commerceBackend.pushAgentDetailsInCart = async () => {
  // Do simple refresh cart to make sure we push data before sharing.
  const postData = {
    extension: {
      action: 'refresh',
    },
  };

  return updateCart(postData)
    .then(async (response) => {
      // Process cart data.
      response.data = await getProcessedCartData(response.data);
      return response;
    });
};

/**
 * Return customer email from cart in session.
 *
 * @returns {Promise<string|null>}
 *   Return customer email or null.
 */
const getCartCustomerEmail = async () => {
  let email = Drupal.alshayaSpc.staticStorage.get('cartCustomerEmail');
  if (email !== null) {
    return email;
  }

  const response = await getCart();
  if (!hasValue(response) || !hasValue(response.data)) {
    email = '';
  } else {
    const cart = response.data;
    if (hasValue(cart.customer)
      && hasValue(cart.customer.email)
      && cart.customer.email !== ''
    ) {
      email = cart.customer.email;
    }
  }

  Drupal.alshayaSpc.staticStorage.set('cartCustomerEmail', email);
  return email;
};

/**
 * Checks if cart has OOS item or not by item level attribute.
 *
 * @param {object} cart
 *   Cart data.
 *
 * @return {bool}
 *   TRUE if cart has an OOS item.
 */
const isCartHasOosItem = (cartData) => {
  if (hasValue(cartData.cart.items)) {
    for (let i = 0; i < cartData.cart.items.length; i++) {
      const item = cartData.cart.items[i];
      // If error at item level.
      if (hasValue(item.extension_attributes)
        && hasValue(item.extension_attributes.error_message)
      ) {
        const exceptionType = getExceptionMessageType(item.extension_attributes.error_message);
        if (hasValue(exceptionType) && exceptionType === 'OOS') {
          return true;
        }
      }
    }
  }
  return false;
};

/**
 * Formats the error message as required for cart.
 *
 * @param {int} code
 *   The response code.
 * @param {string} message
 *   The response message.
 */
const getFormattedError = (code, message) => ({
  error: true,
  error_code: code,
  error_message: message,
  response_message: {
    msg: message,
    status: 'error',
  },
});

/**
 * Helper function to prepare the data.
 *
 * @param array $filters
 *   Array containing all filters, must contain field and value, can contain
 *   condition_type too or all that is supported by Magento.
 * @param string $base
 *   Filter Base, mostly searchCriteria.
 * @param int $group_id
 *   Filter group id, mostly 0.
 *
 * @return object
 *   Prepared data.
 */
const prepareFilterData = (filters, base = 'searchCriteria', groupId = 0) => {
  const data = {};

  filters.forEach((filter, index) => {
    Object.keys(filter).forEach((key) => {
      // Prepared string like below.
      // searchCriteria[filter_groups][0][filters][0][field]=field
      // This is how Magento search criteria in APIs work.
      data[`${base}[filter_groups][${groupId}][filters][${index}][${key}]`] = filter[key];
    });
  });

  return data;
};

/**
 * Function to get locations for delivery matrix.
 *
 * @param string $filterField
 *   The field name to filter on.
 * @param string $filterValue
 *   The value of the field to filter on.
 *
 * @return mixed
 *   Response from API.
 */
const getLocations = async (filterField = 'attribute_id', filterValue = 'governate') => {
  const filters = [];
  // Add filter for field values.
  const fieldFilters = {
    field: filterField,
    value: filterValue,
    condition_type: 'eq',
  };

  filters.push(fieldFilters);

  // Always add status check.
  const statusFilters = {
    field: 'status',
    value: '1',
    condition_type: 'eq',
  };
  filters.push(statusFilters);

  // Filter by Country.
  const countryFilters = {
    field: 'country_id',
    value: drupalSettings.country_code,
    condition_type: 'eq',
  };
  filters.push(countryFilters);

  const pageSize = 1000;
  let currentPage = 1;
  let responseData = [];
  let noOfPages = '';
  const preparedFilterData = prepareFilterData(filters);

  // Filter page size.
  preparedFilterData['searchCriteria[page_size]'] = pageSize;

  const url = '/V1/deliverymatrix/address-locations/search';

  do {
    // Filter current page.
    preparedFilterData['searchCriteria[current_page]'] = currentPage;

    try {
      // eslint-disable-next-line no-await-in-loop
      const response = await callMagentoApi(url, 'GET', preparedFilterData);

      if (hasValue(response.data.error) && response.data.error) {
        logger.error('Error in getting locations for delivery matrix. Error: @message', {
          '@message': response.data.error_message,
        });

        return getFormattedError(response.data.error_code, response.data.error_message);
      }

      if (!hasValue(response.data)) {
        const message = 'Got empty response while getting locations for delivery matrix.';
        logger.notice(message);

        return getFormattedError(600, message);
      }

      if (!hasValue(responseData)) {
        responseData = response.data;
      } else {
        // Merging response items from all the pages of the API call.
        responseData.items = [...responseData.items, ...response.data.items];
      }

      noOfPages = hasValue(noOfPages)
        ? noOfPages
        : Math.ceil(response.data.total_count / pageSize);
      currentPage += 1;
    } catch (error) {
      logger.error('Error occurred while fetching governates data. Message: @message.', {
        '@message': error.message,
      });
    }
  } while (currentPage <= noOfPages);

  return responseData;
};

/**
 * Gets governates list items.
 *
 * @returns {Promise<object>}
 *  returns list of governates.
 */
window.commerceBackend.getGovernatesList = async () => {
  if (!drupalSettings.address_fields) {
    logger.error('Error in getting address fields mappings');

    return {};
  }
  if (drupalSettings.address_fields
    && !(drupalSettings.address_fields.area_parent.visible)) {
    return {};
  }
  const mapping = drupalSettings.address_fields;
  // Use the magento field name from mapping.
  const responseData = await getLocations('attribute_id', mapping.area_parent.key);

  if (responseData !== null && responseData.total_count > 0) {
    return responseData;
  }
  logger.warning('No governates found in the list as count is zero, API Response: @response.', {
    '@response': JSON.stringify(responseData),
  });

  return {};
};

/**
 * Gets area List items.
 *
 * @returns {Promise<object>}
 *  returns list of area under a governate if governate id is valid.
 */
window.commerceBackend.getDeliveryAreaList = async (governateId) => {
  let responseData = null;
  if (governateId !== undefined) {
    // Get all area items if governate is none.
    if (governateId !== 'none') {
      responseData = await getLocations('parent_id', governateId);
    } else {
      responseData = await getLocations('attribute_id', 'area');
    }
    if (responseData !== null && responseData.total_count > 0) {
      return responseData;
    }
    logger.warning('No areas found under governate Id : @governateId, API Response: @response.', {
      '@response': JSON.stringify(responseData),
      '@governateId': governateId,
    });
  }
  return {};
};

/**
 * Gets individual area detail.
 *
 * @returns {Promise<object>}
 *  returns details of area if area id is valid.
 */
window.commerceBackend.getDeliveryAreaValue = async (areaId) => {
  if (areaId !== undefined) {
    const responseData = await getLocations('location_id', areaId);
    if (responseData !== null && responseData.total_count > 0) {
      return responseData;
    }
    logger.warning('No details found for area Id : @areaId, API Response: @response.', {
      '@response': JSON.stringify(responseData),
      '@areaId': areaId,
    });
  }
  return {};
};

/**
 * Gets product shipping methods.
 *
 * @returns {Promise<object>}
 *  returns list of governates.
 */
const getProductShippingMethods = async (currentArea, sku = undefined, cartId = null) => {
  let cartIdInt = cartId;
  let cartData = null;
  if (sku === undefined && cartId === null) {
    cartData = window.commerceBackend.getCartDataFromStorage();
    if (cartData.cart.cart_id !== null) {
      cartIdInt = cartData.cart.cart_id_int;
    }
  }

  // Example key: "{}|0984692002|".
  // Example key when area is set:
  // "{\"label\":{\"en\":\"Abbasiya\"},\"value\":{\"area\":6759,\"governate\":6756}}|0984692002|".
  const staticKey = [
    JSON.stringify(currentArea || {}),
    sku || '',
    cartIdInt || '',
  ].join('|');

  // Invoke API only once per page request.
  const staticData = Drupal.alshayaSpc.staticStorage.get(staticKey);
  if (staticData) {
    return staticData;
  }

  const url = '/V1/deliverymatrix/get-applicable-shipping-methods';
  const attributes = [];
  if (currentArea !== null) {
    Object.keys(currentArea.value).forEach((key) => {
      const areaItemsObj = {
        attribute_code: key,
        value: currentArea.value[key],
      };
      attributes.push(areaItemsObj);
    });
  }
  try {
    const params = {
      productAndAddressInformation: {
        cart_id: cartIdInt,
        product_sku: (sku !== undefined) ? sku : null,
        address: {
          custom_attributes: attributes,
        },
      },
    };

    // Associate cart to customer.
    const response = await callMagentoApi(url, 'POST', params);
    if (!hasValue(response.data) || hasValue(response.data.error)) {
      logger.error('Error occurred while fetching governates, Response: @response.', {
        '@response': JSON.stringify(response.data),
      });
      return null;
    }

    // If no city available, return empty.
    if (!hasValue(response.data)) {
      return null;
    }

    Drupal.alshayaSpc.staticStorage.set(staticKey, response.data);
    return response.data;
  } catch (error) {
    logger.error('Error occurred while fetching governates data. Message: @message.', {
      '@message': error.message,
    });
  }
  return {};
};

export {
  isAnonymousUserWithoutCart,
  isAuthenticatedUserWithoutCart,
  associateCartToCustomer,
  preUpdateValidation,
  getCart,
  getCartWithProcessedData,
  updateCart,
  getProcessedCartData,
  checkoutComUpapiVaultMethod,
  checkoutComVaultMethod,
  getFormattedError,
  getCartCustomerEmail,
  getCartCustomerId,
  matchStockQuantity,
  isCartHasOosItem,
  getProductStatus,
  getLocations,
  getProductShippingMethods,
  clearProductStatusStaticCache,
  mergeGuestCartToCustomer,
};
