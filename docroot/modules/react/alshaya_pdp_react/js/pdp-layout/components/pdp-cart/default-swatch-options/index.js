import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

const DefaultSwatchOptions = (props) => {
  const {
    attr,
    value,
    handleLiClick,
    code,
    label,
    swatchType,
  } = props;

  const values = hasValue(value)
    ? value.split('|')
    : [];
  if (swatchType === 'RGB' && values.length > 1) {
    return (
      <li key={attr} id={`value${attr}`} className="in-active" value={attr} data-attribute-label={label}>
        <a href="#" onClick={(e) => handleLiClick(e, code)}>
          <div style={{
            backgroundColor: values[0],
            width: '50%',
            borderTopLeftRadius: '50px',
            borderBottomLeftRadius: '50px',
          }}
          />
          <div style={{
            backgroundColor: values[1],
            width: '50%',
            borderTopRightRadius: '50px',
            borderBottomRightRadius: '50px',
          }}
          />
        </a>
      </li>
    );
  }

  return (
    <li key={attr} id={`value${attr}`} className="in-active" value={attr} data-attribute-label={label}>
      <a href="#" style={(swatchType === 'RGB' ? { backgroundColor: value } : { backgroundImage: `url(${value})` })} onClick={(e) => handleLiClick(e, code)} />
    </li>
  );
};

export default DefaultSwatchOptions;
