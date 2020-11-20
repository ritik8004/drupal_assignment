import React from 'react';

const FitCalculatorRadio = () => (
  <div className="fit-calculator-measurement-container">
    <div className="fit-calculator-measurement-list fadeInUp">
      <input
        type="radio"
        value="Inches"
        name="fitCalcMeasurement"
        id="fitCalcMeasurement-inches"
      />
      <label htmlFor="fitCalcMeasurement-inches">
        { Drupal.t('Inches') }
      </label>
    </div>
    <div className="fit-calculator-measurement-list fadeInUp">
      <input
        type="radio"
        value="Centimeters"
        name="fitCalcMeasurement"
        id="fitCalcMeasurement-centimeters"
      />
      <label htmlFor="fitCalcMeasurement-centimeters">
        { Drupal.t('Centimeters') }
      </label>
    </div>
  </div>
);

export default FitCalculatorRadio;
