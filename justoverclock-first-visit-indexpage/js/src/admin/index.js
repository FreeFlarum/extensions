import app from 'flarum/admin/app';

app.initializers.add('justoverclock/first-visit-indexpage', () => {
  console.log('[justoverclock/first-visit-indexpage] Hello, admin!');
});
