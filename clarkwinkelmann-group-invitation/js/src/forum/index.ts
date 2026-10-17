import app from 'flarum/forum/app';
import Invitation from '../common/Invitation';
import GetRolePage from './components/GetRolePage';

app.initializers.add('clarkwinkelmann-group-invitation', () => {
  app.store.models['group-invitations'] = Invitation;

  app.routes.clarkwinkelmannGroupInvitation = {
    path: '/get-role/:code',
    component: GetRolePage,
  };

  // The alert manager renders this once the application is mounted after the initializers ran
  if (localStorage.getItem('groupInvitationSuccess')) {
    localStorage.removeItem('groupInvitationSuccess');

    app.alerts.show(
      {
        type: 'success',
      },
      app.translator.trans('clarkwinkelmann-group-invitation.forum.success-alert')
    );
  }
});
