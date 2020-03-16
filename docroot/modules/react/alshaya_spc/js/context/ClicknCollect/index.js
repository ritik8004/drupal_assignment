import React from 'react';
import { makeFullName } from '../../utilities/cart_customer_util';
import { cleanMobileNumber } from '../../utilities/checkout_util';

export const ClicknCollectContext = React.createContext();

export class ClicknCollectContextProvider extends React.Component {
  _isMounted = true;

  constructor(props) {
    super(props);
    let coords = null;
    let selectedStore = null;
    const { storeList } = props;
    let contactInfo = null;

    const { cart: { customer, store_info, shipping_address } } = props.cart;

    if (!shipping_address && customer !== undefined) {
      contactInfo = {
        fullname: makeFullName(customer.firstname || '', customer.lastname || ''),
        email: customer.email || '',
        telephone: cleanMobileNumber(customer.telephone) || '',
      };
    } else if (shipping_address) {
      contactInfo = {
        fullname: makeFullName(shipping_address.firstname || '', shipping_address.lastname || ''),
        email: shipping_address.email || '',
        telephone: cleanMobileNumber(shipping_address.telephone) || '',
      };
    }

    if (store_info) {
      coords = {
        lat: parseFloat(store_info.lat),
        lng: parseFloat(store_info.lng),
      };
      selectedStore = store_info;
    }

    this.state = {
      coords,
      storeList,
      selectedStore,
      contactInfo,
    };
  }

  componentDidMount() {
    this._isMounted = true;
    this.setState({ storeList: this.props.storeList });
  }

  componentWillUnmount() {
    this._isMounted = false;
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
        {this.props.children}
      </ClicknCollectContext.Provider>
    );
  }
}

export default ClicknCollectContextProvider;
