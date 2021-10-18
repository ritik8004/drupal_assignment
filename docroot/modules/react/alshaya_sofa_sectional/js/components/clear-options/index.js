import React from 'react';

const ClearOptions = (props) => {
  const {
    handleClearOptions,
    noOfOptions,
  } = props;

  return (
    <div className="sofa-section-select-option-wrapper">
      <div className="sofa-section-select-option">
        {Drupal.t('Select options 1 to @length', { '@length': noOfOptions })}
      </div>
      <div className="sofa-section-clear-option-btn-wrapper">
        <button
          className="sofa-section-clear-option-btn"
          type="button"
          onClick={handleClearOptions}
        >
          {Drupal.t('Clear Options')}
        </button>
      </div>
    </div>
  );
};

export default ClearOptions;
