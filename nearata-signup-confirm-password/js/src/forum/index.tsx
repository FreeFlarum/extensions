import { extend } from 'flarum/common/extend';
import extractText from 'flarum/common/utils/extractText';
import Stream from 'flarum/common/utils/Stream';
import app from 'flarum/forum/app';

/**
 * ref: https://github.com/flarum/framework/blob/v1.7.1/framework/core/js/src/forum/components/SignUpModal.tsx
 */
app.initializers.add('nearata-signup-confirm-password', () => {
  extend('flarum/forum/components/SignUpModal', 'oninit', function () {
    this.confirmPassword = Stream('');
  });

  extend('flarum/forum/components/SignUpModal', 'fields', function (items) {
    if (this.attrs.token) {
      return;
    }

    const placeholder = extractText(app.translator.trans('nearata-signup-confirm-password.forum.field_placeholder'));

    items.add(
      'nearataConfirmPassword',
      <div class="Form-group">
        <label className="label">{placeholder}</label>
        <input
          className="FormControl"
          name="confirmPassword"
          type="password"
          autocomplete="new-password"
          placeholder={placeholder}
          aria-label={placeholder}
          bidi={this.confirmPassword}
          disabled={this.loading}
        />
      </div>,
      10
    );
  });

  extend('flarum/forum/components/SignUpModal', 'submitData', function (data) {
    if (!this.attrs.token) {
      data.confirmPassword = this.confirmPassword();
    }
  });
});
