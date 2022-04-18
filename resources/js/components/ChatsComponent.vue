<template>
   <div class="row">
        <div class="col-2">
            <div class="card card-default">
                <div class="card-header">Chat rooms <span v-on:click="addingRoom = !addingRoom;">+++</span></div>
                <div v-if="addingRoom==true">
                    <input
                        @keyup.enter="createRoom"
                        v-model="newRoom"
                        type="text"
                        name="roomName"
                        placeholder="Enter room name..."
                        class="form-control">                        
                </div>               
                <div class="card-body">
                    <ul v-if="chatrooms!=''">
                        <li class="py-2" v-for="(chatroom, index) in chatrooms" :key="index">
                            <span class="rooms" v-on:click="selectRoom" v-bind:id="chatroom.room_id" >{{chatroom.room_name}}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>       
        <div class="col-8">
            <div class="card card-default">
                <div v-for="(chatroom, index) in roomMsgs" :key="index" >
                    <div class="roomMessages" v-bind:id="'messages_room' + chatroom.room_id" v-if="chatroom.room_id==activeRoom">
                        <div class="card-header">
                            {{chatroom.room_name}} Messages 
                            <span v-on:click="addingMember=!addingMember">+member</span>
                        </div>
                        <div v-if="addingMember==true">
                            <input
                                @keyup.enter="addMember"
                                v-model="newMemberEmail"
                                type="text"
                                name="memberAdd"
                                placeholder="Enter email address..."
                                class="form-control">                    
                        </div>
                        <div class="card-body p-0">
                            <ul id="ChatWindow" class="list-unstyled" style="height: 500px; overflow-y:scroll" v-chat-scroll>
                                <li class="p-2" v-for="(message, index) in chatroom.messages" :key="index" >
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
                    </div>
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
                        <FileUploadComponent v-on:upload-success="handleAttachmentUpload"></FileUploadComponent>
                    </div>                   
                </div>

            </div>
                <span class="text-muted" v-if="activeUser" >{{ activeUser.name }} is typing...</span>
        </div>

        <div class="col-2">
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
        max-width: 15rem;
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
                roomMsgs:[],
                /*roomMsgs replaces messages
                structure based on ChatsController:
                $roomMsgs[] = array('room_id'=> $result->room_id, 'room_name'=>$result->room_name, 'messages'=> $msgs);
                */                
                //messages: [],
                newMessage: '',
                users:[],
                activeUser: false,
                typingTimer: false,
                //chatroom variables
                chatrooms:[],
                activeRoom:'',
                newRoom:'',
                addingRoom:false,
                addingMember:false,      
                newMemberEmail:'',
            }
        },

        created() {
            this.fetchChatrooms();
            Echo.join('chat')
                .here(user => {
                    this.users = user;
                    this.fetchMessages(); 
                })
                .joining(user => {
                    this.users.push(user);
                })
                .leaving(user => {
                    this.users = this.users.filter(u => u.id != user.id);
                })
                .listen('.message',(event) => {   
                    let found;
                    for(const indx in this.roomMsgs){
                        console.log(indx, this.roomMsgs[indx]);
                        for(const inx2 in this.roomMsgs[indx]){
                            console.log(inx2, this.roomMsgs[indx][inx2]);
                            if(inx2 == 'room_id' && this.roomMsgs[indx][inx2]==event.room_id){
                                found = indx;
                            }                       
                        }
                    }
                    this.roomMsgs[found].messages.push({
                        user: { name: event.username},
                        message: event.message
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
                    this.roomMsgs = response.data;
                });
                console.log('fetching messages');
            },

            scrollToChatBottom() {
                const chatWindow = document.getElementById('ChatWindow');
                chatWindow.scrollTop = chatWindow.scrollHeight;
            },

            sendMessage(){
                /* no chatroom version
                this.messages.push({
                    user: this.user,
                    message: this.newMessage
                });*/

                //chatroom version to push new message to array
                let found;
                for(const indx in this.roomMsgs){
                    for(const inx2 in this.roomMsgs[indx]){
                        if(inx2 == 'room_id' && this.roomMsgs[indx][inx2]==this.activeRoom){
                            found = indx;
                        }                       
                    }
                }                   
                this.roomMsgs[found].messages.push({
                    user: this.user,
                    message: this.newMessage
                });
                axios.post('messages', {
                    message: this.newMessage,
                    room_id: this.activeRoom
                });

                this.newMessage = '';
            },

            sendTypingEvent() {
                Echo.join('chat')
                    .whisper('typing', this.user);
            },

            handleAttachmentUpload(attachmentUrl) {
                /*
                this.messages.push({
                    user: this.user,
                    message: '',
                    attachment_path: attachmentUrl 
                });
                */
                let found;
                for(const indx in this.roomMsgs){
                    for(const inx2 in this.roomMsgs[indx]){
                        if(inx2 == 'room_id' && this.roomMsgs[indx][inx2]==event.room_id){
                            found = indx;
                        }                       
                    }
                }
            this.roomMsgs[found].messages.push({
                user: this.user,
                message: '',
                attachment_path:attachmentUrl
            });                 
            },
            //chatroom methods
            fetchChatrooms(){//runs only when page is loaded
                axios.get('rooms').then(response =>{
                    if(response.data.length > 0){
                        this.chatrooms = response.data;
                        this.activeRoom = this.chatrooms[0].room_id;  
                    }
                });
            },            
            selectRoom: function(event){
                this.activeRoom = event.target.id;
                //alert(this.activeRoom);
            },
            createRoom(){
                axios.post('newRoom', {
                    room_name: this.newRoom,
                }).then(response => {
                    this.chatrooms.unshift(response.data);
                    
                    this.roomMsgs.push({
                        room_id: response.data.id,
                        room_name: response.data.room_name,
                        messages:[]
                    });
                    //this.fetchMessages();
                    for(const indx in this.roomMsgs){
                        console.log(indx, this.roomMsgs[indx]);
                        for(const inx2 in this.roomMsgs[indx]){
                            console.log(inx2, this.roomMsgs[indx][inx2]);                    
                        }
                    }                    
                });
                console.log('after',this.roomMsgs);
                this.newRoom = '';
                this.addingRoom = false;             
            },
            addMember(){
                axios.post('addMember', {
                    email: this.newMemberEmail,
                    room_id: this.activeRoom
                }).then(response =>{
                    console.log(response);
                });
                this.addingMember = false;
                this.newMemberEmail='';
            }
        }
    }
</script>
