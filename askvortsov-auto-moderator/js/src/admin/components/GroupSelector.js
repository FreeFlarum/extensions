import Component from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import Dropdown from 'flarum/common/components/Dropdown';
import Icon from 'flarum/common/components/Icon';
import Group from 'flarum/common/models/Group';

export default class GroupSelector extends Component {
  view() {
    const group = app.store.getById('groups', this.attrs.value);
    const label = group
      ? [<Icon name={group.icon()} />, '\t', group.namePlural()]
      : app.translator.trans('askvortsov-auto-moderator.admin.group_selector.placeholder');
    return (
      <div className="Form-group">
        <label>{this.attrs.label}</label>

        {this.attrs.disabled ? (
          <div className="Button Button--danger">{label}</div>
        ) : (
          <Dropdown label={label} disabled={this.attrs.disabled} buttonClassName="Button Button--danger">
            {app.store
              .all('groups')
              .filter((g) => ![Group.MEMBER_ID, Group.GUEST_ID].includes(g.id()))
              .map((g) =>
                Button.component(
                  {
                    active: group && group.id() === g.id(),
                    disabled: group && group.id() === g.id(),
                    icon: g.icon(),
                    onclick: () => {
                      this.attrs.onchange(g.id());
                    },
                  },
                  g.namePlural()
                )
              )}
          </Dropdown>
        )}
      </div>
    );
  }
}
