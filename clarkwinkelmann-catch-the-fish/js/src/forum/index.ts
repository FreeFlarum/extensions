import app from 'flarum/forum/app';
import addFishBasket from './addFishBasket';
import addDropAreas from './addDropAreas';
import addNavLinks from './addNavLinks';
import addRoundAlert from './addRoundAlert';
import calendar from 'dayjs/plugin/calendar';

export {default as extend} from './extend';

// Import calendar plugin for Day.js
dayjs.extend(calendar)

app.initializers.add('clarkwinkelmann-catch-the-fish', () => {
    addFishBasket();
    addDropAreas();
    addNavLinks();
    addRoundAlert();
});
