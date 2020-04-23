import React from 'react';
import Select from 'react-select';

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
    const { sku, onQtyChange } = this.props;
    this.selectRef.current.select.inputRef.closest('.spc-select').previousSibling.classList.add('loading');
    onQtyChange('update item', sku, selectedOption.value, this.afterCartUpdate);
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
