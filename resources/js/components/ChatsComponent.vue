<template>
   <div class="row">
        <div class="col-2">
            <div class="card card-default">
                <!-- List of Chat Rooms -->
                <div class="card-header">Chat rooms <span v-on:click="addingRoom = !addingRoom;">+++</span></div>

                <!-- Form for Adding Users -->
                <div 
                    v-if="addingRoom==true"
                    @keyup.enter="createRoom()">
                    <vue-multiselect
                        v-model="newRoomMembers"
                        :options="userDropdownOptions"
                        :multiple="true"
                        :block-keys="['Tab', 'Enter']"
                        :hide-selected="true"
                        select-label=""
                        deselect-label=""
                        placeholder="Type in a name..."
                        :close-on-select="false"
                        label="id"
                        :show-label="false"
                        track-by="id"
                        :custom-label="(user) => `${user.first_name} ${user.last_name}`"
                        @search-change="findUser"
                        :loading="isSearchLoading">
                        
                        <template v-slot:caret><span></span></template>
                    </vue-multiselect>
                </div>               
                <div class="card-body">
                    <!-- Chatroom Selection Component -->
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
                    <!-- Chat Messages Window -->
                    <div class="roomMessages" v-bind:id="'messages_room' + chatroom.room_id" v-if="chatroom.room_id==activeRoom">
                        <div class="card-header">
                            {{chatroom.room_name}} Messages 
                            <span v-on:click="addingMember=!addingMember">+member</span>
                        </div>

                        <!-- Add Member Input -->
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
                roomMsgs: [],
                /*roomMsgs replaces messages
                structure based on ChatsController:
                $roomMsgs[] = array('room_id'=> $result->room_id, 'room_name'=>$result->room_name, 'messages'=> $msgs);
                */                
                //messages: [],
                newMessage: '',
                users: [],
                activeUser: false,
                typingTimer: false,
                //chatroom variables
                chatrooms: [],
                activeRoom:'',
                currentUser: this.$props.user,
                
                // Create room vairables
                isSearchLoading: false,
                newRoom: '', // Stores input value of new room
                addingRoom: false,
                newRoomMembers: [], // Stores members to be added to new room
                userDropdownOptions: [], // List of available users who may be added to a room

                // Add chat member variables
                addingMember: false,      
                newMemberEmail: '',
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
                .listen('.chatroom.created', (event) => {
                    // Chatroom was created and user is added to it
                    
                    // Add the chatroom created to the user's list of chatrooms
                    this.chatrooms.unshift({
                        room_id: event.chatRoom.room_id,
                        room_name: event.chatRoom.room_name
                    });

                    this.roomMsgs.unshift({
                        room_id: event.chatRoom.room_id,
                        room_name: event.chatRoom.room_name,
                        messages: []
                    });
                })
                .listen('.message',(event) => {
                    //check which room the message goes
                    let found = this.getTargetRoomIndex(event.room_id);                                
                    if(event.room_name == '' && found == null && event.new_member != this.user.id){
                        //no one is being added and user doesn't have the room
                        //message is not for the user
                        return;
                    }else if(event.room_name != '' && found == null && event.new_member == this.user.id){
                        //user is being added to the room
                        this.chatrooms.unshift({
                            room_id: event.room_id,
                            room_name: event.room_name
                        });
                        this.roomMsgs.unshift({
                            room_id: event.room_id,
                            room_name: event.room_name,
                            messages:[]
                        });
                        //this.activeRoom = event.room_id;
                        found = this.getTargetRoomIndex(event.room_id);                        
                    }

                    if(found != null){
                        //put the new message received in the right room
                        this.roomMsgs[found].messages.push({
                            user: { name: event.username},
                            message: event.message
                        });
                        //move up the room with the new message
                        let found2 = null;
                        for(const indx in this.chatrooms){
                            if(this.chatrooms[indx].room_id==event.room_id){
                                found2 = indx;
                            }                     
                        }
                        let room = this.chatrooms[found2]
                        this.chatrooms.splice(found2, 1);
                        this.chatrooms.unshift(room);      
                    }
                 
                })              
                .listenForWhisper('typing', user => {
                   this.activeUser = user;

                    if(this.typingTimer) {
                        clearTimeout(this.typingTimer);
                    }

                   this.typingTimer = setTimeout(() => {
                       this.activeUser = false;
                   }, 3000);
                });
        },

        methods: {
            fetchMessages() {
                axios.get('messages').then(response => {
                    this.roomMsgs = response.data;
                });
            },
            scrollToChatBottom() {
                const chatWindow = document.getElementById('ChatWindow');
                chatWindow.scrollTop = chatWindow.scrollHeight;
            },
            sendMessage(){
                let found = this.getTargetRoomIndex(this.activeRoom);          
                this.roomMsgs[found].messages.push({
                    user: this.user,
                    message: this.newMessage
                });
                axios.post('messages', {
                    message: this.newMessage,
                    room_id: this.activeRoom
                }).then(response => {
                    if(response.data.status=='success' && this.chatrooms.length > 1){
                        let found = null;
                        for(const indx in this.chatrooms){
                            if(this.chatrooms[indx].room_id==this.activeRoom){
                                found = indx;
                            }                     
                        }
                        let room = this.chatrooms[found]
                        this.chatrooms.splice(found, 1);
                        this.chatrooms.unshift(room);
                    }
                });
                this.newMessage = '';
            },
            sendTypingEvent() {
                Echo.join('chat')
                    .whisper('typing', this.user);
            },
            handleAttachmentUpload(attachmentUrl) {
                let found = this.getTargetRoomIndex(this.activeRoom);
                this.roomMsgs[found].messages.push({
                    user: this.user,
                    message: '',
                    attachment_path:attachmentUrl
                });
            },
            fetchChatrooms(){
                axios.get('rooms').then(response =>{
                    if(response.data.length > 0){
                        this.chatrooms = response.data;
                        this.activeRoom = this.chatrooms[0].room_id;  
                    }
                });
            },            
            selectRoom: function(event){
                this.activeRoom = event.target.id;
            },

            // Function to query the database for a certain user
            findUser(query) {
                if (query === '') {
                    // Only send request if there is a search query
                    this.userDropdownOptions = [];
                    return;
                }

                // Show the loading bar
                this.isSearchLoading = true;
                axios.get('/users', {
                    params: {
                        name: query
                    }
                })
                .then(response => {
                    // Set the result of the database query as the options
                    this.userDropdownOptions = response.data;
                })
                .catch(error => {
                    // Unexpected error occurred, for now log to console
                    console.error(error);
                })
                .finally(() => {
                    this.isSearchLoading = false;
                });
            },

            createRoom(){
                // Generate a room name
                const roomName = this.newRoomMembers
                    .concat(this.$props.user) // Include the current user
                    .map(user => `${user.first_name} ${user.last_name}`)
                    .join(', ');

                // Get the selected members
                const members = this.newRoomMembers.map(user => user.id);

                // Make a request to create a new room
                axios.post('/newRoom', {
                    room_name: roomName,
                    members: members
                }).then(response => {
                    // Upon successful addition, add the new chatroom to the list
                    this.chatrooms.unshift({
                        room_id: response.data.room_id,
                        room_name: response.data.room_name
                    });
                    this.activeRoom = response.data.room_id;
                    this.roomMsgs.unshift({
                        room_id: response.data.room_id,
                        room_name: response.data.room_name,
                        messages:[]
                    });      
                });

                // Reset state
                this.newRoomMembers = [];
                this.addingRoom = false;             
            },
            addMember(){
                axios.post('addMember', {
                    email: this.newMemberEmail,
                    room_id: this.activeRoom
                });
                let found = this.getTargetRoomIndex(this.activeRoom);
                this.roomMsgs[found].messages.push({
                user: '',
                message: this.newMemberEmail + ' has been added'
                });                
                this.addingMember = false;
                this.newMemberEmail='';
            },
            getTargetRoomIndex(targetRoom){
                let found = null;
                for(const indx in this.roomMsgs){
                    if(this.roomMsgs[indx].room_id==targetRoom){
                        found = indx;
                    }                       
                }
                return found;
            }
        }
    }
</script>
