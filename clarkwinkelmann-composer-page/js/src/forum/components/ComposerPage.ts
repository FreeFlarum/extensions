import app from 'flarum/forum/app';
import Page from 'flarum/common/components/Page';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import extractText from 'flarum/common/utils/extractText';
import Stream from 'flarum/common/utils/Stream';
import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import PageStructure from 'flarum/forum/components/PageStructure';
import type Tag from 'ext:flarum/tags/common/models/Tag';

// Copied from flarum-tags addTagFilter()
const findTag = (slug: string) => app.store.all<Tag>('tags').find((tag) => tag.slug().localeCompare(slug, undefined, { sensitivity: 'base' }) === 0);

function isPrivateDiscussion(): boolean {
  return app.composer.bodyMatches('ext:fof/byobu/forum/pages/discussions/PrivateDiscussionComposer');
}

export default class ComposerPage extends Page {
  loading: boolean = false;
  handlers: any = {};

  currentTag(): Promise<Tag | null | undefined> {
    return new Promise((resolve) => {
      const { slug } = m.route.param();

      if (!slug) {
        return resolve(null);
      }

      const tag = findTag(slug);

      if (tag) {
        return resolve(tag);
      }

      this.loading = true;

      // Same code as in flarum-tags app.currentTag
      // Maybe the number of relationships is overkill for our use case here
      app.store.find('tags', slug, { include: 'children,children.parent,parent' }).then(() => {
        this.loading = false;
        resolve(findTag(slug));
      });
    });
  }

  oninit(vnode: any) {
    super.oninit(vnode);

    app.setTitle(
      extractText(app.translator.trans('clarkwinkelmann-composer-page.forum.page' + (isPrivateDiscussion() ? '-private' : '') + '.title'))
    );
    app.setTitleCount(0);

    // Invoke the preloaded payload to get access to the current tag(s)
    app.preloadedApiDocument();

    const { user } = app.session;

    if (!user) {
      this.loading = true;

      // If user is logged out, redirect to index page
      m.route.set(app.route('index'));

      return;
    }

    if (!app.composer.bodyMatches('flarum/forum/components/DiscussionComposer')) {
      // The composer body is loaded asynchronously, wait for it before rendering it
      this.loading = true;

      app.composer
        .load(() => import('flarum/forum/components/DiscussionComposer'), {
          user,
        })
        .then(() => {
          // Will not actually show the real composer, but that's where our logic to set the default when leaving the standalone page resides
          // so we need to call it at some point to prepare the composer
          app.composer.show();

          this.loading = false;

          const fields = app.composer.fields as any;

          if (fields) {
            const { title, content } = m.route.param();

            if (title) {
              // Most likely, the title field won't exist yet at this point
              // but we'll handle both options just to be sure
              if (fields.title) {
                fields.title(title);
              } else {
                fields.title = Stream(title);
              }
            }

            if (content && fields.content) {
              fields.content(content);
            }

            const currentRouteName = (app.current.data as any).routeName;

            if (currentRouteName === 'composeTag') {
              this.currentTag().then((tag) => {
                if (tag) {
                  const parent = tag.parent();
                  fields.tags = parent ? [parent, tag] : [tag];
                } else {
                  fields.tags = [];
                }

                m.redraw();

                this.updateHeight();
              });
            }
          }

          m.redraw();
        });
    }
  }

  view() {
    const isPrivate = isPrivateDiscussion();

    return m(
      PageStructure,
      {
        className: 'ComposerPageContainer' + (isPrivate ? ' ComposerPageContainer--private-discussion' : ''),
        sidebar: () => m(IndexSidebar),
      },
      m('.ComposerPage', [
        m('h2.App-titleControl', app.translator.trans('clarkwinkelmann-composer-page.forum.page' + (isPrivate ? '-private' : '') + '.heading')),
        this.body(),
      ])
    );
  }

  body() {
    const body = app.composer.body as any;

    if (!body.componentClass || this.loading) {
      return LoadingIndicator.component();
    }

    return body.componentClass.component({
      ...body.attrs,
      composer: app.composer,
      disabled: false,
      onTextEditorBuilt: () => {
        app.composer.resolveEditorReady();
        this.updateHeight();
      },
    });
  }

  oncreate(vnode: any) {
    super.oncreate(vnode);

    $(window).on('resize', (this.handlers.onresize = this.updateHeight.bind(this)));

    // We need to watch for changes to the content because it can change while editing:
    // - When adding many tags it can span multiple lines
    // - When adding or removing lines in Formulaire multi-entry fields
    // - When Formulaire validation errors appear during submitting
    this.handlers.observer = new ResizeObserver(this.updateHeight.bind(this));
    this.observeHeader();

    // We need a little delay before the first height update because the TextEditor doesn't initialize the textarea in the same rendering thread
    setTimeout(() => {
      this.updateHeight();
    }, 10);
  }

  onupdate(vnode: any) {
    super.onupdate(vnode);

    // The composer body might only be rendered after the page was created, while it loads
    this.observeHeader();
  }

  observeHeader() {
    const header = this.element.querySelector('.ComposerBody-header');

    if (header && header !== this.handlers.header) {
      this.handlers.header = header;
      this.handlers.observer.observe(header);
    }
  }

  onremove(vnode: any) {
    super.onremove(vnode);

    $(window).off('resize', this.handlers.onresize);

    this.handlers.observer.disconnect();

    // If we leave the page without quitting the composer, switch to the floating composer
    if (app.composer.bodyMatches('flarum/forum/components/DiscussionComposer')) {
      app.composer.minimize();
    }
  }

  updateHeight() {
    const $flexible = this.$('.Composer-flexible');

    // Adapted from Composer.prototype.updateHeight
    // The comparison is now done directly against the full document
    if ($flexible.length) {
      const headerHeight = $flexible.offset()!.top;
      const paddingBottom = parseInt($flexible.css('padding-bottom'), 10);
      const footerHeight = this.$('.Composer-footer').outerHeight(true)!;

      $flexible.height(Math.max(window.innerHeight - headerHeight - paddingBottom - footerHeight, 200));
    }
  }
}
