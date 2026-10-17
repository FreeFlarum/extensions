import Component from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import Tooltip from 'flarum/common/components/Tooltip';
import Icon from 'flarum/common/components/Icon';
import classList from 'flarum/common/utils/classList';
import MinMaxSelector from './MinMaxSelector';

import UndefinedDriverItem from './UndefinedDriverItem';

export default class MetricItem extends Component {
  view() {
    const metric = this.attrs.metric;
    const metricDef = this.attrs.metricDef;
    const selected = this.attrs.selected;

    if (!metricDef) return <UndefinedDriverItem item={metric} selected={selected} />;

    return (
      <li>
        <div
          className={classList({
            'DriverListItem-info': true,
            'DriverListItem--missingExt': metricDef.missingExt,
          })}
        >
          {metricDef.missingExt && (
            <Tooltip text={app.translator.trans('askvortsov-auto-moderator.admin.criterion_page.driver_missing_ext')}>
              <Icon name="fas fa-exclamation-triangle" />
            </Tooltip>
          )}
          <span className="DriverListItem-name">{app.translator.trans(metricDef.translationKey)}</span>
          {Button.component({
            className: 'Button Button--link',
            icon: 'fas fa-trash-alt',
            onclick: () => selected(selected().filter((val) => val !== metric)),
          })}
        </div>
        <MinMaxSelector min={metric.min} max={metric.max}></MinMaxSelector>
        <hr />
      </li>
    );
  }
}
