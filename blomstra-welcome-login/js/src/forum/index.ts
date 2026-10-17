import app from 'flarum/forum/app';
import addLoginAndSignup from './addLoginAndSignup';

app.initializers.add('blomstra/welcome-login', () => {
  addLoginAndSignup();
});
