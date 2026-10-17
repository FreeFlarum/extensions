import { extend, override } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import LinkButton from 'flarum/common/components/LinkButton';
import TextEditor from 'flarum/common/components/TextEditor';
import listItems from 'flarum/common/helpers/listItems';
import classList from 'flarum/common/utils/classList';
import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import ComposerState from 'flarum/forum/states/ComposerState';
import ComposerPage from './components/ComposerPage';

const DISCUSSION_COMPOSER = 'flarum/forum/components/DiscussionComposer';
const PRIVATE_DISCUSSION_COMPOSER = 'ext:fof/byobu/forum/pages/discussions/PrivateDiscussionComposer';

function isComposerPage(): boolean {
  const currentRouteName = (app.current.data as any).routeName;

  return currentRouteName === 'compose' || currentRouteName === 'composeTag';
}

function shouldUsePage(tagSlugs: string[]): boolean {
  const pageTags = app.forum.attribute<string>('composerPageTags');

  // If nothing set, applies to all
  if (!pageTags) {
    return true;
  }

  return pageTags.split(',').some((slug) => tagSlugs.indexOf(slug) !== -1);
}

app.initializers.add('clarkwinkelmann-composer-page', () => {
  app.routes.compose = { path: '/compose', component: ComposerPage };
  // Calling the parameter the same as the original tag route (:tags) seems to lead to issues detecting the current tag when switching between the 2 routes
  // So we'll call it :slug instead
  app.routes.composeTag = { path: '/t/:slug/compose', component: ComposerPage };

  override('flarum/forum/components/Composer', 'view', function (original) {
    if (!isComposerPage()) {
      return original();
    }

    // We don't really need any of this, but we return an almost identical template to the original to reduce the risk of extensions breaking
    // Also there's a lot of hooks around those elements so removing them from the DOM might break the reply composer
    // The only goal is to remove the ComposerBody element so we don't have 2 text editors on the page at the same time
    return m(
      '.Composer',
      {
        className: classList({
          normal: this.state.position === ComposerState.Position.NORMAL,
          minimized: this.state.position === ComposerState.Position.MINIMIZED,
          fullScreen: this.state.position === ComposerState.Position.FULLSCREEN,
          active: this.active,
          visible: this.state.isVisible(),
        }),
      },
      [
        m('.Composer-handle', {
          oncreate: this.configHandle.bind(this),
        }),
        m('ul.Composer-controls', listItems(this.controlItems().toArray())),
        m('.Composer-content'),
      ]
    );
  });

  let lastTagSelectionHash = '[]';

  // This code could be in any onupdate hook, as long as it's a component that exists continously
  extend('flarum/forum/components/Composer', 'onupdate', function () {
    const selectedTagSlugs = ((app.composer.fields || {}).tags || []).map((tag) => tag.slug());

    const newHash = JSON.stringify(selectedTagSlugs);

    // We don't need a check for private discussions here
    // Since the Byobu composer doesn't have a control for tags, the hash will never change
    if (lastTagSelectionHash !== newHash) {
      // Check if new tags selection needs the composer page
      if (app.composer.bodyMatches(DISCUSSION_COMPOSER) && shouldUsePage(selectedTagSlugs) && !isComposerPage()) {
        // Don't switch page before the tags modal has closed otherwise it can never be closed again the next time it's opened
        setTimeout(() => {
          app.composer.show();
        }, 220); // Modal animation is 200ms by default, add a bit more for margin
      }

      lastTagSelectionHash = newHash;
    }
  });

  override(ComposerState.prototype, 'show', function (original) {
    if (!this.bodyMatches(DISCUSSION_COMPOSER)) {
      return original();
    }

    if (this.bodyMatches(PRIVATE_DISCUSSION_COMPOSER) && !app.forum.attribute<string>('composerPagePrivateDiscussions')) {
      return original();
    }

    // Use normal composer for tags where this feature is not enabled, unless we are already on the page
    if (!shouldUsePage((this.fields?.tags || []).map((tag) => tag.slug())) && !isComposerPage()) {
      return original();
    }

    // Set position to minimized in case the user leaves changes page without cancelling the composer
    this.position = ComposerState.Position.HIDDEN;

    if (!isComposerPage()) {
      m.route.set(app.route('compose'));
    }
  });

  override(ComposerState.prototype, 'isVisible', function (original) {
    // We must return visible here so that features like the exit callbacks get triggered
    // We must also check the body because otherwise it returns true in ComposerState.prototype.load which creates nested redraws
    if (this.bodyMatches(DISCUSSION_COMPOSER) && isComposerPage()) {
      return true;
    }

    return original();
  });

  override(DISCUSSION_COMPOSER, 'oninit', function (original, vnode) {
    // This code runs before oninit, so this.composer or this.attrs won't work. We need vnode.attrs
    const content = vnode.attrs.composer.fields.content();

    original(vnode);

    // Copy over the existing value, otherwise it's lost when changing between page<->composer
    this.composer.fields.content(content);
  });

  extend(DISCUSSION_COMPOSER, 'view', function (vdom: any) {
    if (!isComposerPage()) {
      return;
    }

    // Add a second button that we will keep at the bottom on mobile
    // Otherwise it's not super obvious you need to click at the top right
    // Some people might even want to hide the top right button to force people to scroll properly to the bottom
    vdom.children.push(
      m(
        '.ComposerPageMobileSubmit',
        Button.component(
          {
            icon: 'fas fa-paper-plane',
            className: 'Button Button--primary',
            onclick: this.onsubmit.bind(this),
          },
          this.attrs.submitLabel
        )
      )
    );
  });

  extend(IndexSidebar.prototype, 'items', function (items) {
    const newDiscussion = items.has('newDiscussion') && items.get('newDiscussion');

    // Don't replace button if guest, because that button will open the login modal in that situation
    if (!newDiscussion || !app.session.user) {
      return;
    }

    // Do not modify Byobu's button
    // This means there will be no "continue" button either but that's fine
    if (newDiscussion.attrs.itemClassName?.indexOf('fof-byobu_primaryControl') !== -1) {
      return;
    }

    if (isComposerPage()) {
      newDiscussion.attrs.className = newDiscussion.attrs.className.replace('Button--primary', '');
      newDiscussion.attrs.icon = 'fas fa-trash';
      delete newDiscussion.attrs.itemClassName; // Remove App-primaryControl otherwise there might be a conflict with the composer buttons
      newDiscussion.children = app.translator.trans('clarkwinkelmann-composer-page.forum.nav.cancel');

      return;
    }

    if (app.composer.bodyMatches(DISCUSSION_COMPOSER)) {
      // Remove color since the button won't start a new discussion in current tag
      newDiscussion.attrs.className = newDiscussion.attrs.className.replace('Button--tagColored', '');
      newDiscussion.attrs.className = newDiscussion.attrs.className.replace('Button--primary', '');
      if (newDiscussion.attrs.style) {
        delete newDiscussion.attrs.style['--color'];
      }
      newDiscussion.children = app.translator.trans('clarkwinkelmann-composer-page.forum.nav.continue');
      return;
    }

    const tag = app.currentTag && app.currentTag();

    const tagSlugs = [];

    if (tag) {
      tagSlugs.push(tag.slug());

      const parent = tag.parent();

      if (parent) {
        tagSlugs.push(parent.slug());
      }
    }

    // If a tag setting was set, we'll start by opening normally
    if (!shouldUsePage(tagSlugs)) {
      return;
    }

    // Replace button with link. This serves multiple purposes:
    // - No need to override IndexSidebar.prototype.newDiscussionAction to change the URL for tags
    // - Ability to open in new tab
    newDiscussion.tag = LinkButton;
    newDiscussion.attrs.href = tag ? app.route('composeTag', { slug: tag.slug() }) : app.route('compose');
    delete newDiscussion.attrs.onclick;
  });

  override(IndexSidebar.prototype, 'newDiscussionAction', function (original) {
    // Action for "cancel" label
    if (isComposerPage()) {
      // Call close to invoke any exit callback
      app.composer.close();

      // Check that the composer successfully closed
      // (it could still be prevented at that point)
      if (!(app.composer.body as any).componentClass) {
        m.route.set(app.route('index'));
      }

      return Promise.resolve();
    }

    // Action for our "continue" label. Will try to re-open the page, or just the composer if page is not required
    if (app.composer.bodyMatches(DISCUSSION_COMPOSER)) {
      app.composer.show();

      return Promise.resolve();
    }

    return original();
  });

  // This method is exposed in FoF Upload for this specific reason
  // We don't really need to check whether we are on the composer page, we can just return a new selector if the original returned nothing
  // We target the editor and not the whole page to make sure it doesn't interfere with Formulaire upload fields
  override(TextEditor.prototype, 'fofUploadDragAndDropTarget', function (original) {
    const target = original ? original() : null;

    return target || this.$('.TextEditor-editor')[0];
  });
});
