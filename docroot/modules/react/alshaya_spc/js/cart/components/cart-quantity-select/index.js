import React from 'react';

import Select from 'react-select';
import { updateCartItemData } from '../../../utilities/update_cart';

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
        this.selectRef.current.select.inputRef.closest('.spc-select').previousSibling.classList.remove('loading');
        // If error.
        if (result.error !== undefined
          && result.error === true) {
          result = {
            error: true,
            message: {
              type: 'error',
              message: result.error_message,
            },
          };
        }
        const miniCartEvent = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => result } });
        document.dispatchEvent(miniCartEvent);

        const refreshCartEvent = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => result } });
        document.dispatchEvent(refreshCartEvent);
      });
    }
  };

  render() {
    const { qty, stock, is_disabled } = this.props;
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
        isDisabled={is_disabled}
      />
    );
  }
}
