import React from 'react';
import Select from 'react-select';
import 'element-closest-polyfill';
import ConditionalView from '../../../../../../common/components/conditional-view';
import getStringMessage from '../../../../../../../../../js/utilities/strings';

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

  handleChange = (selectedOption) => {
    const { id } = this.props;
    if (selectedOption.value.length > 0) {
      document.getElementById(`${id}-error`).innerHTML = '';
    }
  };

  render() {
    const {
      required,
      id,
      label,
      defaultValue,
      options,
      visible,
      text,
    } = this.props;
    const result = Object.keys(options).map((key) => ({ value: key, label: options[key] }));

    return (
      <>
        <ConditionalView condition={text !== undefined}>
          <div className="head-row">{text}</div>
        </ConditionalView>
        <div className="dropdown-conatiner" key={id}>
          <label className="dropdown-label" htmlFor={label}>
            {label}
            {' '}
            {(required) ? '*' : '' }
          </label>
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
            onChange={this.handleChange}
            placeholder={getStringMessage('selectlist_placeholder')}
          />
          <div id={`${id}-error`} className="error" />
        </div>
      </>
    );
  }
}
