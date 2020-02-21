import React from 'react'

export const ClicknCollectContext = React.createContext();

export class ClicknCollectContextProvider extends React.Component {

  constructor(props) {
    super(props);
    // console.log(props);
    let coords = null;
    let selectedStore = null;
    let contactInfo = null;
    if (props.cart.delivery_type === 'cnc') {
      let { cart: { store_info, shipping_address: { firstname, lastname, email, telephone } } } = props.cart;
      if (store_info) {
        coords = {
          lat: parseFloat(store_info.lat),
          lng: parseFloat(store_info.lng)
        };
        selectedStore = store_info;
        contactInfo = {
          firstname,
          lastname,
          email,
          telephone,
        }
      }
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
