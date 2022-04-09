<template>
   <div class="row">
       <div class="col-8">
           <div class="card card-default">
               <div class="card-header">Messages</div>
               <div class="card-body p-0">
                   <ul id="ChatWindow" class="list-unstyled" style="height: 500px; overflow-y:scroll" v-chat-scroll>
                       <li class="p-2" v-for="(message, index) in messages" :key="index" >
                           <div>
                                <strong>{{ message.user.name }}</strong>
                                {{ message.message }}
                           </div>
                           <div v-if="message.attachment_path">
                               <!-- Attachment -->
                               <img class="img-thumbnail" :src="message.attachment_path" @load="scrollToChatBottom">
                           </div>
                       </li>
                   </ul>
               </div>

               <div class="row">
                   <div class="col-10">
                        <input
                            @keydown="sendTypingEvent"
                            @keyup.enter="sendMessage"
                            v-model="newMessage"
                            type="text"
                            name="message"
                            placeholder="Enter your message..."
                            class="form-control">
                    </div>
                    <div class="col-2">
                        <file-upload-component v-on:upload-success="handleAttachmentUpload"></file-upload-component>
                    </div>                   
               </div>

           </div>
            <span class="text-muted" v-if="activeUser" >{{ activeUser.name }} is typing...</span>
       </div>

        <div class="col-4">
            <div class="card card-default">
                <div class="card-header">Active Users</div>
                <div class="card-body">
                    <ul>
                        <li class="py-2" v-for="(user, index) in users" :key="index">
                            {{ user.name }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
   </div>
</template>

<style scoped>
    .img-thumbnail {
        max-width: 10rem;
    }
</style>

<script>
    import FileUploadComponent from './FileUploadComponent.vue';

    export default {
        props:['user'],

        components: {
            FileUploadComponent
        },

        data() {
            return {
                messages: [],
                newMessage: '',
                users:[],
                activeUser: false,
                typingTimer: false,
            }
        },

        created() {
            window.Echo.join('chat')
                .here(user => {
                    this.fetchMessages();
                    this.users = user;
                })
                .joining(user => {
                    this.users.push(user);
                })
                .leaving(user => {
                    this.users = this.users.filter(u => u.id != user.id);
                })
                .listen('.message',(event) => {
                    console.log(event);
                    this.messages.push({
                        user: {
                            name: event.username
                        },
                        message: event.message,
                        attachment_path: event.attachment_path
                    });
                })
                .listenForWhisper('typing', user => {
                   this.activeUser = user;

                    if(this.typingTimer) {
                        clearTimeout(this.typingTimer);
                    }

                   this.typingTimer = setTimeout(() => {
                       this.activeUser = false;
                   }, 3000);
                })

        },

        methods: {
            fetchMessages() {
                axios.get('messages').then(response => {
                    this.messages = response.data;
                })
            },

            scrollToChatBottom() {
                const chatWindow = document.getElementById('ChatWindow');
                chatWindow.scrollTop = chatWindow.scrollHeight;
            },

            sendMessage() {
                this.messages.push({
                    user: this.user,
                    message: this.newMessage
                });

                axios.post('messages', {message: this.newMessage});

                this.newMessage = '';
            },

            sendTypingEvent() {
                Echo.join('chat')
                    .whisper('typing', this.user);
            },

            handleAttachmentUpload(attachmentUrl) {
                this.messages.push({
                    user: this.user,
                    message: '',
                    attachment_path: attachmentUrl 
                });
            }
        }
    }
</script>
