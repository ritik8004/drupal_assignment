import React from 'react';
import Select from 'react-select';
import getStringMessage from '../../../../../../../js/utilities/strings';
import TextField from '../../../../../utilities/textfield';
import { getHelloMemberDictionaryData } from '../../../../../../../alshaya_hello_member/js/src/hello_member_api_helper';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../../js/utilities/showRemoveFullScreenLoader';

class AuraMobileNumberFieldDisplay extends React.Component {
  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
    this.state = {
      userCountryCode: null,
      options: [],
    };
  }

  componentDidMount() {
    showFullScreenLoader();
    // Set the default country code here.
    const {
      setCountryCode,
      countryMobileCode,
      isDisabled,
    } = this.props;

    const processedCountryCodes = [];

    // We check if the field is disabled in which case we are directly passing
    // values via props.
    if (!isDisabled) {
      setCountryCode(countryMobileCode);
    }

    const helloMemberDictionaryData = getHelloMemberDictionaryData({ type:'EXT_PHONE_PREFIX' });
    if (helloMemberDictionaryData instanceof Promise) {
      helloMemberDictionaryData.then((response) => {
        if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)
          && hasValue(response.data.items)) {
          response.data.items.forEach((item) => {
            processedCountryCodes.push({ value: item.code.replace('+', ''), label: item.code });
          });
          this.setState({
            options: processedCountryCodes,
          })
        } else if (hasValue(response.error)) {
          logger.error('Error while trying to get hello member dictionary data. Data: @data.', {
            '@data': JSON.stringify(response),
          });
        }
        removeFullScreenLoader();
      });
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
      options
    } = this.state;

    if (!hasValue(options)) {
      return null;
    }

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

export default AuraMobileNumberFieldDisplay;