import React from 'react';
import parse from 'html-react-parser';
import Popup from 'reactjs-popup';
import { checkoutAddressProcess } from '../../../utilities/checkout_address_process';
import Loading from '../../../utilities/loading';
import ClickCollectContainer from '../click-collect';
import { cleanMobileNumber } from '../../../utilities/checkout_util';

class ClicknCollectDeiveryInfo extends React.Component {
  isComponentMounted = true;

  constructor(props) {
    super(props);
    this.state = {
      open: false,
      showSelectedStore: false,
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener(
      'refreshCartOnCnCSelect',
      this.eventListener,
      false,
    );
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener(
      'refreshCartOnCnCSelect',
      this.eventListener,
      false,
    );
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
    const { cart: { cart: newCart } } = this.props;
    checkoutAddressProcess(e, newCart);
  };

  eventListener = ({ detail }) => {
    const data = detail.data();
    const { refreshCart } = this.props;
    refreshCart(data);
    if (this.isComponentMounted) {
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
          <div className="contact-telephone">{`+${drupalSettings.country_mobile_code} ${cleanMobileNumber(shippingAddress.telephone)}`}</div>
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
