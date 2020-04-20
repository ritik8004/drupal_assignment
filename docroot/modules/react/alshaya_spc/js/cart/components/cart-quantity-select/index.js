import React from 'react';

import Select from 'react-select';
import { updateCartItemData } from '../../../utilities/update_cart';
import dispatchCustomEvent from '../../../utilities/events';

export default class CartQuantitySelect extends React.Component {
  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
  }

  prepareOptions = (stock, qty, maxLimit) => {
    let cartMaxQty = drupalSettings.alshaya_spc.max_cart_qty;
    if (maxLimit != null) {
      cartMaxQty = (maxLimit < stock) ? maxLimit : stock;
    }
    // We display dropdown with selected quantity to avoid confusions.
    cartMaxQty = (qty > cartMaxQty) ? qty : cartMaxQty;

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
        const resultVal = result;
        this.selectRef.current.select.inputRef.closest('.spc-select').previousSibling.classList.remove('loading');
        let messageInfo = null;
        // If error.
        if (resultVal.error !== undefined
          && resultVal.error === true) {
          messageInfo = {
            type: 'error',
            message: resultVal.error_message,
          };
        } else {
          messageInfo = {
            type: 'success',
            message: Drupal.t('Your bag has been updated successfully.'),
          };
        }
        const miniCartEvent = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => resultVal } });
        document.dispatchEvent(miniCartEvent);

        const refreshCartEvent = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => resultVal } });
        document.dispatchEvent(refreshCartEvent);

        // Trigger message.
        if (messageInfo !== null) {
          dispatchCustomEvent('spcCartMessageUpdate', messageInfo);
        }
      });
    }
  };

  render() {
    const {
      qty, stock, is_disabled: isDisabled, maxLimit,
    } = this.props;
    const options = this.prepareOptions(stock, qty, maxLimit);
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
