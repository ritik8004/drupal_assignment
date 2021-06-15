import React from 'react';
import { createPortal } from 'react-dom';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { isProductBuyable } from '../../../../js/utilities/display';
import NotBuyableButton from '../buttons/not-buyable';
import ConfigurableProductDrawer from '../configurable-drawer';
import { getProductInfo, addProductInfoInStorage, triggerCartTextNotification } from '../../utilities/addtobag';
import getStringMessage from '../../../../js/utilities/strings';

export default class AddToBagConfigurable extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      drawerStatus: 'closed',
      productInfo: null,
    };
  }

  /**
   * Click event handler for the Add button.
   */
  handleOnClick = (e) => {
    const { sku } = this.props;

    // Get the container element for placing the loader effect.
    const btn = e.target;

    // Adding the loader class to start spinner.
    btn.classList.toggle('add-to-bag-loader');

    // Get product's information for drawer.
    const productInfoData = getProductInfo(sku);
    if (productInfoData instanceof Promise) {
      productInfoData.then((response) => {
        // Remove the loader class from button container.
        btn.classList.toggle('add-to-bag-loader');

        // If response is null do nothing.
        if (response === null) {
          // Trigger a minicart notification with error.
          triggerCartTextNotification(
            drupalSettings.add_to_bag.global_error_message,
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

        // Store info in storage.
        addProductInfoInStorage({ infoData: response }, sku);
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
    const { drawerStatus } = this.state;
    const nextStatus = (drawerStatus === 'opened') ? 'closed' : 'opened';

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
    const { sku, productData, url } = this.props;

    // Early return if product is not buyable.
    if (!isProductBuyable(productData.is_buyable)) {
      return (
        <NotBuyableButton url={url} />
      );
    }

    return (
      <>
        <div className="addtobag-config-button-container">
          <button
            className="addtobag-config-button"
            id={`addtobag-button-${sku}`}
            type="button"
            onClick={this.handleOnClick}
          >
            {`${getStringMessage('view_options')}`}
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
            />,
            document.querySelector('#configurable-drawer'),
          )}
        </ConditionalView>
      </>
    );
  }
}
