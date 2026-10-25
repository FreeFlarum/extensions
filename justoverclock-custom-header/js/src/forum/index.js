/*
 * This file is part of justoverclock/custom-header.
 *
 * Copyright (c) 2021 Marco Colia.
 * https://flarum.it
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import extractText from 'flarum/common/utils/extractText';
import IndexPage from 'flarum/forum/components/IndexPage';
import WelcomeHero from 'flarum/forum/components/WelcomeHero';

function socialLink(attribute, icon, title) {
  const url = app.forum.attribute(attribute);

  if (!url) return null;

  return m('a', { href: url, title: extractText(app.translator.trans(title)) }, m('i', { className: 'socialic ' + icon }));
}

function customHeader() {
  const user = app.session.user;
  const bgImageUrl =
    app.forum.attribute('justoverclock-custom-header.headerBackgroundImage') ||
    app.forum.attribute('baseUrl') + '/assets/extensions/justoverclock-custom-header/bg.jpg';

  return m(
    'div',
    { className: 'StreamsHero-image' },
    m('div', { className: 'StreamsHero-content', id: 'StreamsHero-content', style: { backgroundImage: "url('" + bgImageUrl + "')" } }, [
      m('div', { className: 'StreamsHero-buttonContainer' }, [
        m('a', { className: 'js-nav', 'data-element': 'logo', target: '_blank' }, [
          socialLink('twitterIcon', 'fab fa-twitter', 'custom-header.forum.twitter'),
          socialLink('facebookIcon', 'fab fa-facebook-square', 'custom-header.forum.facebook'),
          socialLink('youtubeIcon', 'fab fa-youtube', 'custom-header.forum.youtube'),
          socialLink('githubIcon', 'fab fa-github-square', 'custom-header.forum.github'),
        ]),
        !user && app.forum.attribute('allowSignUp')
          ? m(
              'a',
              {
                className: 'Button StreamsSignUp js-signup',
                onclick: () => app.modal.show(() => import('flarum/forum/components/SignUpModal')),
                'data-component': 'hero',
                'data-element': 'buttons',
              },
              app.translator.trans('custom-header.forum.signup')
            )
          : null,
        !user
          ? m(
              'a',
              {
                className: 'Button StreamsLogin js-login',
                onclick: () => app.modal.show(() => import('flarum/forum/components/LogInModal')),
              },
              app.translator.trans('custom-header.forum.login')
            )
          : null,
      ]),
      m('h2', { className: 'StreamsHero-header' }, app.forum.attribute('headerTitle')),
      m('p', { className: 'StreamsHero-blurb' }, app.forum.attribute('headerTagline')),
      m(
        'a',
        { href: app.forum.attribute('LinkButtonOne'), className: 'headerButtons' },
        m('span', { className: 'buttontext' }),
        app.forum.attribute('buttonText') || 'Button 1'
      ),
      m(
        'a',
        { href: app.forum.attribute('LinkButtonTwo'), className: 'headerButtons' },
        m('span', { className: 'buttontext' }),
        app.forum.attribute('button2Text') || 'Button 2'
      ),
    ])
  );
}

app.initializers.add('justoverclock/custom-header', () => {
  // The header goes above the index page's hero, which it replaces on the
  // home page (the welcome hero is dropped, other heroes such as a tag's stay).
  extend(IndexPage.prototype, 'view', function (vdom) {
    const hero = vdom.attrs.hero;

    vdom.attrs.hero = () => {
      const original = hero ? hero() : null;

      return [customHeader(), original && original.tag === WelcomeHero ? null : original];
    };
  });
});
