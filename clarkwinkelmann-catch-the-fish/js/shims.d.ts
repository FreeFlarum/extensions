import Fish from './src/common/models/Fish';
import Round from './src/common/models/Round';

declare module 'flarum/common/models/User' {
    export default interface User {
        catchTheFishFishes(): Fish[] | false

        catchTheFishBasket(): Fish[] | false
    }
}

declare module 'flarum/common/models/Forum' {
    export default interface Forum {
        catchTheFishActiveRounds(): Round[] | false

        catchTheFishCanModerate(): boolean

        catchTheFishCanSeeRankingsPage(): boolean

        catchTheFishAnimateFlip(): boolean
    }
}

declare module 'flarum/forum/ForumApplication' {
    export default interface ForumApplication {
        draggedFishId?: string | null
    }
}
