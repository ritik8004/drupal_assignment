import React from 'react';
import { makeFullName } from '../../utilities/cart_customer_util';
import { cleanMobileNumber } from '../../utilities/checkout_util';

export const ClicknCollectContext = React.createContext();

class ClicknCollectContextProvider extends React.Component {
  constructor(props) {
    super(props);
    let coords = null;
    let selectedStore = null;
    let cartSelectedStore = null;
    let contactInfo = null;

    const {
      cart: {
        customer,
        shipping: { storeInfo, address: shippingAddress },
        cart_id: cartId,
      },
    } = props.cart;

    if (!shippingAddress && customer !== undefined) {
      contactInfo = {
        fullname: makeFullName(customer.firstname || '', customer.lastname || ''),
        email: customer.email || '',
        telephone: cleanMobileNumber(customer.telephone) || '',
      };
    } else if (shippingAddress) {
      contactInfo = {
        fullname: makeFullName(shippingAddress.firstname || '', shippingAddress.lastname || ''),
        email: shippingAddress.email || '',
        telephone: cleanMobileNumber(shippingAddress.telephone) || '',
      };
    }

    if (storeInfo) {
      coords = {
        lat: parseFloat(storeInfo.lat),
        lng: parseFloat(storeInfo.lng),
      };
      selectedStore = storeInfo;
      cartSelectedStore = storeInfo;
    }

    this.state = {
      coords,
      storeList: [],
      selectedStore,
      contactInfo,
      cartSelectedStore,
      clickCollectModal: false,
      locationAccess: true,
      outsideCountryError: false,
      cartId,
    };
  }

  static getDerivedStateFromProps(props, state) {
    if (props.storeList !== state.storeList) {
      return {
        storeList: state.storeList === null ? props.storeList : state.storeList,
      };
    }
    // Return null to indicate no change to state.
    return null;
  }

  updateSelectedStore = (store) => {
    this.setState({
      selectedStore: store,
    });
  }

  updateCoordsAndStoreList = (coords, storeList, accessStatus = null) => {
    this.setState((prevState) => ({
      ...prevState,
      coords,
      storeList,
      locationAccess: (accessStatus !== null) ? accessStatus : prevState.locationAccess,
    }));
  }

  updateCoords = (coords) => {
    this.setState({
      coords,
    });
  }

  updateContactInfo = (contactInfo) => {
    this.setState({
      contactInfo: {
        fullname: makeFullName(contactInfo.firstname || '', contactInfo.lastname || ''),
        email: contactInfo.email || '',
        telephone: cleanMobileNumber(contactInfo.telephone) || '',
      },
    });
  }

  updateModal = (status) => {
    this.setState({ clickCollectModal: status });
  }

  updateLocationAccess = (accessStatus) => {
    this.setState((prevState) => ({
      ...prevState,
      locationAccess: accessStatus,
    }));
  };

  showOutsideCountryError = (status) => {
    this.setState((prevState) => ({
      ...prevState,
      outsideCountryError: status,
    }));
  }

  render() {
    const { children } = this.props;

    return (
      <ClicknCollectContext.Provider
        value={
          {
            ...this.state,
            updateSelectStore: this.updateSelectedStore,
            updateCoordsAndStoreList: this.updateCoordsAndStoreList,
            updateCoords: this.updateCoords,
            updateContactInfo: this.updateContactInfo,
            updateModal: this.updateModal,
            updateLocationAccess: this.updateLocationAccess,
            showOutsideCountryError: this.showOutsideCountryError,
          }
        }
      >
        {children}
      </ClicknCollectContext.Provider>
    );
  }
}

export default ClicknCollectContextProvider;
