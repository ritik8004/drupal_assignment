import React from 'react';
import parse from 'html-react-parser';
import Popup from 'reactjs-popup';
import { checkoutAddressProcess } from '../../../utilities/checkout_address_process';
import Loading from '../../../utilities/loading';
import ClickCollectContainer from '../click-collect';

class ClicknCollectDeiveryInfo extends React.Component {
  _isMounted = true;

  constructor(props) {
    super(props);
    this.state = {
      open: false,
      showSelectedStore: false,
    };
  }

  openModal = (showSelectedStore) => {
    this.setState({
      open: true,
      showSelectedStore: showSelectedStore || false,
    });
  };

  closeModal = () => {
    this.setState({ open: false });
  };

  processAddress = (e) => {
    const { cart } = this.props.cart;
    checkoutAddressProcess(e, cart);
  };

  componentDidMount() {
    this._isMounted = true;
    document.addEventListener(
      'refreshCartOnCnCSelect',
      this.eventListener,
      false,
    );
  }

  componentWillUnmount() {
    this._isMounted = false;
    document.removeEventListener(
      'refreshCartOnCnCSelect',
      this.eventListener,
      false,
    );
  }

  eventListener = (e) => {
    const data = e.detail.data();
    this.props.refreshCart(data);
    if (this._isMounted) {
      this.closeModal();
    }
  };

  render() {
    const {
      cart: {
        cart: {
          store_info: { name, address },
          shipping_address: shippingAddress,
        },
      },
    } = this.props;

    const { open, showSelectedStore } = this.state;

    return (
      <div className="delivery-information-preview">
        <div className="spc-delivery-store-info">
          <div className="store-name">{name}</div>
          <div className="store-address">
            {parse(address)}
          </div>
          <div
            className="spc-change-address-link"
            onClick={() => this.openModal(false)}
          >
            {Drupal.t('Change')}
          </div>
        </div>
        <div className="spc-delivery-contact-info">
          <div className="contact-info-label">{Drupal.t('Collection by')}</div>
          <div className="contact-name">
            {`${shippingAddress.firstname} ${shippingAddress.lastname}`}
          </div>
          <div className="contact-telephone">{`+${drupalSettings.country_mobile_code} ${shippingAddress.telephone}`}</div>
          <div
            className="spc-change-address-link"
            onClick={() => this.openModal(true)}
          >
            {Drupal.t('Edit')}
          </div>
        </div>
        <Popup
          open={open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
          <React.Suspense fallback={<Loading />}>
            <ClickCollectContainer
              openSelectedStore={showSelectedStore}
              closeModal={this.closeModal}
            />
          </React.Suspense>
        </Popup>
      </div>
    );
  }
}

export default ClicknCollectDeiveryInfo;
