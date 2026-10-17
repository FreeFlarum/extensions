import app from 'flarum/admin/app';
import ConfigureWithOAuthPage from 'ext:fof/oauth/admin/components/ConfigureWithOAuthPage';

app.initializers.add('xrh0905/oauth-microsoft', () => {
  app.registry.for('xrh0905-oauth-microsoft').registerPage(ConfigureWithOAuthPage);
});
