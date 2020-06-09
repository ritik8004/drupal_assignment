import React from 'react';
import AvailableSelectOptions from '../available-select-options';
import DefaultSelectOptions from '../default-select-options';

const SwatchSelectOption = (props) => {
  const {
    handleSelectionChanged,
    configurables,
    code,
    nextCode,
    nextValues,
  } = props;

  return (
    <>
      <div className="non-groupped-attr">
        <select id={code} className="select-attribute" onChange={(e) => handleSelectionChanged(e, code)}>
          {Object.keys(configurables.values).map((attr) => {
            if (code === nextCode) {
              return (
                <AvailableSelectOptions
                  nextValues={nextValues}
                  attr={attr}
                  value={configurables.values[attr].swatch_image}
                  key={attr}
                />
              );
            }
            // Show the default options.
            return (
              <DefaultSelectOptions
                attr={attr}
                value={configurables.values[attr].swatch_image}
                key={attr}
              />
            );
          })}
        </select>
      </div>
    </>
  );
};
export default SwatchSelectOption;
