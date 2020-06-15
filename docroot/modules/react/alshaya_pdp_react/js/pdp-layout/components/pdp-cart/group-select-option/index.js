import React from 'react';
import AvailableSelectOptions from '../available-select-options';
import DefaultSelectOptions from '../default-select-options';
import SizeGuide from '../size-guide';

const GroupSelectOption = (props) => {
  const {
    groupSelect,
    handleSelectionChanged,
    configurables,
    groupName,
    code,
    nextCode,
    nextValues,
  } = props;

  return (
    <>
      <div className="group-anchor-wrapper">
        {Object.keys(configurables.alternates).map((alternate) => (
          <a href="#" key={alternate} onClick={(e) => groupSelect(e, configurables.alternates[alternate])}>{configurables.alternates[alternate]}</a>
        ))}
      </div>
      <SizeGuide attrId={code} />
      <div className="group-option-wrapper">
        <select id={code} className="select-attribute-group clicked" onChange={(e) => handleSelectionChanged(e, code)}>
          {Object.keys(configurables.values).map((attr) => {
            // If the currennt attribute matches the
            // attribute code of the available values.
            if (code === nextCode) {
              return (
                <AvailableSelectOptions
                  nextValues={nextValues}
                  attr={attr}
                  value={configurables.values[attr][groupName]}
                  key={attr}
                />
              );
            }
            // Show the default options.
            return (
              <DefaultSelectOptions
                attr={attr}
                value={configurables.values[attr][groupName]}
                key={attr}
              />
            );
          })}
        </select>
      </div>
    </>
  );
};
export default GroupSelectOption;
