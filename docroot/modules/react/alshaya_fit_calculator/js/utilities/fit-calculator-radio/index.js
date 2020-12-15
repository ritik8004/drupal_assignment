import React, { useState } from 'react';

const FitCalculatorRadio = () => {
  const [selectedOption, setSelectedOption] = useState('fitCalcMeasurement-inches');

  const handleSelect = (method) => {
    setSelectedOption(method);
  };

  return (
    <div className="fit-calculator-measurement-container">
      <div className="fit-calculator-measurement-list fadeInUp">
        <input
          type="radio"
          value="inches"
          name="fitCalcMeasurement"
          id="fitCalcMeasurement-inches"
          checked={selectedOption === 'fitCalcMeasurement-inches'}
          onChange={() => handleSelect('fitCalcMeasurement-inches')}
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
          checked={selectedOption === 'fitCalcMeasurement-centimeters'}
          onChange={() => handleSelect('fitCalcMeasurement-centimeters')}
        />
        <label htmlFor="fitCalcMeasurement-centimeters">
          { Drupal.t('Centimeters') }
        </label>
      </div>
    </div>
  );
};

export default FitCalculatorRadio;
