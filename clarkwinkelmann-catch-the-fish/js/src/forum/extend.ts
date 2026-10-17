import Extend from 'flarum/common/extenders';
import Discussion from 'flarum/common/models/Discussion';
import Post from 'flarum/common/models/Post';
import User from 'flarum/common/models/User';
import Forum from 'flarum/common/models/Forum';
import commonExtend from '../common/extend';
import Fish from '../common/models/Fish';
import Round from '../common/models/Round';
import Rankings from './pages/Rankings';
import RoundList from './pages/RoundList';
import RoundSettings from './pages/RoundSettings';

export default [
    ...commonExtend,

    new Extend.Routes()
        .add('catchTheFishRankings', '/catch-the-fish', Rankings)
        .add('catchTheFishRounds', '/catch-the-fish/rounds', RoundList)
        .add('catchTheFishRound', '/catch-the-fish/rounds/:id', RoundSettings),

    new Extend.Model(Discussion)
        .hasMany<Fish>('catchTheFishFishes'),
    new Extend.Model(Post)
        .hasMany<Fish>('catchTheFishFishes'),
    new Extend.Model(User)
        .hasMany<Fish>('catchTheFishFishes')
        .hasMany<Fish>('catchTheFishBasket'),
    new Extend.Model(Forum)
        .hasMany<Round>('catchTheFishActiveRounds')
        .attribute<boolean>('catchTheFishCanModerate')
        .attribute<boolean>('catchTheFishCanSeeRankingsPage')
        .attribute<boolean>('catchTheFishAnimateFlip'),
];
