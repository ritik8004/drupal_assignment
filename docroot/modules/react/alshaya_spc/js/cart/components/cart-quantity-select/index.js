import React from 'react';
import Select from 'react-select';
import 'element-closest-polyfill';

export default class CartQuantitySelect extends React.Component {
  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
  }

  prepareOptions = (stock, qty, maxLimit) => {
    let cartMaxQty = parseInt(drupalSettings.alshaya_spc.max_cart_qty, 10);
    const maxLimitValue = maxLimit != null ? parseInt(maxLimit, 10) : null;
    const qtyValue = parseInt(qty, 10);
    const stockValue = parseInt(stock, 10);

    // Maximum number of items in dropdown can not
    // be greater than stock value.
    if (cartMaxQty > stockValue) {
      cartMaxQty = stockValue;
    }

    // Quantity Limit is the highest precendence, we
    // can not allow more than that.
    if (maxLimitValue != null && cartMaxQty > maxLimitValue) {
      cartMaxQty = maxLimitValue;
    }

    // We display dropdown with selected quantity to avoid confusions.
    cartMaxQty = (qtyValue > cartMaxQty) ? qtyValue : cartMaxQty;

    const data = [];
    for (let i = 1; i <= cartMaxQty; i++) {
      data[i] = {
        value: i,
        label: i,
        isDisabled: (i > stockValue),
      };
    }

    return data;
  };

  onMenuOpen = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.add('open');
  };

  onMenuClose = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.remove('open');
  };

  handleChange = (selectedOption) => {
    const { sku, onQtyChange } = this.props;
    this.selectRef.current.select.inputRef.closest('.spc-select').previousSibling.classList.add('loading');
    onQtyChange({
      action: 'update item',
      sku,
      qty: selectedOption.value,
      callback: this.afterCartUpdate,
      successMsg: Drupal.t('Your bag has been updated successfully.'),
    });
  };

  afterCartUpdate = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').previousSibling.classList.remove('loading');
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
