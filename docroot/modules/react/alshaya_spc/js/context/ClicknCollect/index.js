import React from 'react'

export const ClicknCollectContext = React.createContext();

export class ClicknCollectContextProvider extends React.Component {

  constructor(props) {
    super(props);
    let coords = null;
    let selectedStore = null;
    let contactInfo = null;

    let { cart: { customer, store_info, shipping_address } } = props.cart;

    if (!shipping_address && customer !== undefined) {
      contactInfo = {
        firstname: customer.firstname || '',
        lastname: customer.lastname || '',
        email: customer.email || '',
        telephone: customer.telephone || '',
      }
    }
    else if (shipping_address) {
      contactInfo = {
        firstname: shipping_address.firstname || '',
        lastname: shipping_address.lastname || '',
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
      storeList: null,
      selectedStore: selectedStore,
      contactInfo: contactInfo
    }
  }

  updateSelectStore = (store) => {
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
            updateSelectStore: this.updateSelectStore,
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
