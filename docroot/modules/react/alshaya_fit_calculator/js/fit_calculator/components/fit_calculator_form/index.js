import React from 'react';
import FitCalculatorTitle from '../../../utilities/fit-calculator-title';
import TextField from '../../../utilities/textfield';
import FitCalculatorSelect from '../../../utilities/fit-calculator-select';
import FitCalculatorRadio from '../../../utilities/fit-calculator-radio';

export default class FitCalculator extends React.Component {
  constructor() {
    super();
    this.state = {};
  }

  render() {
    return (
      <div className="fit-calculator-wrapper">
        <FitCalculatorTitle>
          {Drupal.t('Alshaya fit calculator')}
        </FitCalculatorTitle>
        <div className="fit-calculator-form-wrapper">
          <div className="fit-calculator-radio-wrapper">
            <label>{Drupal.t('Show measurements in:')}</label>
            <FitCalculatorRadio />
          </div>
          <div className="fit-calculator-select-wrapper">
            <label>{Drupal.t('Show measurements in:')}</label>
            <FitCalculatorSelect />
          </div>
          <TextField
            name="band size"
            label="band size"
            focusClass="band-size-input"
          />
          <TextField
            name="bust size"
            label="bust size"
            focusClass="bust-size-input"
          />
          <button
            className="fit-calculator-button"
            id="fit-calculator-button"
            type="button"
          >
            {Drupal.t('get my size')}
          </button>
          <div className="size-conversion-link">
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
        </div>
      </div>
    );
  }
}
