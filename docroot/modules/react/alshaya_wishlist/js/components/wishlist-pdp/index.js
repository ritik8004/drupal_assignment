import React from 'react';
import { getStorageInfo } from '../../../../js/utilities/storage';
import {
  getWishListLocalStorageKey,
  addProductToWishList,
  removeProductFromWishList,
  prepareProductDetailsForWishList,
} from '../../utilities/wishlist-utils';
import { getCurrentProductDetails } from '../../utilities/wishlist-pdp-helper';
import { getUserDetails } from '../../utilities/wishlist-data-helper';

class WishListPDP extends React.Component {
  constructor(props) {
    super(props);
    let addedInWishList = false;

    if (!getUserDetails().id) {
      const localStorageValues = getStorageInfo(getWishListLocalStorageKey());

      if (localStorageValues) {
        const parentProductSku = Object.keys(getCurrentProductDetails())[0];

        if (Object.prototype.hasOwnProperty.call(localStorageValues, parentProductSku)) {
          addedInWishList = true;
        }
      }
    }

    this.state = {
      addedInWishList,
    };
  }

  componentDidMount() {
    document.addEventListener('productAddedToWishlist', this.updateState, false);
    document.addEventListener('productRemovedFromWishlist', this.updateState, false);
  }

  updateState = (data) => {
    const { addedInWishList } = data.detail;
    this.setState({
      addedInWishList,
    });
  };

  getWishListIconClass = () => {
    const { addedInWishList } = this.state;
    const classPrefix = 'wishlist-pdp-icon';

    return addedInWishList ? `${classPrefix}  added` : classPrefix;
  }

  toggleWishlist = () => {
    const { addedInWishList } = this.state;
    const parentProductSku = Object.keys(getCurrentProductDetails())[0];

    if (addedInWishList) {
      removeProductFromWishList(parentProductSku);
    } else {
      addProductToWishList(prepareProductDetailsForWishList(parentProductSku));
    }
  }

  render() {
    return (
      <div
        className={this.getWishListIconClass()}
        onClick={() => this.toggleWishlist()}
      >
        Placeholder for wishlist icon
      </div>
    );
  }
}

export default WishListPDP;
