import React from 'react';
import ReactDOM from 'react-dom';

export default class FitCalculator extends React.Component {
  constructor() {
    super();
    this.state = {};
  }

  render() {
    return (
      <div>Placeholder...</div>
    );
  }
}

ReactDOM.render(
  <FitCalculator />,
  document.querySelector('#fit-calculator-container'),
);
