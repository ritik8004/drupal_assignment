import React from 'react';
import SelectAreaPopupContent from '../utilities/select-area-popup-content';

const SelectAreaPanel = (props) => {
  const {
    panelContent,
  } = props;

  return (
    <div className="select-area-popup-panel">
      <SelectAreaPopupContent>
        {panelContent}
      </SelectAreaPopupContent>
    </div>
  );
};

export default SelectAreaPanel;
