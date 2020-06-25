import React from 'react';
import AvailableSelectOptions from '../available-select-options';
import DefaultSelectOptions from '../default-select-options';

const SwatchSelectOption = (props) => {
  const {
    configurables,
    code,
    nextCode,
    nextValues,
    handleLiClick,
    selected,
  } = props;

  return (
    <>
      <div className="non-groupped-attr">
        <ul id={code} className="select-attribute">
          {Object.keys(configurables.values).map((attr) => {
            if (code === nextCode) {
              return (
                <AvailableSelectOptions
                  nextValues={nextValues}
                  attr={attr}
                  value={configurables.values[attr].swatch_image}
                  key={attr}
                  selected={selected}
                  code={code}
                  handleLiClick={handleLiClick}
                />
              );
            }
            // Show the default options.
            return (
              <DefaultSelectOptions
                attr={attr}
                value={configurables.values[attr].swatch_image}
                key={attr}
                selected={selected}
                code={code}
                handleLiClick={handleLiClick}
              />
            );
          })}
        </ul>
      </div>
    </>
  );
};
export default SwatchSelectOption;
