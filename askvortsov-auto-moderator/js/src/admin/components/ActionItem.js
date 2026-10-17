import Component from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import Tooltip from 'flarum/common/components/Tooltip';
import Icon from 'flarum/common/components/Icon';
import classList from 'flarum/common/utils/classList';

import UndefinedDriverItem from './UndefinedDriverItem';
import DriverSettings from './DriverSettings';

export default class ActionItem extends Component {
  view() {
    const action = this.attrs.action;
    const actionDef = this.attrs.actionDef;
    const selected = this.attrs.selected;

    if (!actionDef) return <UndefinedDriverItem item={action} selected={selected} />;

    return (
      <li>
        <div
          className={classList({
            'DriverListItem-info': true,
            'DriverListItem--missingExt': actionDef.missingExt,
          })}
        >
          {actionDef.missingExt && (
            <Tooltip text={app.translator.trans('askvortsov-auto-moderator.admin.criterion_page.driver_missing_ext')}>
              <Icon name="fas fa-exclamation-triangle" />
            </Tooltip>
          )}
          <span className="DriverListItem-name">{app.translator.trans(actionDef.translationKey)}</span>
          {Button.component({
            className: 'Button Button--link',
            icon: 'fas fa-trash-alt',
            onclick: () => selected(selected().filter((val) => val !== action)),
          })}
          <DriverSettings driverType="action" type={action.type} settings={action.settings} availableSettings={actionDef.availableSettings} />
        </div>
        <hr />
      </li>
    );
  }
}
