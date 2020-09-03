import React from 'react';
import AvailableSwatchOptions from '../available-swatch-options';
import DefaultSwatchOptions from '../default-swatch-options';

const SwatchSelectOption = ({
  configurables, code, handleSelectionChanged, nextCode, nextValues, handleLiClick,
}) => (
  <div className="non-groupped-attr">
    <ul id={code} className="select-attribute" onChange={(e) => handleSelectionChanged(e, code)}>
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
          />
        );
      })}
    </ul>
  </div>
);

export default SwatchSelectOption;
