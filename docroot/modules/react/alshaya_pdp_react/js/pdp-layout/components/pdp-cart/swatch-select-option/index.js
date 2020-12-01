import React from 'react';
import AvailableSwatchOptions from '../available-swatch-options';
import DefaultSwatchOptions from '../default-swatch-options';

const SwatchSelectOption = ({
  configurables, code, handleSelectionChanged, nextCode, nextValues, handleLiClick,
}) => (
  <div className="non-groupped-attr magv2-swatches-wrapper">
    <ul id={code} className="select-attribute magv2-swatch-attribute" onChange={(e) => handleSelectionChanged(e, code)}>
      {Object.keys(configurables.values).map((attr) => {
        const attrVal = configurables.values[attr].value_id;
        if (code === nextCode) {
          return (
            <AvailableSwatchOptions
              nextValues={nextValues}
              attr={attrVal}
              value={configurables.values[attr].swatch_image}
              key={attrVal}
              handleLiClick={handleLiClick}
              code={code}
              label={configurables.values[attr].label}
            />
          );
        }
        return (
          <DefaultSwatchOptions
            attr={attrVal}
            value={configurables.values[attr].swatch_image}
            key={attrVal}
            handleLiClick={handleLiClick}
            code={code}
            label={configurables.values[attr].label}
          />
        );
      })}
    </ul>
  </div>
);

export default SwatchSelectOption;
