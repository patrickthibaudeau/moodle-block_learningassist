import {get_string as getString} from 'core/str';
// import Templates from 'core/templates';
import ajax from 'core/ajax';

export const sendMessage = async () => {
  const input = document.getElementById('block-learningassist-chat-input');
  const prompt = input.value;
  if (prompt) {
    const chatMessages = document.getElementById('chat-messages');
    const loadingText = await getString('loading', 'block_learningassist');

    const html = `<div class="chat-message human-message">
                        <div class="message-content">${prompt}</div>
                     </div>
                    <div id="block-learningassist-delete-me" class="chat-message bot-message">
                        <div class="message-content">
                        <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                        <span class="sr-only">${loadingText}</span></div>
                     </div>`;
    // Scroll down to the top of new message
    chatMessages.innerHTML += html;
    chatMessages.scrollTop = chatMessages.scrollHeight;
    input.value = '';

    const courseId = document.getElementById('block-learningassist-courseid').value;
    const chatType = document.getElementById('block-learningassist-chat-type').value;
    const chatId = document.getElementById('block-learningassist-chatid').value;

    if (prompt) {
      const response = await ajax.call([{
        methodname: 'block_learningassist_chat',
        args: {
          courseid: courseId,
          chattype: chatType,
          prompt: prompt,
          chatid: chatId
        },
      }]);
      response[0].done((result) => {
        const deleteMe = document.getElementById('block-learningassist-delete-me');
        if (deleteMe) {
          deleteMe.remove();
        }
        const bot_html = `<div class="chat-message bot-message">
                            <div class="message-content">${result}</div>
                         </div>`;
        chatMessages.innerHTML += bot_html;
        input.focus();
      }).fail((error) => {
        console.log(error)
      });
    }
  }
};


document.getElementById('block-learningassist-chat-send-btn').addEventListener('click', sendMessage);

document.getElementById('block-learningassist-chat-input').addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    sendMessage();
  }
});
