import React from 'react';
import {
  isProductExistInWishList,
  addProductToWishList,
  removeProductFromWishList,
} from '../../utilities/wishlist-utils';

class WishlistButton extends React.Component {
  constructor(props) {
    super(props);
    // Set the products status in state.
    // true: if sku exist in wishlist,
    // false: default, if sku doesn't exist in wishlist.
    this.state = {
      addedInWishList: false,
      mainSku: props.sku,
      sku: props.sku,
      title: props.title,
      configurationOptions: null,
    };
  }

  componentDidMount = () => {
    const { sku } = this.state;
    const { context } = this.props;
    if (context === 'newpdp') {
      this.setConfigurableOptions();
    }

    // @todo: we need to listen wishlist load event that
    // will trigger from header wishlist component after
    // wishlist data are fetched from MDC on page load
    // for logged in user.
    // Check if product already exist in wishlist, and
    // set the status for the sku.
    if (isProductExistInWishList(sku)) {
      this.updateWishListStatus(true);
    }

    // Rendering wishlist button as per sku variant info.
    document.addEventListener('onSkuVariantSelect', this.updateProductInfoData, false);

    // Set configurable options on change of variant.
    document.addEventListener('onConfigurationOptionsLoad', this.setConfigurableOptions, false);
  };

  setConfigurableOptions = (e) => {
    const options = [];
    let selectedAttributes = {};
    if (e && e.detail.data) {
      selectedAttributes = e.detail.data;
    } else {
      const { mainSku } = this.state;
      const { configurableCombinations } = this.props;
      selectedAttributes = configurableCombinations[mainSku].configurables;
    }
    if (Object.keys(selectedAttributes).length > 0) {
      const { context } = this.props;
      Object.keys(selectedAttributes).forEach((key) => {
        const option = {
          option_id: context === 'newpdp' ? selectedAttributes[key].code : key,
          option_value: context === 'newpdp'
            ? document.querySelector(`#pdp-add-to-cart-form-main #${key}`).querySelectorAll('.active')[0].value : e.detail.data[key],
        };
        options.push(option);
      });
    }
    this.setState({
      configurationOptions: options,
    });
  }

  /**
   * To product info state as per variant selection.
   */
  updateProductInfoData = (e) => {
    const { context } = this.props;
    if (context === 'newpdp') {
      this.setConfigurableOptions();
    }
    if (e.detail && e.detail.data !== '') {
      const { sku } = this.state;
      const variantInfo = e.detail.data;
      this.setState({
        sku: variantInfo.parent_sku ? variantInfo.parent_sku : variantInfo.sku,
        title: variantInfo.title,
      }, () => {
        this.updateWishListStatus(isProductExistInWishList(sku));
      });
    }
  }

  /**
   * This will update the addedInWishList state of product.
   *
   * @param {bool} status
   *  Contains the status or product in wishlist.
   */
  updateWishListStatus = (status) => {
    const { addedInWishList } = this.state;
    if (addedInWishList !== status) {
      this.setState({
        addedInWishList: status,
      });
    }
  }

  /**
   * Add or remove product from the wishlist.
   */
  toggleWishlist = () => {
    const {
      addedInWishList, sku, title, configurationOptions,
    } = this.state;
    // If product already in wishlist remove this else add.
    if (addedInWishList) {
      removeProductFromWishList(sku, this.updateWishListStatus);
    } else {
      const productInfo = {
        sku,
        title,
        configurationOptions,
      };
      addProductToWishList(productInfo, this.updateWishListStatus);
    }
  }

  render() {
    const { addedInWishList } = this.state;
    const { context, position } = this.props;
    const classPrefix = `wishlist-icon ${context} ${position}`;
    const wishListButtonClass = addedInWishList ? `${classPrefix} in-wishlist` : classPrefix;

    return (
      <div
        className={wishListButtonClass}
        onClick={() => this.toggleWishlist()}
      >
        {/* @todo: Display wishlist icon here. */}
        {Drupal.t('Add to wishlist')}
      </div>
    );
  }
}

export default WishlistButton;
