import app from 'flarum/admin/app';
import ExtensionPage from './components/ExtensionPage';

app.initializers.add('migratetoflarum-canonical', (app) => {
  app.registry.for('migratetoflarum-canonical').registerPage(ExtensionPage);
});
