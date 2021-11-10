import React from 'react';
import Collapsible from 'react-collapsible';
import Swatch from '../swatches';
import ConditionalView from '../../../../js/utilities/components/conditional-view';

/**
 * SwatchList component.
 *
 * @param {object} props
 *   The props object.
 */
const SwatchList = (props) => {
  const {
    attributeName,
    swatches,
    onClick,
    label,
    defaultValue,
    activeClass,
    disabledClass,
    isHidden,
    allowedValues,
    index,
  } = props;
  let selectedSwatchLabel;
  let selectedSwatchImage;
  let classes = 'form-swatch-list-wrapper form-list-wrapper';
  classes = isHidden ? `${classes} form-element-hidden` : `${classes}`;
  /* disable eslint to disable eqeqeq rule for Swatch. */
  /* eslint-disable */
  const swatchItems = swatches.map((swatch) => {
    if (defaultValue == swatch.value) {
      selectedSwatchLabel = swatch.label;
      selectedSwatchImage = swatch.data;
    }
    return (
      <Swatch
        attributeName={attributeName}
        data={swatch.data}
        type={swatch.type}
        value={swatch.value}
        label={swatch.label}
        key={swatch.value}
        onClick={onClick}
        isSelected={defaultValue == swatch.value}
        activeClass={activeClass}
        disabledClass={disabledClass}
        allowedValues={allowedValues}
      />
    );
  });

  const SofaSectionConfigSwatchAccordion = (
    <label className={selectedSwatchLabel ? 'active' : ''}>
      <div className="config-number-wrapper">
        <span className="config-index-number">
          {index}
        </span>
      </div>

      <ConditionalView condition={selectedSwatchImage !== null && selectedSwatchImage !== undefined}>
        <span className='selected-image'>
          <img loading="lazy" src={selectedSwatchImage} />
        </span>
      </ConditionalView>

      <div className="config-text-wrapper">
        <span className="config-name">
          {Drupal.t('select')}
          {' '}
          {label}
        </span>

        <ConditionalView condition={attributeName === 'fabric_color'}>
          <span className="fabric-color-icon">
          </span>
        </ConditionalView>

        <ConditionalView condition={selectedSwatchLabel !== null && selectedSwatchLabel !== undefined}>
          <span className="config-value selected-text">
            {selectedSwatchLabel}
          </span>
        </ConditionalView>

      </div>
    </label>
  );

  /* eslint-disable */
  return (
    <div className={classes}>
      <Collapsible trigger={SofaSectionConfigSwatchAccordion} open="true">
        <div className="attribute-options-list">
          <ul className={`swatch-list ${attributeName}`} name={attributeName}>
            {swatchItems}
          </ul>
        </div>
      </Collapsible>
    </div>
  );
};

export default SwatchList;
