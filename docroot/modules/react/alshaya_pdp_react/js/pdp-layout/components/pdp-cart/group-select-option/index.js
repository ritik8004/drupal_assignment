import React from 'react';
import AvailableSelectOptions from '../available-select-options';
import DefaultSelectOptions from '../default-select-options';
import SizeGuide from '../size-guide';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

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
        <SizeGuide attrId={code} context="pdp" />
        <div className="group-option-wrapper">
          <ul id={code} className="select-attribute-group clicked">
            {configurables.values && Object.keys(configurables.values).map((key) => {
              let attr = key;
              let value = configurables.values[key][groupName];
              if (hasValue(configurables.values[key])) {
                // Check if the values object is multidimensional.
                // use the first key to get respected value.
                // This might be possible for size attribute.
                const [attrKey] = Object.keys(configurables.values[key]);
                attr = attrKey;
                value = configurables.values[key][attr][groupName];
              }
              // If the currennt attribute matches the
              // attribute code of the available values.
              if (code === nextCode) {
                return (
                  <AvailableSelectOptions
                    nextValues={nextValues}
                    attr={attr}
                    value={value}
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
                  value={value}
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
