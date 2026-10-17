import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';
import LanguageDropdown from './components/LanguageDropdown';
import Stream from 'flarum/common/utils/Stream';
import type Tag from 'ext:flarum/tags/common/models/Tag';
import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

const SUBSCRIPTION_MODAL = 'ext:fof/follow-tags/forum/components/SubscriptionModal';
const SUBSCRIPTION_LANGUAGE_PRIORITY = 80;

interface SubscriptionModal {
  attrs: { model: Tag };
  subscription: string;
  loading: Stream<boolean>;
  language: Stream<string | null>;
  hide(): void;
}

export default function extendSubscriptionModal() {
  if (!('fof-follow-tags' in flarum.extensions)) return;

  extend(SUBSCRIPTION_MODAL, 'oninit', function (this: SubscriptionModal) {
    let subscriptionLanguage = this.attrs.model.subscriptionLanguage();

    if (!app.forum.attribute('fof-discussion-language.showAnyLangOpt') && subscriptionLanguage === null) {
      subscriptionLanguage = app.translator.getLocale();
    }

    this.language = Stream(subscriptionLanguage);
  });

  extend(SUBSCRIPTION_MODAL, 'formOptionItems', function (this: SubscriptionModal, items: ItemList<Mithril.Children>) {
    items.add(
      'subscription_language',
      <div className="Form-group">
        <label>{app.translator.trans('fof-discussion-language.forum.sub_controls.subscription_language_label')}</label>
        <p className="helpText">{app.translator.trans('fof-discussion-language.forum.sub_controls.subscription_language_help')}</p>
        <LanguageDropdown
          selected={this.language() ?? undefined}
          onclick={(key: string) => {
            this.language(key);
          }}
        />
      </div>,
      SUBSCRIPTION_LANGUAGE_PRIORITY
    );
  });

  // fof/follow-tags only accepts the `subscription` attribute from non-moderators,
  // so the language is sent as request meta.
  override(SUBSCRIPTION_MODAL, 'saveSubscription', function (this: SubscriptionModal, original: unknown, subscription: string) {
    this.loading(true);
    this.subscription = subscription;

    this.attrs.model
      .save({ subscription }, { meta: { subscriptionLanguage: this.language() } })
      .then(() => {
        this.loading(false);
        m.redraw();
        this.hide();
      })
      .catch(() => {
        this.loading(false);
        m.redraw();
      });
  });
}
