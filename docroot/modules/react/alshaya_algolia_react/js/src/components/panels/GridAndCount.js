import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import GridButtons from '../grid-buttons';

export default function GridAndCount(props) {
  const { children } = props;
  const { hideGridToggle } = drupalSettings.algoliaSearch;
  return (
    <div className="block block-alshaya-grid-count-block">
      <div className="total-result-count">
        <div className="view-header search-count tablet">
          {children}
        </div>
      </div>
      <ConditionalView condition={!hideGridToggle}>
        <GridButtons />
      </ConditionalView>
    </div>
  );
}
