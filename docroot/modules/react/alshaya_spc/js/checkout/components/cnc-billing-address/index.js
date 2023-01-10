import React from 'react';

import Popup from 'reactjs-popup';
import Loading from '../../../utilities/loading';
import BillingInfo from '../billing-info';
import SectionTitle from '../../../utilities/section-title';
import {
  processBillingUpdateFromForm,
  getAddressPopupClassName,
} from '../../../utilities/address_util';
import {
  isDeliveryTypeSameAsInCart,
  removeBillingFlagFromStorage,
} from '../../../utilities/checkout_util';
import WithModal from '../with-modal';
import dispatchCustomEvent from '../../../utilities/events';
import { makeFullName } from '../../../utilities/cart_customer_util';
import { cartContainsOnlyVirtualProduct, isFullPaymentDoneByEgift } from '../../../utilities/egift_util';
import { isEgiftCardEnabled } from '../../../../../js/utilities/util';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';
import { isMobile } from '../../../../../js/utilities/display';

const AddressContent = React.lazy(() => import('../address-popup-content'));

// Storage key for billing shipping info same or not.
const localStorageKey = 'billing_shipping_same';

export default class CnCBillingAddress extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);

    // Check and remove flag on load.
    removeBillingFlagFromStorage(props.cart);
  }

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('onBillingAddressUpdate', this.processBillingUpdate);
  }

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = (e) => {
    if (this.isComponentMounted) {
      const data = e.detail;
      const { refreshCart } = this.props;
      // Close modal.
      dispatchCustomEvent('closeModal', 'cncBillingInfo');
      // If there is no error and update was fine, means user
      // has changed the billing address. We set in localstorage.
      if (data.error === undefined) {
        if (data.cart !== undefined) {
          Drupal.addItemInLocalStorage(localStorageKey, false);
        }
      }

      // Refresh cart.
      refreshCart(data);
    }
  };

  /**
   * Process address form submission.
   */
  processAddress = (e) => {
    const { cart } = this.props;
    return processBillingUpdateFromForm(e, cart.cart.shipping.address);
  };

  isActive = () => {
    const { cart } = this.props;
    // Activate the billing address component if full payment is done by egift card.
    // As in case of full payment done by egift card, we disabled other payment methods.
    if (isEgiftCardEnabled() && isFullPaymentDoneByEgift(cart.cart)) {
      return true;
    }

    if (cart.cart.payment.methods === undefined || cart.cart.payment.methods.length === 0) {
      return false;
    }

    return isDeliveryTypeSameAsInCart(cart);
  };

  render() {
    const { cart, refreshCart } = this.props;

    // If carrier info not set, means shipping info not set.
    // So we don't need to show billing.
    if (cart.cart.shipping.method === null
      && !cartContainsOnlyVirtualProduct(cart.cart)) {
      return (null);
    }

    // If billing address city value is 'NONE',
    // means its default billing address (same as shipping)
    // and not added by the user.
    let billingAddressAddedByUser = true;
    if (!cart.cart.billing_address
      || (cart.cart.billing_address && cart.cart.billing_address.city === 'NONE')) {
      billingAddressAddedByUser = false;
    }

    const shippingAddress = cart.cart.shipping.address;
    let editAddressData = {};
    if (shippingAddress) {
      editAddressData = {
        static: {
          fullname: makeFullName(shippingAddress.firstname || '', shippingAddress.lastname || ''),
          telephone: shippingAddress.telephone,
        },
      };
    }

    const activeClass = this.isActive() ? 'active' : 'in-active';

    // If user has not added billing address.
    if (!billingAddressAddedByUser) {
      return (
        <>
          <div className={`spc-section-billing-address cnc-flow ${activeClass}`}>
            <SectionTitle>{Drupal.t('Billing address')}</SectionTitle>
            {isMobile() ? (
              <div className="address-form-mobile-only">
                <React.Suspense fallback={<Loading />}>
                  <AddressContent
                    cart={cart}
                    processAddress={this.processAddress}
                    type="billing"
                    showEmail={
                      !isUserAuthenticated()
                      && cartContainsOnlyVirtualProduct(cart.cart)
                    }
                    default_val={editAddressData}
                    isEmbeddedForm
                  />
                </React.Suspense>
              </div>
            ) : (
              <WithModal modalStatusKey="cncBillingInfo">
                {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
                  <div className="spc-billing-address-wrapper">
                    <div className="spc-billing-top-panel spc-billing-cc-panel" onClick={() => triggerOpenModal()}>
                      {Drupal.t('please add your billing address.')}
                    </div>
                    <Popup
                      className={`spc-billing-address-form-no-saved-address ${getAddressPopupClassName()}`}
                      open={isModalOpen}
                      closeOnEscape={false}
                      closeOnDocumentClick={false}
                    >
                      <React.Suspense fallback={<Loading />}>
                        <AddressContent
                          closeModal={triggerCloseModal}
                          cart={cart}
                          processAddress={this.processAddress}
                          // Show email id field in case of egift card is enabled,
                          // cart contains only virtual products and anonymous user.
                          showEmail={
                            !isUserAuthenticated()
                            && cartContainsOnlyVirtualProduct(cart.cart)
                          }
                          showEditButton={false}
                          type="billing"
                          formContext="billing"
                          headingText={Drupal.t('billing information')}
                          default_val={editAddressData}
                          isEmbeddedForm={false}
                        />
                      </React.Suspense>
                    </Popup>
                  </div>
                )}
              </WithModal>
            )}
          </div>
        </>
      );
    }

    return (
      <div className={`spc-section-billing-address cnc-flow appear ${activeClass}`} style={{ animationDelay: '0.2s' }}>
        <SectionTitle>{Drupal.t('Billing address')}</SectionTitle>
        <div className="spc-billing-address-wrapper">
          <div className="spc-billing-bottom-panel">
            <BillingInfo cart={cart} refreshCart={refreshCart} />
          </div>
        </div>
      </div>
    );
  }
}
