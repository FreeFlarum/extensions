import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Feedback from '@betahuhn/feedback-js';
import HeaderPrimary from 'flarum/forum/components/HeaderPrimary';
import extractText from 'flarum/common/utils/extractText';

app.initializers.add('justoverclock/flarum-ext-feedback', () => {
    extend(HeaderPrimary.prototype, 'oninit', function () {
        const apiUrl = app.forum.attribute('apiUrl') + '/feedback';
        const opts = app.forum.attribute('feedback');

        if (!opts) {
            console.warn('justoverclock/flarum-ext-feedback is not correctly configured!');
            return;
        }

        const options = {
            endpoint: apiUrl,
            events: true,
            emailField: opts.email, // show email input field, default: false
            btnTitle: extractText(app.translator.trans('justoverclock-feedback.forum.button-title')), // title of button
            title: app.forum.attribute('boxTitle') || "Flarum Feedback", // text at the top
            inputPlaceholder: extractText(app.translator.trans('justoverclock-feedback.forum.feedback-widget.placeholder')),
            emailPlaceholder: extractText(app.translator.trans('justoverclock-feedback.forum.feedback-widget.email-placeholder')),
            submitText: extractText(app.translator.trans('justoverclock-feedback.forum.feedback-widget.submit')), //'Submit', // text for submit button
            backText: extractText(app.translator.trans('justoverclock-feedback.forum.feedback-widget.back')), //'Back', // text for back button
            contactText: extractText(app.translator.trans('justoverclock-feedback.forum.feedback-widget.contact-text')), // text for other contact option
            typeMessage: extractText(app.translator.trans('justoverclock-feedback.forum.feedback-widget.type-message')), // message for selecting feedback type
            success: extractText(app.translator.trans('justoverclock-feedback.forum.feedback-widget.success')), // message displayed on successfull submission
            position: opts.position, // position of button left/right
            primary: app.forum.attribute('themePrimaryColor'), // primary color
            background: opts.backgroundColor, // background color
            color: opts.fontColor, // font color
            types: {
                general: {
                    text: app.forum.attribute('feedbackType1') || "Type 1",
                    icon: app.forum.attribute('feedbackType1-icon') || "⚠",
                },
                idea: {
                    text: app.forum.attribute('feedbackType2') || "Type 2",
                    icon: app.forum.attribute('feedbackType2-icon') || "💡",
                },
                bug: {
                    text: app.forum.attribute('feedbackType3') || "Type 3",
                    icon: app.forum.attribute('feedbackType3-icon') || "🐛",
                },
            },
        };
        new Feedback(options).renderButton();

        window.addEventListener('feedback-submit', (event) => {
            app.request({
                method: 'POST',
                url: apiUrl,
                body: event.detail,
            });
        });
    });
});
