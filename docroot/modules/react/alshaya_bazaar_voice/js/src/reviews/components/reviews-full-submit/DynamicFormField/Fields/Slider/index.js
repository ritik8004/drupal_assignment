import React from 'react';
import ConditionalView from '../../../../../../common/components/conditional-view';

class Slider extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      sliderVal: '',
      activeId: '',
    };
  }

  setActiveId = (e, sliderLabel, sliderValue) => {
    if (sliderLabel.length > 0) {
      this.setState({
        activeId: sliderLabel,
        sliderVal: sliderValue,
      });
    }
  };

  render() {
    const {
      required,
      id,
      label,
      options,
      text,
    } = this.props;
    const { sliderVal, activeId } = this.state;
    const rangeLength = Object.keys(options).length;

    return (
      <>
        <ConditionalView condition={text !== undefined}>
          <div id={`${id}-head-row`} className="head-row">{text}</div>
        </ConditionalView>
        <div id={id} className="write-review-type-slider">
          <div className="select-slider__wrap">
            <div className="selectedValue">
              <span>
                {label}
                {(required) ? '*' : '' }
                {':'}
              </span>
              <span className="selectedLabel">{activeId}</span>
            </div>
            <div className="range-slider-block" id={`${id}-error`}>
              <div className={`range-slider range-${rangeLength}`}>
                {Object.values(options).map((sliderLabel, i) => {
                  const sliderValue = i + 1;
                  return (
                    <React.Fragment key={sliderValue}>
                      <input
                        id={`slider-${sliderValue}`}
                        type="radio"
                        name={`slider-${id}`}
                        value={sliderValue}
                        required={required}
                      />

                      <label
                        className={activeId === sliderLabel ? 'active' : 'inactive'}
                        onClick={(e) => this.setActiveId(e, sliderLabel, sliderValue)}
                        htmlFor={sliderLabel}
                      >
                        <span>{sliderLabel}</span>
                      </label>
                    </React.Fragment>
                  );
                })}
              </div>
            </div>
            <input type="hidden" id={id} name={id} required={required} value={sliderVal || ''} />
          </div>
        </div>
      </>
    );
  }
}

export default Slider;
