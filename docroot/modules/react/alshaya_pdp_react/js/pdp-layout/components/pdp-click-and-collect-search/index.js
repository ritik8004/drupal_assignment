import React from 'react';

const PdpClickCollectSearch = ({ inputValue, onChange }) => {
  const changeLabel = (event) => {
    onChange(event.target.value);
  };

  return (
    <div className="location-field-wrapper">
      <div className="location-field">
        <input placeholder="e.g. Salmiya" onChange={changeLabel} value={inputValue} />
      </div>
      <div className="location-field-icon" />
    </div>
  );
};
export default PdpClickCollectSearch;
