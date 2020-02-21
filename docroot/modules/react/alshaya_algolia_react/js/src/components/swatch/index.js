import React from 'react';
import ImageElement from '../gallery/imageHelper/ImageElement';

const Swatch = (props) => {
  const selected_image = props.url + '?selected=' + props.key;
  return (
    <a href={selected_image}>
      <span className='swatch-block swatch-image'>
        {props.url ?
          <ImageElement data-sku-image={props.swatch.image_url} src={props.swatch.image_url} />
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
  const limit = drupalSettings.reactTeaserView.swatches.swatchPlpLimit;
  const total_no_of_swatches = props.swatches.length;
  var swatch_more_text = null;
  const diff = total_no_of_swatches - limit;
  if (diff > 0) {
     swatch_more_text = diff + ' colors';
  }
  console.log(swatch_more_text);

  return (
    <React.Fragment>
      {props.swatches.length > 0 ?
        <div className="swatches">
          {props.swatches.slice(0, limit).map((swatch, key) => <Swatch swatch={swatch} key={key} url={props.url}/>)}
          {swatch_more_text ?
            <a className="swatch-more-link product-selected-url" href={props.url}>+ {swatch_more_text}</a>
            :
            null
          }
        </div>
        :
        null
      }
      {props.swatches.swatch_color_count ?
        <div className="swatches">
          <div className="swatch-color-count-wrapper mobile-only-block">
            <a className="swatch-color-count product-selected-url"
              href="{$`{props.url}`}">{$`{props.swatches.swatch_color_count }`}</a>
          </div>
        </div>
        :
        null
      }
    </React.Fragment>
  )
}

export default Swatches;
