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
        const swatchValue = configurables.values[attr].swatch_image
          || configurables.values[attr].swatch_color;
        const swatchType = configurables.values[attr].swatch_type || '';

        if (code === nextCode) {
          return (
            <AvailableSwatchOptions
              nextValues={nextValues}
              attr={attrVal}
              value={swatchValue}
              key={attrVal}
              handleLiClick={handleLiClick}
              code={code}
              label={configurables.values[attr].label}
              swatchType={swatchType}
            />
          );
        }
        return (
          <DefaultSwatchOptions
            attr={attrVal}
            value={swatchValue}
            key={attrVal}
            handleLiClick={handleLiClick}
            code={code}
            label={configurables.values[attr].label}
            swatchType={swatchType}
          />
        );
      })}
    </ul>
  </div>
);

export default SwatchSelectOption;
