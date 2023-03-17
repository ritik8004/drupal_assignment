import React, { useRef, useEffect } from 'react';
import connectInfiniteHits from '../algolia/connectors/connectInfiniteHits';
import Teaser from '../teaser';
import { removeLoader } from '../../utils';
import ImageElement from '../gallery/imageHelper/ImageElement';
import ConditionalView from '../../../common/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const PlpResultInfiniteHits = connectInfiniteHits(({
  hits, hasMore, refineNext, children = null, gtmContainer, pageType, indexName, subCategories,
}) => {
  // Create ref to get element after it gets rendered.
  const teaserRef = useRef();

  useEffect(
    () => {
      if (typeof teaserRef.current === 'object' && teaserRef.current !== null) {
        if (hits.length > 0) {
          Drupal.algoliaReactPLP.stickyfacetfilter();
          // Trigger gtm event one time, only when search we have search results.
          Drupal.algoliaReactPLP.triggerResultsUpdatedEvent(hits);

          // Trigger back to search page.
          setTimeout(Drupal.processBackToPLP, 10);
        }

        removeLoader();
      }
    }, [hits],
  );

  let groupEnabled = false;
  const results = [];
  const items = [];
  if (hasValue(subCategories)) {
    groupEnabled = true;
    const langcode = 'en';
    Object.keys(subCategories).forEach((key) => {
      const categoryField = subCategories[key].category.category_field;
      const { hierarchy } = subCategories[key].category;

      items[key] = [];
      // Check to match the items with sub categories
      Object.keys(hits).forEach((index) => {
        const level = categoryField.split('.')[1];
        const field = categoryField.split('.')[0];
        // We are fetching EN values only to compare the items correctly with sub-categories.
        if (typeof hits[index][field][langcode] !== 'undefined') {
          const hierarchies = hits[index][field][langcode][level];

          if (hierarchies.includes(hierarchy)) {
            items[key].push(hits[index]);
          }
        }
      });

      if (items[key].length !== 0) {
        // Creating the array with sub category key.
        results[key] = {};
        results[key].image = subCategories[key].image || {};
        results[key].title = subCategories[key].title;
        results[key].desc = subCategories[key].description;
        results[key].hits = [];
        results[key].hits = items[key];
      }
    });

    if (hits.length !== 0) {
      const subcategory = document.querySelectorAll('#block-subcategoryblock .plp-subcategory-block a');

      for (let i = 0; i < subcategory.length; i++) {
        const pid = subcategory[i].querySelector('.sub-category').getAttribute('data-tid');

        if (results[pid] === undefined) {
          subcategory[i].classList.add('visually-hidden');
        } else {
          subcategory[i].classList.remove('visually-hidden');
        }
      }
    }
  }

  return (
    <>
      <ConditionalView condition={!(groupEnabled)}>
        <div className="view-content" ref={teaserRef}>
          <ConditionalView condition={hits.length > 0}>
            {
              hits.map((hit) => (
                <Teaser
                  key={hit.objectID}
                  hit={hit}
                  gtmContainer={gtmContainer}
                  pageType={pageType}
                  indexName={indexName}
                />
              ))
            }
          </ConditionalView>

        </div>

        {children && children({
          results: hits.length,
          hasMore,
          refineNext,
        })}
      </ConditionalView>

      <ConditionalView condition={groupEnabled}>
        <div className="grouping-enabled">
          <div className="view-alshaya-product-list">
            <div className="view-content" ref={teaserRef}>
              {Object.keys(results).map((key) => (
                <div className={`term-header ${subCategories[key].title.replace(' ', '-').toLowerCase()}`} id={subCategories[key].tid}>

                  <ConditionalView condition={results[key].image}>
                    <div className="term-image">
                      <ImageElement
                        src={results[key].image.url}
                        alt={results[key].image.alt}
                        title={results[key].title}
                      />
                    </div>
                  </ConditionalView>

                  <div className="term-title">{results[key].title}</div>
                  <div className="term-desc">{results[key].desc}</div>

                  <ConditionalView condition={results[key].hits.length > 0}>
                    <div>
                      { results[key].hits.map((hit) => (
                        <Teaser
                          key={hit.objectID}
                          hit={hit}
                          gtmContainer={gtmContainer}
                          pageType={pageType}
                          indexName={indexName}
                        />
                      ))}
                    </div>
                  </ConditionalView>
                </div>
              ))}
            </div>

            {children && children({
              results: hits.length,
              hasMore,
              refineNext,
            })}
          </div>
        </div>
      </ConditionalView>
    </>
  );
});

export default PlpResultInfiniteHits;
