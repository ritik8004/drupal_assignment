import React from 'react';

const FilterPanel = (props) => {
  const propClass = (typeof props.className != 'undefined') ? props.className : '';
  const className = "c-facet c-accordion " + propClass;

  return (
    <div className={className}>
      <h3 class="c-facet__title c-accordion__title">{props.header}</h3>
      {props.children}
    </div>
  );
}

export default FilterPanel;
