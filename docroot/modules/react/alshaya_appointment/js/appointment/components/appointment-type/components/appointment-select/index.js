import React from 'react';
import Select from 'react-select';
import 'element-closest-polyfill';
import getStringMessage from '../../../../../../../js/utilities/strings';

export default class AppointmentSelect extends React.Component {
  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
  }

  onMenuOpen = () => {
    this.selectRef.current.select.inputRef.closest('.appointment-select').classList.add('open');
  };

  onMenuClose = () => {
    this.selectRef.current.select.inputRef.closest('.appointment-select').classList.remove('open');
  };

  handleChange = (selectedOption) => {
    const { onSelectChange } = this.props;
    onSelectChange(selectedOption, this.selectRef.current.select.props.name);
  };

  render() {
    const {
      aptSelectClass, name, options, activeItem,
    } = this.props;

    return (
      <Select
        ref={this.selectRef}
        classNamePrefix="appointmentSelect"
        className={`appointment-select fadeInUp ${aptSelectClass}`}
        onMenuOpen={this.onMenuOpen}
        onMenuClose={this.onMenuClose}
        onChange={this.handleChange}
        options={options}
        value={activeItem}
        isSearchable={false}
        name={name}
        placeholder={getStringMessage('selectlist_placeholder')}
      />
    );
  }
}
