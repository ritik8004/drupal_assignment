import React from 'react'

export default function SideBar(props) {
  return (
    <aside className="c-sidebar-first">
      <div className="c-sidebar-first__region">
        <div className="region region__sidebar-first clearfix">
          <div className="c-facet__blocks">
            <div className="c-facet__blocks c-facet block-facet-blockcategory-facet-search c-accordion c-collapse-item">
              {props.children}
            </div>
          </div>
        </div>
      </div>
    </aside>
  );
};
