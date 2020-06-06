import React from 'react';
import AvailableSelectOptions from '../available-select-options';
import DefaultSelectOptions from '../default-select-options';

const NonGroupSelectOption = (props) => {
  const {
    handleSelectionChanged,
    configurables,
    code,
    nextCode,
    nextValues,
  } = props;

  return (
    <>
      <select id={code} className="select-attribute" onChange={(e) => handleSelectionChanged(e, code)}>
        {Object.keys(configurables.values).map((attr) => {
          // If the currennt attribute matches the
          // attribute code of the available values.
          if (code === nextCode) {
            return (
              <AvailableSelectOptions
                nextValues={nextValues}
                attr={attr}
                value={configurables.values[attr].label}
                key={attr}
              />
            );
          }
          // Show the default options.
          return (
            <DefaultSelectOptions
              attr={attr}
              value={configurables.values[attr].label}
              key={attr}
            />
          );
        })}
      </select>
    </>
  );
};
export default NonGroupSelectOption;
