import React from 'react';
import { createPortal } from 'react-dom';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { isProductBuyable } from '../../../../js/utilities/display';
import NotBuyableButton from '../buttons/not-buyable';
import ConfigurableProductDrawer from '../configurable-drawer';
import { addProductInfoInStorage, triggerCartTextNotification } from '../../utilities/addtobag';
import getStringMessage from '../../../../js/utilities/strings';

export default class AddToBagConfigurable extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      drawerStatus: 'closed',
      productInfo: null,
    };

    // Store reference to the main container.
    this.buttonContainerRef = React.createRef();
  }

  /**
   * Click event handler for the Add button.
   */
  handleOnClick = (e) => {
    e.preventDefault();
    e.persist();
    e.stopPropagation();

    const { sku, styleCode } = this.props;

    // Get the container element for placing the loader effect.
    const btn = e.target;

    // Adding the loader class to start spinner.
    btn.classList.toggle('add-to-bag-loader');
    Drupal.cartNotification.spinner_start();

    // Get product's information for drawer.
    const productInfoData = window.commerceBackend.getProductDataAddToBagListing(sku, styleCode);

    if (productInfoData instanceof Promise) {
      productInfoData.then((response) => {
        // Remove the loader class from button container.
        btn.classList.toggle('add-to-bag-loader');

        // If response is null do nothing.
        if (response === null) {
          // Trigger a minicart notification with error.
          triggerCartTextNotification(
            drupalSettings.globalErrorMessage,
            'error',
          );
          return;
        }

        // Show error message if error present.
        if (response.error === true) {
          // Trigger a minicart notification.
          triggerCartTextNotification(response.error_message, 'error');
          return;
        }

        // Open product drawer.
        this.openDrawer(response);
        Drupal.cartNotification.spinner_stop();

        // Push quick add event to GTM.
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'plp quick add clicks',
          eventLabel: 'quick add open',
        });

        // Store info in storage.
        addProductInfoInStorage(response, sku);
      }).catch((error) => {
        Drupal.cartNotification.spinner_stop();
        Drupal.alshayaLogger('error', 'Failed to fetch Product Info for sku @sku. Error @error.', {
          '@sku': (typeof sku !== 'undefined') ? sku : '',
          '@error': typeof error === 'object' ? error.message : error,
        });
      });
    }
  }

  /**
   * Change state to open the product drawer.
   *
   * @param {object} productInfoData
   * An object with product's information.
   */
  openDrawer = (productInfoData) => {
    const nextStatus = 'opened';

    // Trigger Product Details View GTM push.
    const drawerOpenEvent = new CustomEvent('drawerOpenEvent', {
      detail: {
        triggerButtonElement: this.buttonContainerRef.current,
        elementViewMode: this.buttonContainerRef.current.closest('article').getAttribute('gtm-view-mode'),
      },
    });
    document.dispatchEvent(drawerOpenEvent);

    this.setState({
      drawerStatus: nextStatus,
      productInfo: productInfoData,
    });

    // To make sure that markup is present in DOM.
    setTimeout(() => {
      document.querySelector('body').classList.add('overlay-product-modal');
    }, 150);
  };

  /**
   * Callback function for drawer close action.
   */
  onDrawerClose = () => {
    document.querySelector('body').classList.remove('overlay-product-modal');
    setTimeout(() => {
      this.setState({ drawerStatus: 'closed' });
    }, 400);
  }

  render() {
    const { drawerStatus, productInfo } = this.state;
    const {
      sku,
      isBuyable,
      url,
      // 'extraInfo' is used to pass additional information that
      // we want to use in this component.
      extraInfo,
      wishListButtonRef,
    } = this.props;

    // Early return if product is not buyable.
    if (!isProductBuyable(isBuyable)) {
      return (
        <NotBuyableButton url={url} />
      );
    }

    let addToCartText = getStringMessage('view_options');
    // Check if button text is available in extraInfo.
    if (typeof extraInfo.addToCartButtonText !== 'undefined') {
      addToCartText = extraInfo.addToCartButtonText;
    }

    return (
      <>
        <div className="addtobag-config-button-container">
          <button
            className="addtobag-config-button"
            id={`addtobag-button-${sku}`}
            type="button"
            onClick={this.handleOnClick}
            ref={this.buttonContainerRef}
          >
            {addToCartText}
          </button>
        </div>
        <ConditionalView condition={drawerStatus === 'opened'}>
          {createPortal(
            <ConfigurableProductDrawer
              status="opened"
              onDrawerClose={this.onDrawerClose}
              productData={productInfo}
              sku={sku}
              url={url}
              extraInfo={extraInfo}
              wishListButtonRef={wishListButtonRef}
            />,
            document.querySelector('#configurable-drawer'),
          )}
        </ConditionalView>
      </>
    );
  }
}
