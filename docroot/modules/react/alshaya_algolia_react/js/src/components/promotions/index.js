import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import { isPromotionFrameEnabled } from '../../utils/indexUtils';

const Promotion = ({ promotion }) => (
  <span className="sku-promotion-item">
    {(drupalSettings.algoliaSearch.pageSubType !== 'undefined'
      && drupalSettings.algoliaSearch.pageSubType === 'promotion'
      && drupalSettings.algoliaSearch.promotionNodeId === promotion.id) ? (
        <span className="sku-promotion-text">{promotion.text}</span>
      ) : (
        <>
          <ConditionalView condition={isPromotionFrameEnabled()}>
            <div className="sku-promotion-text">{promotion.text}</div>
            <a className="sku-promotion-link" href={promotion[`url_${drupalSettings.path.currentLanguage}`]}>
              {Drupal.t('Shop all products in this offer')}
            </a>
          </ConditionalView>
          <ConditionalView condition={!isPromotionFrameEnabled()}>
            <a className="sku-promotion-link" href={promotion[`url_${drupalSettings.path.currentLanguage}`]}>
              {promotion.text}
            </a>
          </ConditionalView>
        </>
      )}
  </span>
);

const Promotions = ({ promotions }) => {
  const promotionList = (promotions)
    ? promotions.map((promotion) => {
      if (typeof promotion.context === 'undefined' || promotion.context.includes('web') || !promotion.context.length) {
        return <Promotion key={promotion.text} promotion={promotion} />;
      }
      return '';
    })
    : '';
  if (promotionList !== '' && promotionList !== 'null') {
    return <div className="promotions">{promotionList}</div>;
  }
  return (null);
};

export default Promotions;
