import React from 'react';
import AvailableSelectOptions from '../available-select-options';
import DefaultSelectOptions from '../default-select-options';
import SizeGuide from '../size-guide';

const GroupSelectOption = ({
  groupSelect, configurables,
  groupName, code, nextCode, nextValues, handleLiClick,
  selected, keyId, closeModal, context,
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
        <label htmlFor={keyId}>{Drupal.t('Select @title', { '@title': configurables.label })}</label>
      </div>
      <div className="magv2-select-popup-content-wrapper">
        <div className="group-anchor-wrapper">
          <label htmlFor={keyId}>{Drupal.t('Select @title', { '@title': configurables.label })}</label>
          <div className="group-anchor-links">
            {Object.keys(configurables.alternates).map((alternate) => (
              <a
                href="#"
                key={alternate}
                onClick={(e) => groupSelect(e, configurables.alternates[alternate])}
                className={((groupName === configurables.alternates[alternate]))
                  ? 'active' : 'in-active'}
              >
                {configurables.alternates[alternate]}
              </a>
            ))}
          </div>
        </div>
        <SizeGuide attrId={code} />
        <div className="group-option-wrapper">
          <ul id={code} className="select-attribute-group clicked">
            {Object.keys(configurables.values).map((attr) => {
              // If the currennt attribute matches the
              // attribute code of the available values.
              if (code === nextCode) {
                return (
                  <AvailableSelectOptions
                    nextValues={nextValues}
                    attr={attr}
                    value={configurables.values[attr][groupName]}
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
                  value={configurables.values[attr][groupName]}
                  key={attr}
                  selected={selected}
                  code={code}
                  handleLiClick={handleLiClick}
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
export default GroupSelectOption;
