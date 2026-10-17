import app from 'flarum/forum/app';
import Page from 'flarum/common/components/Page';
import PageStructure from 'flarum/forum/components/PageStructure';
import WelcomeHero from 'flarum/forum/components/WelcomeHero';
import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import LinkButton from 'flarum/common/components/LinkButton';
import Icon from 'flarum/common/components/Icon';
import BadgeUserList from './BadgeUserList';

export default class BadgeItemPage extends Page {
  oninit(vnode) {
    super.oninit(vnode);

    this.bodyClass = 'App--index';

    const found = app.store.getById('badges', m.route.param('id'));

    this.loading = !found;

    if (!found) {
      // Load badge
      app.store.find('badges', m.route.param('id')).then((badge) => {
        this.loading = false;

        app.history.push('badgeItemPage', badge.name());

        this.setTitle(badge);

        m.redraw();
      });
    } else {
      app.history.push('badgeItemPage', found.name());

      this.setTitle(found);
    }
  }

  setTitle(badge) {
    app.setTitle(`${badge.name()} - ${app.translator.trans('v17development-flarum-badges.forum.badge.badges', {}, true)}`);
  }

  view() {
    const badge = app.store.getById('badges', m.route.param('id'));

    return (
      <PageStructure className="IndexPage BadgeItemPage" hero={() => <WelcomeHero />} sidebar={() => <IndexSidebar />}>
        <LinkButton href={app.route('badges')} icon={'fas fa-chevron-left'} className={'Button BadgesOverviewButton'}>
          {app.translator.trans('v17development-flarum-badges.forum.badge.badges')}
        </LinkButton>

        {this.loading && <LoadingIndicator size={'large'} />}

        {!this.loading && (
          <div className={'BadgeUserListInfo'}>
            {badge.icon() && <Icon name={badge.icon()} />}

            <div className={'BadgeUserListInfo-meta'}>
              <h3>{badge.name()}</h3>
              <p>{badge.description()}</p>
            </div>
          </div>
        )}

        {badge && (
          <h3>
            {app.translator.trans('v17development-flarum-badges.forum.badge.earned_by_count', {
              count: badge.earnedAmount(),
              b: <b />,
            })}
          </h3>
        )}

        {!this.loading && app.forum.attribute('canViewDetailedBadgeUsers') && <BadgeUserList state={app.userBadgeListState} badgeId={badge.id()} />}
      </PageStructure>
    );
  }
}
