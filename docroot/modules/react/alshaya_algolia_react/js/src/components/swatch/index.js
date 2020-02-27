import React from 'react';
import ImageElement from '../gallery/imageHelper/ImageElement';

const Swatch = (props) => {
  const selected_image = props.url + '?selected=' + props.swatch.child_id;
  return (
    <a href={selected_image}>
      <span className='swatch-block swatch-image'>
        {props.url ?
          <ImageElement data-sku-image={props.swatch.product_url} src={props.swatch.image_url} />
          :
          <ImageElement src={props.swatch.image_url} />
        }
      </span>
    </a>
  )
}

const Swatches = (props) => {
  if ((props.swatches === undefined)) {
    return null;
  }

  // Display the colors count for mobile only if different variants images
  // being shown in gallery on PLP.
  const show_variants_thumbnail_plp_gallery = drupalSettings.reactTeaserView.showVariantsThumbnail;
  // Display the configured number of swatches.
  const limit = drupalSettings.reactTeaserView.swatches.swatchPlpLimit;
  const total_no_of_swatches = props.swatches.length;
  const diff = total_no_of_swatches - limit;
  const swatch_color_count = total_no_of_swatches + (total_no_of_swatches > 1 ? ' colors' : ' color')
  const swatch_more_text = (diff > 0) ? diff + ' colors' : null;

  return (
    <React.Fragment>
      {total_no_of_swatches > 0 && !show_variants_thumbnail_plp_gallery ?
        <div className="swatches">
          {props.swatches.slice(0, limit).map((swatch, key) => <Swatch swatch={swatch} key={key} url={props.url} />)}
          {swatch_more_text ? <a className="swatch-more-link product-selected-url" href={props.url}>+ {swatch_more_text}</a> : null}
        </div>
        :
        <React.Fragment>
          {total_no_of_swatches > 0 ?
            <div className="swatches">
              <div className="swatch-color-count-wrapper mobile-only-block">
                <a className="swatch-color-count product-selected-url"
                  href={props.url}>{swatch_color_count}</a>
              </div>
            </div>
            :
            null
          }
        </React.Fragment>
      }
    </React.Fragment>
  )
}

export default Swatches;
