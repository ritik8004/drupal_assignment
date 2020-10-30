import React from 'react';

/**
 * Wrapper component to display eatch filters in a common wrapper.
 */
const FilterPanel = (props) => {
  const {
    id,
    className,
    header,
    children,
  } = props;
  const propClass = (typeof className !== 'undefined') ? className : '';
  const finalClassName = `c-facet c-collapse-item ${propClass}`;

  return (
    <div className={finalClassName} id={id}>
      <h3 className="c-facet__title c-collapse__title">{header}</h3>
      {children}
    </div>
  );
};

export default FilterPanel;
