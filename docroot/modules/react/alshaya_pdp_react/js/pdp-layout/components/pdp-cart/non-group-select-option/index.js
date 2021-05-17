import React from 'react';
import AvailableSelectOptions from '../available-select-options';
import DefaultSelectOptions from '../default-select-options';
import SizeGuide from '../size-guide';

const NonGroupSelectOption = ({
  handleSelectionChanged, configurables, code,
  nextCode, nextValues, handleLiClick, selected, attributeKey, closeModal,
  context,
}) => (
  <div className="magv2-select-popup-container">
    <div className="magv2-select-popup-wrapper">
      <div className="magv2-select-popup-header-wrapper">
        {(context === 'related')
          ? (
            <a className="back" onClick={(e) => closeModal(e)}>
              &times;
            </a>
          )
          : null}
        <a className="close" onClick={(e) => closeModal(e)}>
          &times;
        </a>
        <label htmlFor={attributeKey}>{Drupal.t('Select @title', { '@title': configurables.label })}</label>
      </div>
      <div className="magv2-select-popup-content-wrapper">
        <div className="non-group-anchor-wrapper">
          <label htmlFor={attributeKey}>{Drupal.t('Select @title', { '@title': configurables.label })}</label>
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
                    attr={configurables.values[attr].value_id}
                    value={configurables.values[attr].label}
                    key={attr}
                    selected={selected}
                    handleLiClick={handleLiClick}
                    code={code}
                  />
                );
              }
              // Show the default options.
              return (
                <DefaultSelectOptions
                  attr={configurables.values[attr].value_id}
                  value={configurables.values[attr].label}
                  key={attr}
                  selected={selected}
                  handleLiClick={handleLiClick}
                  code={code}
                />
              );
            })}
          </ul>
        </div>
        <div className="magv2-confirm-size-btn">
          <button className="magv2-button add-to-cart-button" type="submit" value="Confirm Size" onClick={(e) => closeModal(e)}>{Drupal.t('Confirm Size')}</button>
        </div>
      </div>
    </div>
  </div>
);
export default NonGroupSelectOption;
