import React from 'react'

export const ClicknCollectContext = React.createContext();

export class ClicknCollectContextProvider extends React.Component {
  state = {
    coords: null,
    storeList: null,
    selectedStore: null
  }

  updateSelectStore = (store) => {
    this.setState({
      selectedStore: store
    });
  }

  updateCoordsAndStoreList = (coords, storeList) => {
    this.setState({
      coords : coords,
      storeList: storeList
    });
  }

  updateCoords = (coords) => {
    this.setState({
      coords: coords
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
            updateCoords: this.updateCoords
          }
        }>
        {this.props.children}
      </ClicknCollectContext.Provider>
    )
  }
}

export default ClicknCollectContextProvider;
