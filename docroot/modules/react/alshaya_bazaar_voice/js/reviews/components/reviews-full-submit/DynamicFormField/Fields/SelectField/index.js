import React from 'react';
import Select from 'react-select';
import 'element-closest-polyfill';

export default class SelectField extends React.Component {
  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
  }

  onMenuOpen = () => {
    this.selectRef.current.select.inputRef.closest('.bv-select').classList.add('open');
  };

  onMenuClose = () => {
    this.selectRef.current.select.inputRef.closest('.bv-select').classList.remove('open');
  };

  afterCartUpdate = () => {
    this.selectRef.current.select.inputRef.closest('.bv-select').previousSibling.classList.remove('loading');
  };

  render() {
    const {
      required,
      id,
      label,
      defaultValue,
      options,
      visible,
    } = this.props;
    const result = Object.keys(options).map((key) => ({ value: key, label: options[key] }));

    return (
      <div key={id}>
        <label htmlFor={label}>{label}</label>
        <Select
          ref={this.selectRef}
          classNamePrefix="bvSelect"
          className="bv-select"
          onMenuOpen={this.onMenuOpen}
          onMenuClose={this.onMenuClose}
          options={result}
          id={id}
          name={id}
          required={required}
          default_value={defaultValue}
          isSearchable={false}
          isDisabled={false}
          hidden={visible}
        />
      </div>
    );
  }
}
