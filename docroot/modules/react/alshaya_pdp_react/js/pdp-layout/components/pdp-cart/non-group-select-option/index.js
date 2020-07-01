import React from 'react';
import AvailableSelectOptions from '../available-select-options';
import DefaultSelectOptions from '../default-select-options';
import SizeGuide from '../size-guide';

const NonGroupSelectOption = ({
  handleSelectionChanged, configurables, code,
  nextCode, nextValues, handleLiClick, selected,
  key, closeModal,
}) => (
  <div className="magv2-select-popup-container">
    <div className="magv2-select-popup-wrapper">
      <div className="magv2-select-popup-header-wrapper">
        <a className="close" onClick={() => closeModal()}>
          &times;
        </a>
        <label htmlFor={key}>{configurables.label}</label>
      </div>
      <div className="magv2-select-popup-content-wrapper">
        <div className="non-group-anchor-wrapper">
          <label htmlFor={key}>{configurables.label}</label>
        </div>
        <SizeGuide attrId={code} />
        <div className="non-group-option-wrapper">
          <ul id={code} className="select-attribute" onChange={(e) => handleSelectionChanged(e, code)}>
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
                    selected={selected}
                    handleLiClick={handleLiClick}
                  />
                );
              }
              // Show the default options.
              return (
                <DefaultSelectOptions
                  attr={attr}
                  value={configurables.values[attr].label}
                  key={attr}
                  selected={selected}
                  handleLiClick={handleLiClick}
                />
              );
            })}
          </ul>
        </div>
      </div>
    </div>
  </div>
);
export default NonGroupSelectOption;
