import React from 'react';
import ReactDOM from 'react-dom';
import FitCalculator from './fit_calculator/components/fit_calculator_form';

if (document.querySelector('#fit-calculator-container')) {
  ReactDOM.render(
    <FitCalculator />,
    document.querySelector('#fit-calculator-container'),
  );
}
