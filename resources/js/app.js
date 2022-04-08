const { default: axios } = require('axios');

require('./bootstrap');


const username_input = document.getElementById("username");
const message_input = document.getElementById("message_input");
const message_form = document.getElementById("message_form");
let target_room = document.getElementById("target_room");
let messages_el = document.getElementById("messages_room1");

message_form.addEventListener('submit', function(e){
    //alert("send to " + target_room.value);
    e.preventDefault();
    let has_errors = false;
    if(username_input.value == ''){
        alert("Please enter a username");
        has_errors = true;
    }
    if(message_input.value == ''){
        alert("Please enter a message");
        has_errors = true;
    }
    if(has_errors){
        return;
    }

    const options= {
        method: 'post',
        url: '/send-message',
        data: {
            username: username_input.value,
            message: message_input.value,
            target_room: target_room.value
        }
    }
    axios(options);
});

window.Echo.channel('chat')
.listen('.message',(e)=>{
    //console.log(e);
    //alert("received in " + e.target_room);
    messages_el = document.getElementById("messages_" + e.target_room);
    messages_el.innerHTML += '<div class="message"><strong>'+ 
    e.username+     ':</strong>'
    + e.message + '</div>';
});

//adrian code
document.getElementById("room1").style.backgroundColor = "red";
const rooms = document.getElementsByClassName("rooms");
window.onload = function() {
   for (let i = 0; i < rooms.length; i++) {
    rooms[i].onclick = selectRoom;
  }
}
function selectRoom(){
    let theRoom = this.id;
    target_room.value = theRoom;
    //alert("messages_" + theRoom);
    
    for (let i = 0; i < rooms.length; i++) {
        rooms[i].style.backgroundColor = "white";
      }
    this.style.backgroundColor = "red";
}