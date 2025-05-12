import notification from 'core/notification';
import ajax from 'core/ajax';
import * as Str from 'core/str';

export const init = () => {
    display_modules();
};

/**
 * Display course modules
 */
function display_modules() {
    // Find all buttons with the new class
    var buttons = document.querySelectorAll('.block-learningassist-course-module-btn');
    if (!buttons.length) {
        return;
    }
    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            // get data-courseid from current element
            var courseid = this.getAttribute('data-courseid');
            var chatType = this.getAttribute('data-chattype');

            var display_modules = ajax.call([{
                methodname: 'block_learningassist_display_course_modules',
                args: {
                    'courseid': courseid,
                    'chattype': chatType
                }
            }]);

            display_modules[0].done(function (results) {
                notification.confirm(Str.get_string('select_study_subject', 'block_learningassist'),
                    results,
                    Str.get_string('save', 'block_learningassist'),
                    Str.get_string('cancel', 'block_learningassist'), function () {

                    });

                setTimeout(function () {


                    var blocks = document.querySelectorAll('.blockLearningAssist');
                    blocks.forEach(function (block) {
                        block.addEventListener('click', function () {
                            var dataId = this.getAttribute('data-id');
                            var contentClassName = 'blockLearningAssistContent-' + dataId;
                            var contentBlock = document.querySelector('.' + contentClassName);
                            var folderIcon = this.querySelector('.blockLearningAssistFolderIcon');
                            if (contentBlock.style.display === 'none' || contentBlock.style.display === '') {
                                contentBlock.style.display = 'block';
                                folderIcon.classList.remove('fa-folder');
                                folderIcon.classList.add('fa-folder-open');
                            } else {
                                contentBlock.style.display = 'none';
                                folderIcon.classList.remove('fa-folder-open');
                                folderIcon.classList.add('fa-folder');
                            }
                        });
                    });

                }, 1000);

            }).fail(function () {
                alert('An error has occurred. Cannot display data');
            });

        });
    });
}


