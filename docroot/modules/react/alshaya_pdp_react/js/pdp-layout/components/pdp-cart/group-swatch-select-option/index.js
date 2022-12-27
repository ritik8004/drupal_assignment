import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import AvailableSwatchOptions from '../available-swatch-options';

const GroupSwatchSelectOption = ({
  configurables, code, handleSelectionChanged, nextCode, nextValues, handleLiClick, selected,
}) => (
  <div className="non-groupped-attr magv2-swatches-wrapper">
    <ul id={code} className="select-attribute magv2-swatch-attribute" onChange={(e) => handleSelectionChanged(e, code)}>
      {Object.keys(configurables.values).filter((attr) => {
        if (attr === 'undefined') {
          return false;
        }
        return true;
      }).map((attr) => {
        const colorLabelId = document.getElementById(`color-label-${attr}`);
        // Get label for current color variant.
        const colorLabel = window.commerceBackend.getAttributeValueLabel(code, selected);
        if (hasValue(colorLabelId)) {
          colorLabelId.innerHTML = '';
        }
        const processedNextValues = (code === nextCode)
          ? nextValues
          : null;
        return (
          <div className="group-swatch-option" key={attr}>
            <span className="group-swatch-text">
              {attr}
              <span id={`color-label-${attr}`} />
            </span>
            <div className="group-swatch-items">
              {Object.keys(configurables.values[attr]).filter((item) => {
                if (!hasValue(configurables.values[attr][item].color_group)) {
                  return false;
                }
                return true;
              }).map((item) => {
                const attrVal = configurables.values[attr][item].value_id;
                // Set label for current color variant.
                if (hasValue(colorLabel) && hasValue(colorLabelId)
                && colorLabel === configurables.values[attr][item].label) {
                  colorLabelId.innerHTML = `: ${colorLabel}`;
                }
                return (
                  <AvailableSwatchOptions
                    nextValues={processedNextValues}
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
