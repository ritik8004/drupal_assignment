import React from 'react';
import AvailableSwatchOptions from '../available-swatch-options';
import DefaultSwatchOptions from '../default-swatch-options';

const GroupSwatchSelectOption = ({
  configurables, code, handleSelectionChanged, nextCode, nextValues, handleLiClick,
}) => (
  <div className="non-groupped-attr magv2-swatches-wrapper">
    <ul id={code} className="select-attribute magv2-swatch-attribute" onChange={(e) => handleSelectionChanged(e, code)}>
      {Object.keys(configurables.values).map((attr) => (
        <div className="group-swatch-option" key={attr}>
          <span className="group-swatch-text">{attr}</span>
          <div className="group-swatch-items">
            {Object.keys(configurables.values[attr]).map((item) => {
              const attrVal = configurables.values[attr][item].value_id;

              if (code === nextCode) {
                return (
                  <AvailableSwatchOptions
                    nextValues={nextValues}
                    attr={attrVal}
                    value={configurables.values[attr][item].swatch_color}
                    key={attrVal}
                    handleLiClick={handleLiClick}
                    code={code}
                    label={configurables.values[attr][item].label}
                    swatchType={configurables.values[attr][item].swatch_type}
                  />
                );
              }
              return (
                <DefaultSwatchOptions
                  attr={attrVal}
                  value={configurables.values[attr][item].swatch_color}
                  key={attrVal}
                  handleLiClick={handleLiClick}
                  code={code}
                  label={configurables.values[attr][item].label}
                  swatchType={configurables.values[attr][item].swatch_type}
                />
              );
            })}
          </div>
        </div>
      ))}
    </ul>
  </div>
);

export default GroupSwatchSelectOption;
