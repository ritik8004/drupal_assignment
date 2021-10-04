import React from 'react';
import SwatchList from './swatch';
import SelectList from './select';
import UnorderedList from './unordered';
import ConditionalView from '../../../../js/utilities/components/conditional-view';

const FormElement = (props) => {
  const {
    type,
    attributeName,
    options,
    label,
    defaultValue,
    activeClass,
    disabledClass,
    onChange,
    isHidden,
    allowedValues,
    groupData,
  } = props;

  let element = null;

  if (options.length === 0) {
    return null;
  }

  switch (type) {
    case 'unordered':
      element = (
        <UnorderedList
          attributeName={attributeName}
          options={options}
          label={label}
          defaultValue={defaultValue}
          activeClass={activeClass}
          disabledClass={disabledClass}
          onClick={onChange}
          isHidden={isHidden}
          allowedValues={allowedValues}
          groupData={groupData}
        />
      );
      break;

    case 'select':
      element = (
        <SelectList
          attributeName={attributeName}
          options={options}
          label={label}
          defaultValue={defaultValue}
          activeClass={activeClass}
          disabledClass={disabledClass}
          onChange={onChange}
          isHidden={isHidden}
          allowedValues={allowedValues}
          groupData={groupData}
        />
      );
      break;

    case 'swatch':
      element = (
        <SwatchList
          attributeName={attributeName}
          swatches={options}
          label={label}
          onClick={onChange}
          defaultValue={defaultValue}
          activeClass={activeClass}
          disabledClass={disabledClass}
          isHidden={isHidden}
          // We want all swatches to be enabled always.
          allowedValues={allowedValues}
        />
      );
      break;

    default:
      break;
  }

  return (
    <ConditionalView condition={element !== null}>
      <div data-configurable-code={attributeName}>{element}</div>
    </ConditionalView>
  );
};

export default FormElement;
