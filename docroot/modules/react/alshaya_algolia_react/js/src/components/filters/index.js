import React, { useState, useRef } from 'react';
import WidgetManager from '../widget-manager';
import DynamicWidgets from '../algolia/widgets/DynamicWidgets';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isConfigurableFiltersEnabled } from '../../../../../js/utilities/helper';
import { getFilters } from '../../utils';

const Filters = ({ indexName, pageType, ...props }) => {
  const [filterCounts, setfilters] = useState([]);
  const [facets, setFacets] = useState([]);
  const ref = useRef();

  // Loop through all the filters given in config and prepare an array of filters.
  const updateFilterResult = (itm) => {
    filterCounts[itm.attr] = itm.count;
    setfilters(filterCounts);
    if (typeof ref.current === 'object' && ref.current !== null) {
      const filters = ref.current.querySelectorAll('.c-collapse-item');
      const activeFilters = [];
      filters.forEach((element) => {
        const ulElement = element.getElementsByTagName('ul');
        const childrenLi = ulElement[0] ? ulElement[0].querySelector('li') : null;
        if (ulElement.length === 0 || childrenLi === null) {
          element.classList.add('hide-facet-block');
        } else {
          activeFilters.push(element);
          element.classList.remove('hide-facet-block');
        }
      });

      props.callback({ activeFilters, filterCounts, ...props });
    }
  };

  /**
   * Check overrides and update default context data for facet config.
   *
   * @param data
   *   userData with context from algolia result.
   * @param facetsConfig
   *   Facet config from algolia result.
   *
   * @returns {*}
   *   Updated userData with overrides.
   */
  const overrideFilterConfig = (data, facetsConfig) => {
    const userData = data;
    Object.entries(facetsConfig).forEach(([key, value]) => {
      if (hasValue(userData.facets_config[key])) {
        if (hasValue(value.label)) {
          userData.facets_config[key].label = value.label;
        }

        if (hasValue(value.widget)) {
          userData.facets_config[key].widget = value.widget;
        }

        if (hasValue(value.slug)) {
          userData.facets_config[key].slug = value.slug;
        }

        if (hasValue(value.facets_value)) {
          userData.facets_config[key].slug = value.facets_value;
        }
      }
    });
    return userData;
  };

  /**
   * Check overrides and update default context data for sort config.
   *
   * @param data
   *   userData with context from algolia result.
   * @param sortdata
   *   Sort config from algolia result.
   * @returns {*}
   *   Updated userData with overrides.
   */
  const overrideSortByConfig = (data, sortdata) => {
    const userData = data;
    const { sorting_label: Sortlabel } = sortdata;
    if (hasValue(Sortlabel)) {
      Object.entries(Sortlabel).forEach(([key, value]) => {
        if (hasValue(userData.sorting_label[key])) {
          userData.sorting_label[key] = value;
        }
      });
    }
    const { sorting_options: sortOptions } = sortdata;
    const { sorting_options_config: sortOptionsConfig } = sortdata;
    if (hasValue(sortOptions)) {
      if (hasValue(userData.sorting_options)) {
        userData.sorting_options = sortOptions;
      }
      sortOptions.forEach((option) => {
        if (hasValue(sortOptionsConfig[option])) {
          userData.sorting_options_config[option] = sortOptionsConfig[option];
        }
      });
    }

    return userData;
  };

  /**
   * Fetches userdata from algolia result and process it for facets
   * override by contexts eg: women_shirt.
   *
   * @param {array} data
   *   userData array from algolia search result.
   *
   * @returns {Object}
   *   userData with facets override by context.
   */
  const processUserDataWithOverrides = (data) => {
    const { ruleContext } = drupalSettings.algoliaSearch;
    let userData = {};
    if (hasValue(ruleContext)) {
      // Get contexts array from drupalSettings.
      const contexts = Object.values(ruleContext);
      // Add default context to the list of contexts.
      contexts.unshift('default');
      // Sort userData array by context.
      data.sort((a, b) => contexts.indexOf(a.context) - contexts.indexOf(b.context));
    }

    data.forEach((filterData) => {
      if (hasValue(filterData) && hasValue(filterData.context) && filterData.context === 'default') {
        // Collect default context userData in the first iteration.
        userData = { ...filterData };
      } else {
        // The data is available for other contexts then collect the data and
        // override the default context data.
        const { facets_config: facetsConfig } = filterData;
        if (hasValue(facetsConfig)) {
          // Overrides filter configuration from context data configured in
          // algolia.
          userData = overrideFilterConfig(userData, facetsConfig);
        }
        // Overrides sort configuration from context data configured in
        // algolia.
        userData = overrideSortByConfig(userData, filterData);
      }
    });

    return userData;
  };

  /**
   * Returns sortby configuration in the required format from userData.
   *
   * @param sortOptions
   *   Sort options like default, title_asc, final_price_desc etc.
   * @param sortOptionsConfig
   *   Sort options config like index, label.
   * @param langcode
   *   Language code to select english or arabic index, label.
   *
   * @returns {[]}
   *   Array of items for sortby facet.
   */
  const getSortByOptions = (sortOptions, sortOptionsConfig, langcode) => {
    const items = [];
    sortOptions.forEach((option) => {
      const item = {
        value: sortOptionsConfig[option].index[langcode],
        label: sortOptionsConfig[option].label[langcode],
        gtm_key: option,
      };

      items.push(item);
    });

    return items;
  };

  /**
   * Builds Facets from userData received from algolia query response.
   *
   * @param {object} data
   *   Facets userData from algolia response.
   */
  const buildFacets = (data) => {
    // Check if algolia response userData has value.
    if (!hasValue(data)) {
      return;
    }

    // Initialize facets array to get facets from userData.
    const facetsArray = [];

    // Initialize indentifier prefix.
    let identifierPrefix = '';
    let userData = data[0];
    if (pageType !== 'search') {
      // If the page type is search then prefix is empty
      // else it will be current language code.
      identifierPrefix = drupalSettings.path.currentLanguage;
      // Process any override rules in userData.
      userData = processUserDataWithOverrides(data);
    }

    // Get facets config from userData.
    const { facets_config: filters } = userData;

    // Loop through the facets config from userData.
    Object.entries(filters).forEach(([key, value]) => {
      // Format the filters as required by widget manager.
      const filter = {
        identifier: `${key}.${identifierPrefix}`,
        label: value.label[identifierPrefix],
        name: value.label.en, // Used for gtm, hence always english value.
        widget: {
          type: value.widget.type,
        },
        id: value.slug.replace('_', ''),
        alias: value.slug,
      };

      if (hasValue(value.widget.config)) {
        // Get config is present in facet config.
        filter.widget.config = value.widget.config;
      }

      if (hasValue(value.facets_values)) {
        // Get facet values if explicitly set in config for some facets
        // like attr_delivery_ways.
        filter.facets_value = value.facets_value;
      }

      facetsArray.push(filter);
    });

    const {
      sorting_label: sortlabel,
      sorting_options: sortOptions,
      sorting_options_config: sortOptionsConfig,
    } = userData;

    const sort = {
      identifier: 'sort_by',
      name: sortlabel.en,
      label: sortlabel[identifierPrefix],
      widget: {
        type: 'sort_by',
        items: getSortByOptions(sortOptions, sortOptionsConfig, identifierPrefix),
      },
    };

    facets.unshift(sort);

    setFacets(facets);
  };

  // Set facets config if facet is build from userData.
  let facetsConfig = facets;

  if (!isConfigurableFiltersEnabled()) {
    // Check if configurable attributes is disabled.
    // Set facet config from drupal settings.
    facetsConfig = getFilters(pageType);
  }

  let facetsList = [];

  if (hasValue(facetsConfig)) {
    facetsConfig.forEach((facet) => {
      facetsList.push(
        <WidgetManager
          key={facet.identifier}
          facet={facet}
          indexName={indexName}
          filterResult={(test) => updateFilterResult(test)}
          pageType={pageType}
          attribute={facet.identifier}
        />,
      );
    });
  }

  // If configurable filters is enabled then wrap facetsLists in
  // Dynamic widgets component. Dynamic widgets component uses renderingContent
  // from algolia search result which has the facet display configuration.
  if (isConfigurableFiltersEnabled()) {
    facetsList = (
      <DynamicWidgets buildFacets={buildFacets}>
        {facetsList}
      </DynamicWidgets>
    );
  }

  return (
    <div ref={ref} className="filter-facets">
      {facetsList}
    </div>
  );
};

export default Filters;
