import app from 'flarum/forum/app';
import Component, { ComponentAttrs } from 'flarum/common/Component';
import Link from 'flarum/common/components/Link';
import Avatar from 'flarum/common/components/Avatar';
import humanTime from 'flarum/common/helpers/humanTime';
import Icon from 'flarum/common/components/Icon';
import username from 'flarum/common/helpers/username';
import Reward from '../models/Reward';
import FormattedMoney from './FormattedMoney';

interface RewardRecordAttrs extends ComponentAttrs {
  reward: Reward;
  showReceiver?: boolean;
}

export default class RewardRecord extends Component<RewardRecordAttrs> {
  view() {
    const { reward, showReceiver } = this.attrs;

    const giver = reward.giver() || null;
    const receiver = reward.receiver() || null;
    const post = reward.post();

    const giverContent = [
      Avatar.component({
        user: giver,
      }),
      username(giver),
    ];

    const receiverContent = [
      Avatar.component({
        user: receiver,
      }),
      username(receiver),
    ];

    const comment = reward.comment();

    return m('li.MoneyRewardRecord', [
      m(
        'span.MoneyRewardRecordIcon',
        Icon.component({
          name: 'fas fa-gift',
        })
      ),
      m('span.MoneyRewardRecordDate', humanTime(reward.createdAt()!)),
      m(
        'span.MoneyRewardRecordAmount',
        FormattedMoney.component({
          money: reward.amount(),
        })
      ),
      m('span.MoneyRewardRecordGiver', [
        ' ',
        app.translator.trans('clarkwinkelmann-money-rewards.forum.record.from'),
        ' ',
        giver
          ? Link.component(
              {
                className: 'MoneyRewardRecordUser',
                href: app.route.user(giver),
              },
              giverContent
            )
          : m('span.MoneyRewardRecordUser', giverContent),
      ]),
      showReceiver
        ? m('span.MoneyRewardRecordReceiver', [
            ' ',
            app.translator.trans('clarkwinkelmann-money-rewards.forum.record.to'),
            ' ',
            receiver
              ? Link.component(
                  {
                    className: 'MoneyRewardRecordUser',
                    href: app.route.user(receiver),
                  },
                  receiverContent
                )
              : m('span.MoneyRewardRecordUser', receiverContent),
          ])
        : null,
      showReceiver && post
        ? m('span.MoneyRewardRecordPost', [
            ' ',
            app.translator.trans('clarkwinkelmann-money-rewards.forum.record.post', {
              number: post.number(),
              title: post.discussion()?.title() || 'N/A',
              a: Link.component({
                href: app.route.post(post),
              }),
            }),
          ])
        : null,
      comment
        ? m('.MoneyRewardRecordComment', [
            m('span.MoneyRewardRecordCommentLabel', app.translator.trans('clarkwinkelmann-money-rewards.forum.record.comment')),
            ' ',
            m('span.MoneyRewardRecordCommentContent', reward.comment()),
          ])
        : null,
    ]);
  }
}
