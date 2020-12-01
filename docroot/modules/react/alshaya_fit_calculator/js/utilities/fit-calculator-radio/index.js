import React from 'react';

const handleSelect = (el) => {
  const elements = document.getElementsByName('fitCalcMeasurement');
  for (let i = 0; i < elements.length; i++) {
    elements[i].classList.remove('selected');
  }
  el.target.classList.add('selected');
};

const FitCalculatorRadio = () => (
  <div className="fit-calculator-measurement-container">
    <div className="fit-calculator-measurement-list fadeInUp">
      <input
        type="radio"
        value="inches"
        name="fitCalcMeasurement"
        id="fitCalcMeasurement-inches"
        checked
        onClick={handleSelect}
      />
      <label htmlFor="fitCalcMeasurement-inches">
        { Drupal.t('Inches') }
      </label>
    </div>
    <div className="fit-calculator-measurement-list fadeInUp">
      <input
        type="radio"
        value="centimeters"
        name="fitCalcMeasurement"
        id="fitCalcMeasurement-centimeters"
        onClick={handleSelect}
      />
      <label htmlFor="fitCalcMeasurement-centimeters">
        { Drupal.t('Centimeters') }
      </label>
    </div>
  </div>
);

export default FitCalculatorRadio;
