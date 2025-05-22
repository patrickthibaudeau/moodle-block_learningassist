import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import {get_string as getString} from 'core/str';
import ajax from 'core/ajax';

/* eslint-disable no-console */
export const init = async () => {
    // Get element with id ai-policy-status
    const policyStatus = document.getElementById('ai-policy-status');
    // Show modal pop-up for poliucy acceptance if policyStatus is empty
    if (policyStatus.value === '0') {
        const modal = await ModalSaveCancel.create({
            title: getString('aipolicyacceptance', 'ai'),
            body: getString('userpolicy', 'ai'),
            large: true,
            buttons: {
                'save': getString('acceptai', 'ai'),
                'cancel': getString('declineaipolicy', 'ai'),
            },
            show: true,
        });

        modal.getRoot().on(ModalEvents.save, () => {
            // Get context id element
            const contextId = M.cfg.contextid;
            // Set policy status
            const setPolicyStatus = ajax.call([{
                methodname: 'core_ai_set_policy_status',
                args: {
                    contextid: contextId,
                },
            }]);

            // Refresh the page
            setPolicyStatus[0].done(() => {
                window.location.reload();
            }).fail((error) => {
                alert('Error setting policy status:', error);
            });
        });
    }
};