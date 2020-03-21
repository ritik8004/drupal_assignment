import React from 'react';
import SectionTitle from '../section-title';

export default class FilterList extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      items: [],
      selected: null,
    };
  }


  componentDidMount() {
    const { selected, options } = this.props;
    this.setState({
      items: options,
      selected,
    });
  }

  /**
   * Filter the list on search.
   */
  filterList = () => {
    const { options } = this.props;
    let updatedList = options;
    updatedList = updatedList.filter((item) => item.label.toLowerCase().search(
      event.target.value.toLowerCase(),
    ) !== -1);

    this.setState({
      items: updatedList,
    });
  }

  /**
   * Handle click on <li>.
   */
  handleLiClick = (e) => {
    const {
      toggleFilterList,
      processingCallback,
    } = this.props;
    this.setState({
      selected: e.target.parentElement.value,
    });

    toggleFilterList();

    // Call the process.
    processingCallback(e.target.parentElement.value);
  };

  backButtonClick = () => {
    const { toggleFilterList } = this.props;
    toggleFilterList();
  };

  render() {
    const { items, selected } = this.state;
    const { panelTitle, placeHolderText } = this.props;
    if (items === 0) {
      return (null);
    }

    return (
      <div className="filter-list">
        <div className="spc-filter-panel-header">
          <span className="spc-filter-panel-back" onClick={() => this.backButtonClick()} />
          <SectionTitle>{panelTitle}</SectionTitle>
        </div>
        <div className="spc-filter-panel-search-form-item">
          <input className="spc-filter-panel-search-field" type="text" placeholder={placeHolderText} onChange={this.filterList} />
        </div>
        <div className="spc-filter-area-panel-list-wrapper">
          <ul>
            {
            items.map((item) => (
              <li
                key={item.value}
                value={item.value}
                className={
                    ((selected !== undefined && selected.value == item.value))
                      ? 'active' : 'in-active'
}
              >
                <span onClick={(e) => this.handleLiClick(e)} className="spc-area-panel-item">{item.label}</span>
              </li>
            ))
            }
          </ul>
        </div>
      </div>
    );
  }
}
