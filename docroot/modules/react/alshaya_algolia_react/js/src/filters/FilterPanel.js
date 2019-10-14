import React from 'react';

/**
 * Wrapper component to display eatch filters in a common wrapper.
 */
const FilterPanel = (props) => {
  const propClass = (typeof props.className != 'undefined') ? props.className : '';
  const className = "c-facet c-accordion " + propClass;

  return (
    <div className={className} id={props.id}>
      <h3 className="c-facet__title c-accordion__title">{props.header}</h3>
      {props.children}
    </div>
  );
}

export default FilterPanel;
