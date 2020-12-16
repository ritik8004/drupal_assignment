import React from 'react';
import Select from 'react-select';
import 'element-closest-polyfill';

export default class FitCalculatorSelect extends React.Component {
  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
  }

  onMenuOpen = () => {
    this.selectRef.current.select.inputRef.closest('.fit-calculator-select').classList.add('open');
  };

  onMenuClose = () => {
    this.selectRef.current.select.inputRef.closest('.fit-calculator-select').classList.remove('open');
  };

  render() {
    const options = [
      { value: 'inches', label: Drupal.t('Inches') },
      { value: 'centimeters', label: Drupal.t('Centimeters') },
    ];

    return (
      <Select
        ref={this.selectRef}
        classNamePrefix="fitCalcSelect"
        className="fit-calculator-select fadeInUp"
        onMenuOpen={this.onMenuOpen}
        onMenuClose={this.onMenuClose}
        options={options}
        value={options.inches}
        defaultValue={options[0]}
        isSearchable={false}
        name="fitCalcMeasurement"
      />
    );
  }
}
