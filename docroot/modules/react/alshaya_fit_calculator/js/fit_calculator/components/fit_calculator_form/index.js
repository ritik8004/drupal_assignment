import React from 'react';
import FitCalculatorTitle from '../../../utilities/fit-calculator-title';
import TextField from '../../../utilities/textfield';
import FitCalculatorSelect from '../../../utilities/fit-calculator-select';
import FitCalculatorRadio from '../../../utilities/fit-calculator-radio';
import ConditionalView from '../../../common/components/conditional-view';

export default class FitCalculator extends React.Component {
  constructor() {
    super();
    this.state = {
      bandSizeLabel: Drupal.t('band size'),
      burstSizeLabel: Drupal.t('burst size'),
      errorMessage: false,
      resultSize: false,
    };
  }

  handleSubmit = (e) => {
    e.preventDefault();
    // Get form field elements.
    const unit = e.target.elements.fitCalcMeasurement.value;
    const bandSize = e.target.elements.band_size;
    const burstSize = e.target.elements.burst_size;

    // If empty fields then set placeholder and class.
    if (this.checkFieldEmpty(bandSize, burstSize) === false) {
      return false;
    }

    const bandSizeValue = Math.round(bandSize.value);
    const burstSizeValue = Math.round(burstSize.value);

    // Checks if input is valid.
    if (this.checkIfValidInput(bandSizeValue, burstSizeValue) === false) {
      return false;
    }

    // Check if the sizeData json is parsable from config settings data.
    let sizeData = [];
    try {
      sizeData = JSON.parse(drupalSettings.fitCalculator.sizeData);
    } catch (error) {
      this.setState({
        errorMessage: true,
        resultSize: false,
      });
      throw (error);
    }

    // Check if size exists.
    if (this.checkIfSizeExists(sizeData, unit, bandSizeValue, burstSizeValue) === false) {
      return false;
    }

    // Show result and link to PLP page if size is available.
    this.setState({
      resultSize: sizeData[unit][bandSizeValue][burstSizeValue],
      errorMessage: false,
    });
    return true;
  };

  /**
   * Checks if field are empty.
   */
  checkFieldEmpty = (bandSize, burstSize) => {
    if (bandSize.value === '') {
      this.setState({
        bandSizeLabel: Drupal.t('Please enter band size'),
      });
      bandSize.focus();
      bandSize.classList.add('empty');
      return false;
    }
    if (burstSize.value === '') {
      this.setState({
        burstSizeLabel: Drupal.t('Please enter burst size'),
      });
      burstSize.focus();
      burstSize.classList.add('empty');
      return false;
    }
    return true;
  };

  /**
   * Checks if input are valid.
   */
  checkIfValidInput = (bandSizeValue, burstSizeValue) => {
    if (Number.isNaN(bandSizeValue) || Number.isNaN(burstSizeValue)) {
      this.setState({
        errorMessage: true,
        resultSize: false,
      });
      return false;
    }
    return true;
  };

  /**
   * Checks if size data available.
   */
  checkIfSizeExists = (sizeData, unit, bandSizeValue, burstSizeValue) => {
    if (sizeData[unit][bandSizeValue] === undefined) {
      this.setState({
        errorMessage: true,
        resultSize: false,
      });
      return false;
    } if (sizeData[unit][bandSizeValue][burstSizeValue] === undefined) {
      this.setState({
        errorMessage: true,
        resultSize: false,
      });
      return false;
    }
    return true;
  }

  render() {
    const {
      errorMessage,
      bandSizeLabel,
      burstSizeLabel,
      resultSize,
    } = this.state;

    const { measurementField, plpPage } = drupalSettings.fitCalculator;

    return (
      <>
        <div className={`fit-calculator-wrapper ${measurementField}`}>
          <form
            className="fit-calculator-form fadeInUp"
            style={{ animationDelay: '0.4s' }}
            onSubmit={(e) => this.handleSubmit(e)}
          >
            <FitCalculatorTitle>
              {Drupal.t('The Perfect Fit Calculator')}
            </FitCalculatorTitle>
            <div className="fit-calculator-form-wrapper">
              <div className="fit-calculator-unit-wrapper">
                <label>{Drupal.t('Show measurements in:')}</label>
                <ConditionalView condition={(window.innerWidth < 767) && (measurementField === 'main-form')}>
                  <FitCalculatorRadio />
                </ConditionalView>
                <ConditionalView condition={(window.innerWidth > 767) && (measurementField === 'main-form')}>
                  <FitCalculatorSelect />
                </ConditionalView>
                <ConditionalView condition={measurementField === 'size-guide-calculator'}>
                  <FitCalculatorRadio />
                </ConditionalView>
              </div>
              <div className="fit-calculator-input-wrapper">
                <TextField
                  name="band_size"
                  label={bandSizeLabel}
                  focusClass="band-size-input"
                />
                <TextField
                  name="burst_size"
                  label={burstSizeLabel}
                  focusClass="bust-size-input"
                />
                <button
                  className="fit-calculator-button"
                  id="fit-calculator-button"
                  type="submit"
                >
                  {Drupal.t('get my size')}
                </button>
              </div>
            </div>
            <div className="message-area">
              { errorMessage
                && (
                <div className="error-message">
                  { Drupal.t('Sorry, we don\u2019t carry your size yet but we are always working to expand our collections.') }
                </div>
                )}
              { resultSize
                && (
                <div className="success-message">
                  <span>{Drupal.t('Your size is ')}</span>
                  <span className="result">{resultSize}</span>
                  <a
                    href={`${plpPage}--size-${resultSize.toLowerCase()}`}
                  >
                    {Drupal.t('shop your size')}
                  </a>
                </div>
                )}
            </div>
            <div className="fit-calculator-size-conversion-chart">
              <a
                href={drupalSettings.fitCalculator.sizeConversionChartUrl}
                className="size-guide-link use-ajax"
                data-dialog-type="dialog"
                data-dialog-options="{&quot;height&quot;:400,&quot;width&quot;:700}"
                rel="nofollow"
              >
                {Drupal.t('Size Conversion Chart')}
              </a>
            </div>
          </form>
        </div>
      </>
    );
  }
}
