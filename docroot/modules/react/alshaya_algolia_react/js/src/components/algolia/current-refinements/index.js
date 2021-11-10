import React from 'react';
import { connectCurrentRefinements } from 'react-instantsearch-dom';
import _uniqBy from 'lodash/uniqBy';
import FiltersLabels from './FiltersLabels';
import ClearRefinements from '../ClearFilters';

export default connectCurrentRefinements(({
  items, refine, pageType = null, ...props
}) => {
  const uniqueItems = (pageType === 'plp')
    ? _uniqBy(items, 'attribute').filter((value) => value.attribute.indexOf('lhn_category') < 0)
    : _uniqBy(items, 'attribute');
  props.callback(uniqueItems.length);

  return (
    <ul>
      {
        uniqueItems.map((item) => (
          <React.Fragment key={item.id}>
            {item.items ? (
              <>
                {
                      item.items.map((nested) => (
                        <li key={nested.label}>
                          <a
                            href="#"
                            onClick={(event) => {
                              event.preventDefault();
                              refine(nested.value);
                            }}
                          >
                            <FiltersLabels
                              value={nested.label}
                              attribute={item.attribute}
                              pageType={pageType}
                            />
                          </a>
                        </li>
                      ))
                    }
              </>
            ) : (
              <li>
                <a
                  href="#"
                  onClick={(event) => {
                    event.preventDefault();
                    refine(item.value);
                  }}
                >
                  <FiltersLabels
                    value={item.label}
                    attribute={item.attribute}
                    pageType={pageType}
                  />
                </a>
              </li>
            )}
          </React.Fragment>
        ))
      }
      {(items.length > 0) ? (
        <li className="clear-all"><ClearRefinements pageType={pageType} title={Drupal.t('clear filters')} /></li>
      ) : null}
    </ul>
  );
});
