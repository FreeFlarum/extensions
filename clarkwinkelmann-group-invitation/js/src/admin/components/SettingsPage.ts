import { Vnode } from 'mithril';
import app from 'flarum/admin/app';
import Button from 'flarum/common/components/Button';
import GroupBadge from 'flarum/common/components/GroupBadge';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Group from 'flarum/common/models/Group';
import GroupSelect from './GroupSelect';
import Invitation from '../../common/Invitation';

const translationPrefix = 'clarkwinkelmann-group-invitation.admin.settings.';

export default class SettingsPage extends ExtensionPage {
  invitations: Invitation[] | null = null;
  newInvitationGroupId: string = '';
  newInvitationMaxUsage: string = '';

  oninit(vnode: Vnode) {
    super.oninit(vnode);

    app.store.find<Invitation[]>('group-invitations', {}).then((invitations) => {
      this.invitations = invitations;
      m.redraw();
    });

    this.loading = false;
  }

  content() {
    return m(
      '.ExtensionPage-settings.GroupInvitationSettingsPage',
      m('.container', [
        m('table', [
          m(
            'thead',
            m('tr', [
              m('th', app.translator.trans(translationPrefix + 'head.code')),
              m('th', app.translator.trans(translationPrefix + 'head.group')),
              m('th', app.translator.trans(translationPrefix + 'head.usage')),
              m('th'),
            ])
          ),
          m('tbody', [
            this.invitations === null
              ? m('tr', m('td', LoadingIndicator.component()))
              : [
                  this.invitations.map((invitation, index) => {
                    const group = invitation.group();

                    return m('tr', [
                      m(
                        'td',
                        m(
                          'a',
                          {
                            href: app.forum.attribute('baseUrl') + '/get-role/' + invitation.code(),
                            target: '_blank',
                          },
                          m('code', invitation.code())
                        )
                      ),
                      m('td', [
                        GroupBadge.component({
                          group,
                        }),
                        ' ',
                        group && group.namePlural(),
                      ]),
                      m(
                        'td',
                        invitation.usageCount() +
                          '/' +
                          (invitation.maxUsage() === null ? app.translator.trans(translationPrefix + 'unlimited') : invitation.maxUsage())
                      ),
                      m(
                        'td',
                        Button.component(
                          {
                            className: 'Button Button--danger',
                            onclick: () => {
                              this.loading = true;

                              invitation
                                .delete()
                                .then(() => {
                                  this.loading = false;

                                  this.invitations!.splice(index, 1);
                                  m.redraw();
                                })
                                .catch((e) => {
                                  this.loading = false;
                                  m.redraw();
                                  throw e;
                                });
                            },
                            loading: this.loading,
                          },
                          app.translator.trans(translationPrefix + 'delete')
                        )
                      ),
                    ]);
                  }),
                  this.invitations.length === 0
                    ? m(
                        'tr.NoResultRow',
                        m(
                          'td',
                          {
                            colspan: 4,
                          },
                          m('em', app.translator.trans(translationPrefix + 'empty'))
                        )
                      )
                    : null,
                ],
            m('tr', [
              m('td', m('em', app.translator.trans(translationPrefix + 'placeholder.code'))),
              m(
                'td',
                m(GroupSelect, {
                  onchange: (value: string) => {
                    this.newInvitationGroupId = value;
                  },
                  value: this.newInvitationGroupId,
                })
              ),
              m(
                'td',
                m('input.FormControl', {
                  type: 'number',
                  min: '0',
                  onchange: (event: Event) => {
                    const value = (event.target as HTMLInputElement).value;
                    this.newInvitationMaxUsage = value === '0' ? '' : value;
                  },
                  value: this.newInvitationMaxUsage,
                  placeholder: app.translator.trans(translationPrefix + 'placeholder.usage'),
                })
              ),
              m(
                'td',
                Button.component(
                  {
                    className: 'Button Button--primary',
                    onclick: () => {
                      this.loading = true;

                      app.store
                        .createRecord<Invitation>('group-invitations')
                        .save({
                          maxUsage: this.newInvitationMaxUsage ? parseInt(this.newInvitationMaxUsage) : null,
                          relationships: {
                            group: app.store.getById<Group>('groups', this.newInvitationGroupId)!,
                          },
                        })
                        .then((invitation) => {
                          this.loading = false;

                          this.invitations!.push(invitation);
                          this.newInvitationGroupId = '';
                          this.newInvitationMaxUsage = '';
                          m.redraw();
                        })
                        .catch((e) => {
                          this.loading = false;
                          m.redraw();
                          throw e;
                        });
                    },
                    loading: this.loading,
                    disabled: !this.newInvitationGroupId,
                  },
                  app.translator.trans(translationPrefix + 'create')
                )
              ),
            ]),
          ]),
        ]),
      ])
    );
  }
}
