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
      field, fieldChanged,
    } = this.props;
    const Options = field['#options'];
    const result = Object.keys(Options).map((key) => ({ value: key, label: Options[key] }));
    return (
      <div key={field['#id']}>
        <label htmlFor={field['#id']}>{field['#title']}</label>
        <Select
          ref={this.selectRef}
          classNamePrefix="bvSelect"
          className="bv-select"
          onMenuOpen={this.onMenuOpen}
          onMenuClose={this.onMenuClose}
          options={result}
          id={field['#id']}
          name={field['#id']}
          required={field['#required']}
          default_value={field['#default_value']}
          isSearchable={false}
          isDisabled={false}
          hidden={field['#hidden']}
          onChange={(e) => {
            fieldChanged(field['#id'], e.value);
          }}
        />
      </div>
    );
  }
}
