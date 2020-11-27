import React from 'react';
import Select from 'react-select';
import TextField from '../../../../utilities/textfield';
import getStringMessage from '../../../../utilities/strings';
import { getProcessedMobileCountryCode } from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';

class AuraMobileNumberField extends React.Component {
  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
    this.state = {
      userCountryCode: null,
    };
  }

  componentDidMount() {
    // Set the default country code here.
    const {
      setCountryCode,
      countryMobileCode,
      isDisabled,
    } = this.props;

    // We check if the field is disabled in which case we are directly passing
    // values via props.
    if (!isDisabled) {
      setCountryCode(countryMobileCode);
    }
  }

  onMenuOpen = () => {
    this.selectRef.current.select.inputRef.closest('.spc-aura-mobile-number').classList.add('open');
  };

  onMenuClose = () => {
    this.selectRef.current.select.inputRef.closest('.spc-aura-mobile-number').classList.remove('open');
  };

  handleChange = (selectedOption) => {
    const { setCountryCode } = this.props;
    // Set the user selected country code.
    this.setState({
      userCountryCode: selectedOption.value,
    });

    setCountryCode(selectedOption.value);
  };

  getDefaultValueKey = (options, countryMobileCode) => {
    let index = null;
    options.forEach((value, key) => {
      if (countryMobileCode.toString() === value.value) {
        index = key;
      }
    });

    return index;
  };

  render() {
    const {
      isDisabled,
      name,
      maxLength,
      countryMobileCode,
      defaultValue,
      onlyMobileFieldPlaceholder,
    } = this.props;

    const {
      userCountryCode,
    } = this.state;

    const options = getProcessedMobileCountryCode();
    // Get default value from config or from what user has chosen.
    const key = userCountryCode === null
      ? this.getDefaultValueKey(options, countryMobileCode)
      : this.getDefaultValueKey(options, userCountryCode);

    return (
      <div className={`spc-aura-mobile-number ${name}-aura-mobile-field`}>
        <div className="field-wrapper">
          <div className="spc-aura-mobile-number-country-code">
            <label>{Drupal.t('Country Code')}</label>
            <Select
              ref={this.selectRef}
              classNamePrefix="spcAuraSelect"
              className={`spc-aura-select ${name}-country-code`}
              onMenuOpen={this.onMenuOpen}
              onMenuClose={this.onMenuClose}
              options={options}
              defaultValue={options[key]}
              isSearchable={false}
              isDisabled={isDisabled}
              onChange={this.handleChange}
            />
          </div>
          <TextField
            type="text"
            required
            maxLength={maxLength}
            disabled={isDisabled}
            name={`${name}-mobile-number`}
            defaultValue={defaultValue}
            label={getStringMessage('mobile_label')}
            placeholder={onlyMobileFieldPlaceholder}
          />
        </div>
        <div id={`${name}-aura-mobile-field-error`} className="error" />
      </div>
    );
  }
}

export default AuraMobileNumberField;
