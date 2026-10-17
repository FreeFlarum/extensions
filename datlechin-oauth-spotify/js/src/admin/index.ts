import app from 'flarum/admin/app';
import ConfigureWithOAuthPage from 'ext:fof/oauth/admin/components/ConfigureWithOAuthPage';

app.initializers.add('datlechin/flarum-oauth-spotify', () => {
  app.registry.for('datlechin-oauth-spotify').registerPage(ConfigureWithOAuthPage);
});
