<template>
  <div class="row">
    <div class="col-4">
      <div class="header" data-toggle="collapse">
        <div class="userimg">
          <img :src="user.profile_picture || 'http://placekitten.com/150/150'" class="cover" />
        </div>

        <h4>
          {{ user.first_name }} {{ user.last_name }}<br />
          <span>{{ user.email }}</span>
        </h4>

        <ul class="nav_icons">
          <li>
            <ion-icon
              name="chatbubble-ellipses-outline"
              @click="addingRoom = !addingRoom"
              id="headerToggle"
            ></ion-icon>
          </li>
          <li><ProfileUpdateComponent></ProfileUpdateComponent></li>
        </ul>
      </div>

      <!-- search -->

      <div class="search_chat">
        <div @keyup.enter="createRoom()">
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
            :loading="isSearchLoading"
          >
            <template v-slot:caret><span></span></template>
          </vue-multiselect>
        </div>
      </div>

    <!-- chatlist -->
    <div class="chatlist" style="overflow-y: scroll">
      <div
        :class="{ block: true, active: chatroom.room_id == activeRoom }"
        v-for="chatroom in chatrooms"
        :key="chatroom.room_id"
      >
        <div
          class="details"
          v-on:click="selectRoom(chatroom.room_id)"
          v-bind:id="chatroom.room_id"
        >
          <div class="listHead">
            <h4>{{ chatroom.room_name }}</h4>
          </div>

          <!-- The message last sent to the room -->
          <div class="message_p">
            <p v-if="roomMsgs.find(room => room.room_id == chatroom.room_id).messages.length > 0">
              {{
                // Get the last message and convert to string
                convertMessageObjectToString(
                  roomMsgs.find(room => room.room_id === chatroom.room_id).messages.slice(-1)[0]
                )
              }}
            </p>
            <b>1</b>
          </div>
        </div>
      </div>
    </div>
    </div>

    <div class="col-8">
        <div class="rightSide">
          <div class="header" id="orig" :style="{ display: !addingRoom ? 'flex' : 'none' }">
            <div class="imgText">
              <!-- TODO: Default Room Photo -->
              <!-- <div class="userimg">
                <img src="https://via.placeholder.com/150" class="cover" />
              </div> -->
              <!-- TODO: Consider showing photo if chatroom is 1-on-1 -->

              <h4 v-if="activeRoomDetails">
                {{ activeRoomDetails.room_name }}

              </h4>
            </div>
          </div>

          <!-- TOGGLE DISPLAY when ADD to Chat is Clicked -->

          <div class="header" id="toBar" :style="{ display: addingRoom ? 'flex' : 'none' }">
            <div class="imgTextLabel" @keyup.enter="createRoom()">
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
                :loading="isSearchLoading"
              >
                <template v-slot:caret><span></span></template>
              </vue-multiselect>
            </div>
          </div>
        </div>

        <div class="card card-default">
          <!-- Placeholder chat box (if there are no rooms yet) -->
         <div v-if="roomMsgs.length === 0" class="card-body chatboxfix p-4" >
           <div class="d-flex justify-content-center align-items-center h-100">
                <p class="fs-3 text-muted" v-if="!loadingChatrooms">Click on the bubble icon <ion-icon name="chatbubble-ellipses-outline"></ion-icon> above to create a new chatroom!</p>
              </div>
         </div>

          <div v-for="chatroom in roomMsgs" :key="chatroom.room_id">
            <!-- Chat messages and 'is typing...' -->
            <div class="card-body chatboxfix p-0 roomMessages" :style="{'background-image':'url(background_trans.png)'}" 
              v-bind:id="'messages_room' + chatroom.room_id" 
              v-if="chatroom.room_id == activeRoom">
              <ul
                v-if="!addingRoom"
                ref="chatWindow"
                class="list-unstyled"
                style="height: 560px; overflow-y: scroll; overflow-x: hidden; padding: 0 1.5rem"
                v-chat-scroll
              >
                <!-- Chatroom Messages -->
                <li
                  class="p-2"
                  v-for="(message, index) in chatroom.messages"
                  :key="index"
                >
                  <!-- TODO: Differentiate messages you sent -->
                  <div :class="{
                    message: true, 
                    my_message: message.user.id === user.id,
                    friend_message: message.user.id !== user.id
                  }">
                    <span class="p">
                      <strong v-if="message.user.id !== user.id"> {{ message.user.first_name }} {{ message.user.last_name }} : </strong>
                      {{ message.message }} 
                    </span>
                    <div v-if="message.attachment_path" :class="{
                    message: true, 
                    my_message: message.user.id === user.id,
                    friend_message: message.user.id !== user.id
                  }">
                    <!-- Attachment -->
                    <img
                      class="img-thumbnail p"
                      :src="message.attachment_path"
                      @load="scrollToChatBottom"
                    />
                  </div>
                  </div>
                  
                </li>
              </ul>

              <!-- Message shown while adding a room via To Bar: -->
              <div class="d-flex justify-content-center align-items-center h-100" v-else>
                <p class="fs-3 text-muted">Add chat members above, and then press <strong>enter</strong> to create a new chatroom!</p>
              </div>

              <span class="text-muted" v-if="activeUser"
                >{{ activeUser.name }} is typing...</span
              >
            </div>
          </div>

          <!--chat input-->
          <div class="chatbox_input">
            <FileUploadComponent
            :active-room="activeRoom"
              v-on:upload-success="handleAttachmentUpload"
            ></FileUploadComponent>

            <input
              @keydown="sendTypingEvent"
              @keyup.enter="sendMessage"
              v-model="newMessage"
              type="text"
              name="message"
              placeholder="Enter your message..."
              class="form-control"
            />
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
    import ProfileUpdateComponent from './ProfileUpdateComponent.vue';

export default {
  props: ["user"],

        components: {
            FileUploadComponent,
            ProfileUpdateComponent
        },

  data() {
    return {
      roomMsgs: [],
      /*roomMsgs replaces messages
                structure based on ChatsController:
                $roomMsgs[] = array('room_id'=> $result->room_id, 'room_name'=>$result->room_name, 'messages'=> $msgs);
                */
      //messages: [],
      newMessage: "",
      users: [],
      activeUser: false,
      typingTimer: false,
      //chatroom variables
      chatrooms: [],
      activeRoom: "",

      // Create room vairables
      isSearchLoading: false,
      addingRoom: false,
      newRoomMembers: [], // Stores members to be added to new room
      userDropdownOptions: [], // List of available users who may be added to a room
      loadingChatrooms: true,

      // Add chat member variables
      isMemberSearchLoading: false,
      addingMember: false,
      newMemberDropdownOptions: [],
    };
  },

  computed: {
    activeRoomDetails: function() {
      const activeRoomResult = this.roomMsgs.filter(room => room.room_id == this.activeRoom);

      if (activeRoomResult.length === 0) {
        return null;
      }
      else {
        return activeRoomResult[0];
      }
    }
  },

  created() {
    this.fetchChatrooms();
    Echo.join("chat")
      .here((user) => {
        this.users = user;
        this.fetchMessages();
      })
      .joining((user) => {
        this.users.push(user);
      })
      .leaving((user) => {
        this.users = this.users.filter((u) => u.id != user.id);
      })
      .listen(".chatroom.created", (event) => {
        // Add the chatroom created to the user's list of chatrooms

        // Only if they're a member of the chatroom
        if (
          event.chatRoom.members.some(
            (member) => member.id === this.$props.user.id
          )
        ) {
          this.chatrooms.unshift({
            room_id: event.chatRoom.room_id,
            room_name: event.chatRoom.room_name,
          });

          this.roomMsgs.unshift({
            room_id: event.chatRoom.room_id,
            room_name: event.chatRoom.room_name,
            messages: [],
          });
        }
      })
      .listen(".message", (event) => {
        //check which room the message goes
        let found = this.getTargetRoomIndex(event.room_id);
        if (
          event.room_name == "" &&
          found == null &&
          event.new_member != this.user.id
        ) {
          //no one is being added and user doesn't have the room
          //message is not for the user
          return;
        } else if (
          event.room_name != "" &&
          found == null &&
          event.new_member == this.user.id
        ) {
          //user is being added to the room
          this.chatrooms.unshift({
            room_id: event.room_id,
            room_name: event.room_name,
          });
          this.roomMsgs.unshift({
            room_id: event.room_id,
            room_name: event.room_name,
            messages: [],
          });
          //this.activeRoom = event.room_id;
          found = this.getTargetRoomIndex(event.room_id);
        }

        if (found != null) {
          //put the new message received in the right room
          let newMessage = {
            user: event.user,
            message: event.message,
          };
          console.log("Event");
          console.log(event);
          console.log("New Message");
          console.log(newMessage);

          // Check if incoming has an attachment
          if (event.attachment_path) {
            newMessage.attachment_path = event.attachment_path;
          }

          this.roomMsgs[found].messages.push(newMessage);
          //move up the room with the new message
          let found2 = null;
          for (const indx in this.chatrooms) {
            if (this.chatrooms[indx].room_id == event.room_id) {
              found2 = indx;
            }
          }
          let room = this.chatrooms[found2];
          this.chatrooms.splice(found2, 1);
          this.chatrooms.unshift(room);
        }
      })
      .listenForWhisper("typing", (user) => {
        this.activeUser = user;

        if (this.typingTimer) {
          clearTimeout(this.typingTimer);
        }

        this.typingTimer = setTimeout(() => {
          this.activeUser = false;
        }, 3000);
      });
  },

  methods: {
    fetchMessages() {
      axios.get("messages").then((response) => {
        this.roomMsgs = response.data;
      });
    },
    scrollToChatBottom() {
      const chatWindow = this.$refs.chatWindow;
      chatWindow.scrollTop = chatWindow.scrollHeight;
    },
    sendMessage() {
      let found = this.getTargetRoomIndex(this.activeRoom);
      this.roomMsgs[found].messages.push({
        user: this.user,
        message: this.newMessage,
      });
      axios
        .post("messages", {
          message: this.newMessage,
          room_id: this.activeRoom,
        })
        .then((response) => {
          if (response.data.status == "success" && this.chatrooms.length > 1) {
            let found = null;
            for (const indx in this.chatrooms) {
              if (this.chatrooms[indx].room_id == this.activeRoom) {
                found = indx;
              }
            }
            let room = this.chatrooms[found];
            this.chatrooms.splice(found, 1);
            this.chatrooms.unshift(room);
          }
        });
      this.newMessage = "";
    },
    sendTypingEvent() {
      Echo.join("chat").whisper("typing", this.user);
    },
    handleAttachmentUpload(attachmentUrl) {
      let found = this.getTargetRoomIndex(this.activeRoom);
      this.roomMsgs[found].messages.push({
        user: this.user,
        message: "",
        attachment_path: attachmentUrl,
      });
    },
    fetchChatrooms() {
      this.loadingChatrooms = true;
      axios.get("rooms").then((response) => {
        if (response.data.length > 0) {
          this.chatrooms = response.data;
          this.activeRoom = this.chatrooms[0].room_id;
        }
      })
      .finally(() => {
        this.loadingChatrooms = false;
      });
    },
    selectRoom: function (roomId) {
      this.activeRoom = roomId;
    },

    // Function to query the database for a certain user
    findUser(query) {
      if (query === "") {
        // Only send request if there is a search query
        this.userDropdownOptions = [];
        return;
      }

      // Show the loading bar
      this.isSearchLoading = true;
      axios
        .get("/users", {
          params: {
            name: query,
          },
        })
        .then((response) => {
          // Set the result of the database query as the options
          this.userDropdownOptions = response.data;
        })
        .catch((error) => {
          // Unexpected error occurred, for now log to console
          console.error(error);
        })
        .finally(() => {
          this.isSearchLoading = false;
        });
    },

    findUserNotInRoom(nameQuery, roomId) {
      if (nameQuery === "") {
        // Only send request if there is a search query
        this.newMemberDropdownOptions = [];
        return;
      }

      // Show the loading spinner
      this.isMemberSearchLoading = true;
      axios
        .get("/users", {
          params: {
            name: nameQuery,
            notInRoom: roomId,
          },
        })
        .then((response) => {
          // Set the result of the database query as the options
          this.newMemberDropdownOptions = response.data;
        })
        .catch((error) => {
          // Unexpected error occurred, for now log to console
          console.error(error);
        })
        .finally(() => {
          this.isMemberSearchLoading = false;
        });
    },

    createRoom() {
      // Check if there are newRoomMembers
      if (this.newRoomMembers.length === 0) {
        alert("Cannot create a room with no other members!");
        return;
      }

      // Generate a room name
      const roomName = this.newRoomMembers
        .concat(this.$props.user) // Include the current user
        .map((user) => `${user.first_name} ${user.last_name}`)
        .join(", ");

      // Get the selected members
      const members = this.newRoomMembers.map((user) => user.id);

      // Make a request to create a new room
      axios
        .post("/newRoom", {
          room_name: roomName,
          members: members,
        })
        .then((response) => {
          // Upon successful addition, add the new chatroom to the list
          this.chatrooms.unshift({
            room_id: response.data.room_id,
            room_name: response.data.room_name,
          });
          this.activeRoom = response.data.room_id;
          this.roomMsgs.unshift({
            room_id: response.data.room_id,
            room_name: response.data.room_name,
            messages: [],
          });
        });

      // Reset state
      this.newRoomMembers = [];
      this.addingRoom = false;
    },

    addMember(newMember) {
      axios.post("addMember", {
        email: newMember.email,
        room_id: this.activeRoom,
      });

      // Inform the chatroom that a new member has been added
      let found = this.getTargetRoomIndex(this.activeRoom);
      this.roomMsgs[found].messages.push({
        user: "",
        message:
          newMember.first_name + " " + newMember.last_name + " has been added",
      });

      this.newMemberDropdownOptions = [];
      this.addingMember = false;
    },
    getTargetRoomIndex(targetRoom) {
      let found = null;
      for (const indx in this.roomMsgs) {
        if (this.roomMsgs[indx].room_id == targetRoom) {
          found = indx;
        }
      }
      return found;
    },

    // Given a message object, converts it to a string resembling a chat message
    // This is used for the chat rooms list message preview
    convertMessageObjectToString(message) {
      if (!message) return "";

      if (message.attachment_path) {
        return `${message.user.first_name || ''} ${message.user.last_name || ''} sent an attachment.`;
      }

      return `${message.user.first_name || ''} ${message.user.last_name || ''}: ${message.message}`;
    }
  },
};
</script>

