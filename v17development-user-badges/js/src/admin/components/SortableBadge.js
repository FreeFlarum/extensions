import app from 'flarum/admin/app';
import Component from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import Icon from 'flarum/common/components/Icon';
import UserBadge from '../../common/components/UserBadge';
import ConfirmModal from './ConfirmModal';
import EditBadgeModal from './EditBadgeModal';

export default class SortableBadge extends Component {
  oninit(attrs) {
    super.oninit(attrs);

    this.loading = false;
  }

  view() {
    const badge = this.attrs.badge;

    if (!badge) return null;

    return (
      <li data-id={badge.id()} className={!badge.isVisible() ? 'BadgeDisabled' : ''}>
        <div className="SortableBadges-info">
          <span className={'BadgeDetails'}>
            <UserBadge
              badge={badge}
              onclick={() =>
                app.modal.show(EditBadgeModal, {
                  badge,
                })
              }
              forceVisibility={true}
            />
          </span>
          <span className={'BadgeButtons'}>
            <Button
              className={'Button'}
              disabled={this.loading}
              onclick={() =>
                app.modal.show(EditBadgeModal, {
                  badge,
                })
              }
            >
              <Icon name={'fas fa-edit'} />
            </Button>
            <Button
              className={'Button'}
              disabled={this.loading}
              onclick={() =>
                app.modal.show(ConfirmModal, {
                  text: app.translator.trans('v17development-flarum-badges.admin.confirm_messages.delete_badge'),
                  promise: true,
                  onconfirm: (resolve, reject) => badge.delete().then(resolve).catch(reject),
                })
              }
            >
              <Icon name={'fas fa-trash'} />
            </Button>
          </span>
        </div>
      </li>
    );
  }
}
