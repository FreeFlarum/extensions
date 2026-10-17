import app from 'flarum/admin/app';
import BadgeActionDriverSettings from './components/BadgeActionDriverSettings';

export { default as extend } from './extend';

app.initializers.add('v17development-user-badges', () => {
  // Auto moderation
  if (app.autoModeratorForms) {
    app.autoModeratorForms.action.give_badge = BadgeActionDriverSettings;
    app.autoModeratorForms.action.remove_badge = BadgeActionDriverSettings;

    app.autoModeratorForms.requirement.has_badge = BadgeActionDriverSettings;
  }
});
