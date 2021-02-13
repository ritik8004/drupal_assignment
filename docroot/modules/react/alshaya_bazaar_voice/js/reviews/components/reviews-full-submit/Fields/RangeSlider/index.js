import React, { useState } from 'react';

const RangeSlider = ({
  field,
}) => {
  const [activeId, setActiveId] = useState();
  const options = field['#options'];
  const sliderTitle = field['#title'];
  const result = Object.keys(options).map((key) => ({ value: key, label: options[key] }));

  return (
    <div className="review-field-container">
      <div className="selectedValue">
        <span>
          {sliderTitle}
          {':'}
        </span>
        <span>{activeId}</span>
      </div>
      <div className="range-slider">
        {result.map((val) => (
          <React.Fragment key={val.value}>
            <input id={val.label} type="radio" name="letter" />
            <label
              className={activeId === val.label ? 'active' : 'inactive'}
              onClick={() => setActiveId(val.label)}
              htmlFor={val.label}
            >
              <span>{val.label}</span>
            </label>
          </React.Fragment>
        ))}
      </div>
    </div>
  );
};

export default RangeSlider;
