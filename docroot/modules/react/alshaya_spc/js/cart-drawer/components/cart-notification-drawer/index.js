import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import isCartNotificationDrawerEnabled from '../../../utilities/cart_notification_util';
import CartDrawerContent from '../cart-drawer-content';
import CartDrawerPanel from '../cart-drawer-panel';

class CartNotificationDrawer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      productAddedToBasket: false,
      productData: null,
      panelContent: null,
    };
  }

  componentDidMount() {
    if (isCartNotificationDrawerEnabled()) {
      document.addEventListener('showCartDrawer', this.handleProductAddToCart);
    }
  }

  /**
   * This event listener function called when item added to cart.
   *
   * @param {object} event
   *  Event detail containing product data.
   */
  handleProductAddToCart = (event) => {
    if (event.detail) {
      // to make sure that markup is present in DOM.
      document.querySelector('body').classList.add('overlay-cart-drawer');
      this.setState({
        productAddedToBasket: true,
        productData: event.detail,
        panelContent: this.getPanelContent(event.detail),
      });
    }
  }

  getPanelContent = (productData) => {
    if (hasValue(productData) && hasValue(productData.productInfo)) {
      return (
        <CartDrawerContent
          productData={productData.productInfo}
          closeModal={() => this.closeModal()}
        />
      );
    }
    return null;
  }

  closeModal = () => {
    document.querySelector('body').classList.remove('overlay-cart-drawer');
    this.setState({
      panelContent: null,
    });
  }

  render() {
    const { productAddedToBasket, productData, panelContent } = this.state;

    if (!productAddedToBasket && !hasValue(productData) && !hasValue(panelContent)) {
      return null;
    }

    return (
      <div className="cart-notification-drawer">
        <div className="cart-drawer-wrapper">
          <CartDrawerPanel
            panelContent={panelContent}
          />
        </div>
      </div>
    );
  }
}

export default CartNotificationDrawer;
