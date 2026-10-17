import Extend from 'flarum/common/extenders';
import Fish from './models/Fish';
import Ranking from './models/Ranking';
import Round from './models/Round';

export default [
    new Extend.Store()
        .add('catchthefish-fishes', Fish)
        .add('catchthefish-rankings', Ranking)
        .add('catchthefish-rounds', Round),
];
