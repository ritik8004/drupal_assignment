import React from 'react';

const LoyaltySelectOption = ({
  animationDelay,
  selectedOption,
  optionName,
  changeLoyaltyOption,
  optionText,
}) => (
  <div className={`loyalty-option ${optionName} fadeInUp`} style={{ animationDelay }} onClick={() => changeLoyaltyOption(optionName)}>
    <input id={`loyalty-option-${optionName}`} defaultChecked={selectedOption === optionName} value={optionName} name="loyalty-option" type="radio" />
    <label className="radio-sim radio-label">
      <div className="loaylty-option-text">{optionText}</div>
    </label>
  </div>
);

export default LoyaltySelectOption;
