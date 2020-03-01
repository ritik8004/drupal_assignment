import React from 'react'
import { fetchClicknCollectStores } from "../../utilities/api/requests";
import _isEqual from 'lodash/isEqual';
import { makeFullName } from '../../utilities/cart_customer_util';

export const ClicknCollectContext = React.createContext();

export class ClicknCollectContextProvider extends React.Component {
  _isMounted = true;

  constructor(props) {
    super(props);
    let coords = null;
    let selectedStore = null;
    let storeList = props.storeList;
    let contactInfo = null;

    let { cart: { customer, store_info, shipping_address } } = props.cart;

    if (!shipping_address && customer !== undefined) {
      contactInfo = {
        fullname: makeFullName(customer.firstname || '', customer.lastname || ''),
        email: customer.email || '',
        telephone: customer.telephone || '',
      }
    }
    else if (shipping_address) {
      contactInfo = {
        fullname: makeFullName(shipping_address.firstname || '', shipping_address.lastname || ''),
        email: shipping_address.email || '',
        telephone: shipping_address.telephone || '',
      }
    }

    if (store_info) {
      coords = {
        lat: parseFloat(store_info.lat),
        lng: parseFloat(store_info.lng)
      };
      selectedStore = store_info;
    }

    this.state = {
      coords: coords,
      storeList: storeList,
      selectedStore: selectedStore,
      contactInfo: contactInfo,
    }
  }

  componentDidMount() {
    this._isMounted = true;
    this.setState({storeList: this.props.storeList});
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
      selectedStore: store
    });
  }

  updateCoordsAndStoreList = (coords, storeList) => {
    this.setState({
      coords: coords,
      storeList: storeList
    });
  }

  updateCoords = (coords) => {
    this.setState({
      coords: coords
    });
  }

  updateContactInfo = (contactInfo) => {
    this.setState({
      contactInfo: contactInfo
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
            updateContactInfo: this.updateContactInfo
          }
        }>
        {this.props.children}
      </ClicknCollectContext.Provider>
    )
  }
}

export default ClicknCollectContextProvider;
