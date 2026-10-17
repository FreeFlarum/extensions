import app from 'flarum/admin/app';
import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';

export default class ConfirmModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);

    this.loading = false;
  }

  className() {
    return 'Modal--small FlarumBadgesConfirmModal';
  }

  title() {
    return app.translator.trans(`v17development-flarum-badges.admin.confirm_dialog.title`);
  }

  content() {
    return [
      <div className="Modal-body">
        <p>{this.attrs.text}</p>
      </div>,

      <div className="Modal-footer FlarumBadgesConfirmButtons">
        {Button.component(
          {
            className: 'Button',
            disabled: this.loading,
            onclick: () => this.hide(),
          },
          app.translator.trans('v17development-flarum-badges.admin.confirm_dialog.no')
        )}
        {Button.component(
          {
            className: 'Button Button--primary',
            loading: this.loading,
            onclick: () => this.confirm(),
          },
          app.translator.trans('v17development-flarum-badges.admin.confirm_dialog.yes')
        )}
      </div>,
    ];
  }

  /**
   * Confirm
   */
  confirm() {
    // Act as promise
    if (this.attrs.promise) {
      this.loading = true;

      this.attrs.onconfirm(
        () => this.hide(),
        () => {
          this.loading = false;
          m.redraw();
        }
      );
      return;
    }

    this.attrs.onconfirm();
    this.hide();
  }
}
