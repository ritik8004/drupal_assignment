import React from 'react';

/**
 * Wrapper component to display eatch filters in a common wrapper.
 */
const FilterPanel = (props) => {
  const {
    id,
    dataId,
    className,
    header,
    children,
  } = props;
  const propClass = (typeof className !== 'undefined') ? className : '';
  const finalClassName = `c-facet c-collapse-item ${propClass}`;

  return (
    // Remove language suffix for _product_list index.
    // ID to be attr_size instead of attr_size.en.
    <div className={finalClassName} id={id.split('.')[0]} data-id={dataId.split('.')[0]}>
      <h3 className="c-facet__title c-collapse__title">{header}</h3>
      {children}
    </div>
  );
};

export default FilterPanel;
