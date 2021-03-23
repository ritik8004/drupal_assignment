import React from 'react';
import ConditionalView from '../../../../../../common/components/conditional-view';

class StarRating extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      rating: 0,
      hover: 0,
    };
  }

  onClick = (e, ratingValue) => {
    if (ratingValue > 0) {
      this.setState({
        rating: ratingValue,
      });
    }
  };

  onHover = (e, ratingValue) => {
    if (ratingValue > 0) {
      this.setState({
        hover: ratingValue,
      });
    } else {
      this.setState({
        hover: null,
      });
    }
  };

  render() {
    const {
      required,
      id,
      label,
      text,
    } = this.props;
    const { rating, hover } = this.state;

    return (
      <>
        <ConditionalView condition={text !== undefined}>
          <div className="head-row">{text}</div>
        </ConditionalView>
        <div className="write-review-type-star-rating">
          <div className={`select-star__wrap ${id}`}>
            <label className="star-rating-label" htmlFor={label}>
              {label}
              {' '}
              {(required) ? '*' : '' }
            </label>
            <div className="star-counter" id={`${id}-error`}>
              {[...Array(5)].map((star, i) => {
                const ratingValue = i + 1;
                return (
                  <label key={ratingValue}>
                    <input
                      className="select-star-rating__input"
                      type="radio"
                      name={`star-${id}`}
                      value={ratingValue}
                      onClick={(e) => this.onClick(e, ratingValue)}
                      required={required}
                    />
                    <span
                      className={ratingValue <= (hover || rating)
                        ? 'select-star-rating__ico ratingfull'
                        : 'select-star-rating__ico rating'}
                      onMouseEnter={(e) => this.onHover(e, ratingValue)}
                      onMouseLeave={(e) => this.onHover(e, null)}
                    />
                  </label>
                );
              })}
            </div>
            <input type="hidden" id={id} name={id} required={required} value={rating || ''} />
          </div>
        </div>
      </>
    );
  }
}

export default StarRating;
