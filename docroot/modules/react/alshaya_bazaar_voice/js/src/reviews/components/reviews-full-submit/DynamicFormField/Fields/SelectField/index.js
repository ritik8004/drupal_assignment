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
      addClass,
      text,
    } = this.props;
    let defaultVal = defaultValue;
    const optionsList = [];
    let i = 0;
    Object.entries(options).forEach(([index]) => {
      if (defaultValue === index) {
        defaultVal = { value: index, label: options[index] };
      }
      optionsList[i] = { value: index, label: options[index] };
      i += 1;
    });

    return (
      <>
        <ConditionalView condition={text !== undefined}>
          <div id={`${id}-head-row`} className="head-row">{text}</div>
        </ConditionalView>
        <div id={id} className={`${addClass} dropdown-conatiner`} key={id}>
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
            options={optionsList}
            id={id}
            name={id}
            required={required}
            defaultValue={defaultVal}
            isSearchable={false}
            isDisabled={false}
            onChange={this.handleChange}
            placeholder={getStringMessage('selectlist_placeholder')}
          />
          <div id={`${id}-error`} className={(required) ? 'error' : ''} />
        </div>
      </>
    );
  }
}
