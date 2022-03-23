import React from 'react';
import { connectHierarchicalMenu } from 'react-instantsearch-dom';

const PLPHierarchicalMenu = ({
  items, refine, createURL, facetLevel, plpCategoryRef,
}) => (
  <ul ref={plpCategoryRef}>
    {items.map((item) => (
      <li
        key={item.label}
        className={
          `item
          ${item.isRefined ? 'item--selected' : ''}
          ${item.noRefinement ? 'item--selected' : ''}
          ${item.items && Boolean(item.items.length) ? 'item--parent' : ''}`
        }
      >
        <a
          href={`#${createURL(item.value)}`}
          data-level={facetLevel}
          className={`facet-item ${item.isRefined ? 'is-active ' : ''}`}
          datadrupalfacetlabel={item.label}
          onClick={(event) => {
            event.preventDefault();
            refine(item.value);
          }}
        >
          <span className="facet-item__value">
            {item.label}
            <span className="facet-item__count">{`(${item.count})`}</span>
          </span>
        </a>
      </li>
    ))}
  </ul>
);

const PLPHierarchicalMenuHoc = connectHierarchicalMenu(PLPHierarchicalMenu);

export default React.forwardRef((props, ref) => (
  <PLPHierarchicalMenuHoc {...props} plpCategoryRef={ref} />
));
