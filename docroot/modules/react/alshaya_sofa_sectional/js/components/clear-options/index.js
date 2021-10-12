import React from 'react';

const ClearOptions = (props) => {
  const {
    handleClearOptions,
    noOfOptions,
  } = props;

  return (
    <div className="clear-options">
      <span>
        { Drupal.t('Select Options 1 to @steps', { '@steps': noOfOptions }) }
      </span>
      <button type="button" onClick={handleClearOptions}>
        { Drupal.t('Clear Options') }
      </button>
    </div>
  );
};

export default ClearOptions;
