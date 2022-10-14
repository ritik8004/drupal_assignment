import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CartNotificationDrawerContent from '../cart-notification-drawer-content';
import CartNotificationDrawerPopupContent from '../utilities/cart-notification-drawer-popup-content';

class CartNotificationDrawer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      cartDrawerOpen: true,
    };
  }

  componentDidUpdate(prevProps) {
    const { cartDrawerOpen } = this.props;
    if (prevProps.cartDrawerOpen !== cartDrawerOpen) {
      this.refreshState();
    }
  }

  refreshState = () => {
    this.setState({
      cartDrawerOpen: true,
    });
  }

  getPanelContent = () => {
    const { productData } = this.props;
    if (hasValue(productData) && hasValue(productData.productInfo)) {
      return (
        <CartNotificationDrawerContent
          productData={productData.productInfo}
          closeModal={() => this.closeModal()}
          overlayClass="overlay-cart"
        />
      );
    }
    return null;
  }

  closeModal = () => {
    document.querySelector('body').classList.remove('overlay-cart-drawer');
    this.setState({
      cartDrawerOpen: false,
    });
  }

  render() {
    const { cartDrawerOpen } = this.state;
    if (!cartDrawerOpen) {
      return null;
    }

    const panelContent = this.getPanelContent();
    if (!hasValue(panelContent)) {
      return null;
    }

    return (
      <div className="cart-notification-drawer">
        <CartNotificationDrawerPopupContent>
          {panelContent}
        </CartNotificationDrawerPopupContent>
      </div>
    );
  }
}

export default CartNotificationDrawer;
