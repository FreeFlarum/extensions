import app from 'flarum/admin/app';
import EmailFilterSettingsPage from './components/EmailFilterSettingsPage';

export { default as extend } from './extend';

app.initializers.add('nyu8-email-filter', () => {
  app.registry.for('nyu8-email-filter').registerPage(EmailFilterSettingsPage);
});
