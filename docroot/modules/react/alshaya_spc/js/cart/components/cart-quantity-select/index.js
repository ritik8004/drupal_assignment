import React from 'react';

import Select from 'react-select';
import { updateCartItemData } from '../../../utilities/update_cart';
import { cartLocalStorageHasSameItems } from '../../../utilities/checkout_util';
import dispatchCustomEvent from '../../../utilities/events';

export default class CartQuantitySelect extends React.Component {
  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
  }

  prepareOptions = (stock) => {
    const cartMaxQty = window.drupalSettings.alshaya_spc.max_cart_qty;
    const data = [];
    for (let i = 1; i <= cartMaxQty; i++) {
      data[i] = {
        value: i,
        label: i,
        isDisabled: (i > stock),
      };
    }

    return data;
  }

  onMenuOpen = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.add('open');
  };

  onMenuClose = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.remove('open');
  };

  handleChange = (selectedOption) => {
    const { sku } = this.props;
    this.selectRef.current.select.inputRef.closest('.spc-select').previousSibling.classList.add('loading');
    const cartData = updateCartItemData('update item', sku, selectedOption.value);
    if (cartData instanceof Promise) {
      cartData.then((result) => {
        let resultVal = result;
        let itemCountSame = true;
        this.selectRef.current.select.inputRef.closest('.spc-select').previousSibling.classList.remove('loading');
        // If error.
        if (resultVal.error !== undefined
          && resultVal.error === true) {
          resultVal = {
            error: true,
            message: {
              type: 'error',
              message: resultVal.error_message,
            },
          };
        } else {
          // If no error, check if item count is same.
          itemCountSame = cartLocalStorageHasSameItems(result);
        }
        const miniCartEvent = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => resultVal } });
        document.dispatchEvent(miniCartEvent);

        const refreshCartEvent = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => resultVal } });
        document.dispatchEvent(refreshCartEvent);

        // If item count not same, show message to user.
        if (itemCountSame === false) {
          // Dispatch event for error to show.
          dispatchCustomEvent('spcCartMessageUpdate', {
            type: 'error',
            message: Drupal.t('Sorry, one or more products in your basket are no longer available and were removed from your basket.'),
          });
        }
      });
    }
  };

  render() {
    const { qty, stock, is_disabled: isDisabled } = this.props;
    const options = this.prepareOptions(stock, qty);
    const qtyClass = stock < qty ? 'invalid' : 'valid';
    return (
      <Select
        ref={this.selectRef}
        classNamePrefix="spcSelect"
        className={`spc-select ${qtyClass}`}
        onMenuOpen={this.onMenuOpen}
        onMenuClose={this.onMenuClose}
        onChange={this.handleChange}
        options={options}
        value={options[qty]}
        defaultValue={options[qty]}
        isSearchable={false}
        isDisabled={isDisabled}
      />
    );
  }
}
