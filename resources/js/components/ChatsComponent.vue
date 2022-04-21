<template>
    
    <div class="row">
        <div class= "col-4">

            <div class ="header" data-toggle="collapse">
                <div class = "userimg">
                    <img src = "https://via.placeholder.com/150" class="cover">
                
                </div>
                
                <h4 class="userName">{{ user.name }}<br> <span>{{user.email}}</span></h4>
                
                <ul class = "nav_icons">
                    <li><ion-icon name="chatbubble-ellipses-outline" onclick="toggleheaderleft()" id="headerToggle"></ion-icon></li>
                    <li><SampleProfileEditForm></SampleProfileEditForm></li>
                </ul>
            </div>

            <!-- search -->

            <div class = "search_chat">
                <div>
                    <input type="text" placeholder="Search or start a new chat"> 
                </div>
            </div>

            <!-- chatlist -->

            <div class="chatlist" style="overflow-y:scroll">
        
                <div class="block active">
                    <div class="details">
                        <div class="listHead">
                            <h4>Ali Seanard</h4>
                            <p class="time">10:23</p>
                        </div>
                    
                    
                        <div class="message_p">
                            <p>The quick brown fox jumps over the lazy dog. The quick brown fox jumps over the lazy dog</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- FROM ORIG FILE/ DON'T WANT TO MESS WITH THIS MUNA SORRY

            
            <div class="card card-default">
                <div class="card-header">Active Users</div>
                    <div class="card-body">
                        <ul>
                            <li class="py-2" v-for="(user, index) in users" :key="index">
                                {{ user.name }}
                            </li>
                        </ul>
                    </div>
                
            </div>-->
        </div>

       <div class="col-8">
            <div class="rightSide">

                <div class = "header" id="orig" style="display:flex">
                    <div class="imgText">
                        <div class = "userimg">
                            <img src = "https://via.placeholder.com/150" class="cover">
                        </div>
                        
                        <h4>Ali Seanard <br> <span>Online</span></h4>
                    </div>
                </div>

                <!-- TOGGLE DISPLAY when ADD to Chat is Clicked -->
                
                <div class = "header" id="toBar" style="display:none">
                    <div class="imgTextLabel">
                        
                        <label class="toLabel">To:</label> 
                            <input
                            type="text"
                            name="contact"
                            placeholder="Enter contact name or email..."
                            />

                    </div>
                </div>
            </div>  

            <div class="card card-default">
                
                    <div class="card-body chatboxfix p-0">

                            <ul ref="chatWindow" class="list-unstyled" style="height:560px; overflow-y:scroll" v-chat-scroll>

                                <li class="p-2" v-for="(message, index) in messages" :key="index" >
                                    <div class="message my_message">
                                        <span class="p" style="text-align: left;"><strong> {{ message.user.name }} : </strong>
                                        {{ message.message }}</span>
                                    </div>
                                
                                    <div v-if="message.attachment_path">
                                        <!-- Attachment -->
                                        <img class="img-thumbnail" :src="message.attachment_path" @load="scrollToChatBottom">
                                    </div>

                                </li>
                                
                                
                                
                            </ul>
                            <span class="text-muted" v-if="activeUser" >{{ activeUser.name }} is typing...</span>
                        
                    </div>
                    
                    

                    <!--chat input-->
                    <div class="chatbox_input">
                        <FileUploadComponent v-on:upload-success="handleAttachmentUpload"></FileUploadComponent>
                        
                        <input
                            @keydown="sendTypingEvent"
                            @keyup.enter="sendMessage"
                            v-model="newMessage"
                            type="text"
                            name="message"
                            placeholder="Enter your message..."
                            class="form-control">

                    </div>
                   

                </div>
                
           </div>


   </div>

</template>

<style scoped>
    .img-thumbnail {
        max-width: 15rem;
    }
</style>



<script>
    import FileUploadComponent from './FileUploadComponent.vue';
    import SampleProfileEditForm from './SampleProfileEditForm.vue';

    export default {
        props:['user'],

        components: {
            FileUploadComponent,
            SampleProfileEditForm
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
                const chatWindow = this.$refs.chatWindow;
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

