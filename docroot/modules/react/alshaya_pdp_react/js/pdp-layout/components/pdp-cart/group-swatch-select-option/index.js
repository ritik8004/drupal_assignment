import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import AvailableSwatchOptions from '../available-swatch-options';
import DefaultSwatchOptions from '../default-swatch-options';

const GroupSwatchSelectOption = ({
  configurables, code, handleSelectionChanged, nextCode, nextValues, handleLiClick, selected,
}) => (
  <div className="non-groupped-attr magv2-swatches-wrapper">
    <ul id={code} className="select-attribute magv2-swatch-attribute" onChange={(e) => handleSelectionChanged(e, code)}>
      {Object.keys(configurables.values).map((attr) => {
        if (hasValue(document.getElementById(`color-label-${attr}`))) {
          document.getElementById(`color-label-${attr}`).innerHTML = '';
        }
        // Get label for current color variant.
        const colorLabel = window.commerceBackend.getAttributeValueLabel(code, selected);
        return (
          <div className="group-swatch-option" key={attr}>
            <span className="group-swatch-text">
              {attr}
              <span id={`color-label-${attr}`} />
            </span>
            <div className="group-swatch-items">
              {Object.keys(configurables.values[attr]).map((item) => {
                const attrVal = configurables.values[attr][item].value_id;
                // Set label for current color variant.
                if (typeof colorLabel !== 'undefined'
                && colorLabel === configurables.values[attr][item].label) {
                  document.getElementById(`color-label-${attr}`).innerHTML = `: ${colorLabel}`;
                }
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
        );
      })}
    </ul>
  </div>
);

export default GroupSwatchSelectOption;
