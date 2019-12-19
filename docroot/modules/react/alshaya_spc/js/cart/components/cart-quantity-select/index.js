import React from 'react';

import Select from 'react-select';
import {updateCartItemData} from '../../../utilities/update_cart';

export default class CartQuantitySelect extends React.Component {

  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
  }

  prepareOptions = (stock, qty) => {
    const cart_max_qty = window.drupalSettings.alshaya_spc.max_cart_qty;
    var data = new Array();
    for (var i = 1; i <= cart_max_qty; i++) {
      data[i] = {
        value: i,
        label: i,
        isDisabled: (i > stock)
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
    const sku = this.props.sku;
    this.selectRef.current.select.inputRef.closest('.spc-select').previousSibling.classList.add('loading');
    var cart_data = updateCartItemData('update item', sku, selectedOption.value);
    if (cart_data instanceof Promise) {
      cart_data.then((result) => {
        this.selectRef.current.select.inputRef.closest('.spc-select').previousSibling.classList.remove('loading');
        var event = new CustomEvent('refreshMiniCart', {bubbles: true, detail: { data: () => result }});
        document.dispatchEvent(event);

        var event = new CustomEvent('refreshCart', {bubbles: true, detail: { data: () => result }});
        document.dispatchEvent(event);
      });
    }
  };

  render() {
    const {qty, stock, is_disabled} = this.props;
    const options = this.prepareOptions(stock, qty);
    const qty_class = stock < qty ? 'invalid' : 'valid';
    return (
      <Select
        ref={this.selectRef}
        classNamePrefix="spcSelect"
        className={"spc-select " + qty_class}
        onMenuOpen={this.onMenuOpen}
        onMenuClose={this.onMenuClose}
        onChange={this.handleChange}
        options={options}
        value={options[qty]}
        defaultValue={options[qty]}
        isSearchable={false}
        isDisabled={is_disabled}
      />
    )
  }
}
