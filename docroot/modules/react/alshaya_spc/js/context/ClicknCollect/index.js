import React from 'react'

export const ClicknCollectContext = React.createContext();

export class ClicknCollectContextProvider extends React.Component {
  state = {
    coords: null,
    store_list: null,
    selected: null
  }

  updateSelectStore = (store) => {
    this.setState({
      selected: store
    });
  }

  updateCoordsAndStoreList = (coords, storeList) => {
    this.setState({
      coords : coords,
      store_list: storeList
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
