import React from 'react';
import { makeFullName } from '../../utilities/cart_customer_util';
import { cleanMobileNumber } from '../../utilities/checkout_util';

export const ClicknCollectContext = React.createContext();

class ClicknCollectContextProvider extends React.Component {
  constructor(props) {
    super(props);
    let coords = null;
    let selectedStore = null;
    const { storeList } = props;
    let contactInfo = null;

    const {
      cart: {
        customer,
        store_info: storeInfo,
        shipping_address: shippingAddress,
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
    }

    this.state = {
      coords,
      storeList,
      selectedStore,
      contactInfo,
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

  componentDidMount() {
    const { storeList } = this.props;
    this.setState({ storeList });
  }

  updateSelectedStore = (store) => {
    this.setState({
      selectedStore: store,
    });
  }

  updateCoordsAndStoreList = (coords, storeList) => {
    this.setState({
      coords,
      storeList,
    });
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
          }
        }
      >
        {children}
      </ClicknCollectContext.Provider>
    );
  }
}

export default ClicknCollectContextProvider;
