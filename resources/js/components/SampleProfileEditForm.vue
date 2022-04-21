<template>
    <div>
        <button type = "button" class="EditProfileButton" title="Edit Profile" data-bs-toggle="modal" data-bs-target="#EditProfileModal">
            <ion-icon name="create-outline"></ion-icon>
        </button>

        <!-- Edit Profile  Dialog Modal -->
        <div id="EditProfileModal" class="modal add-modal-container" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title">Edit Profile</div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">    
                        <form class="formContainer" @submit.prevent="onSubmit()">
                            <section class="formDiv">
                                <div><p>Current Profile Picture:</p>
                                <img v-if="currentProfilePicture !== null" class="img-thumbnail" style="width:100px;height:100px; object-fit:cover; border-radius:100px;" :src="currentProfilePicture" alt="Profile picture">
                                <p v-else-if="currentProfilePicture === null">No profile picture.</p></div>
                                <div class="changeImageWrap">
                                    <label class="changeImage" for="profilepicture">Change Profile Pic</label>
                                    <input type="file" name="profilepicture" id="profilepicture" @change="onFileChange($event)">
                                </div>
                            </section>

                            <div> 
                                <label for="firstname">First Name</label>
                                <input class="form-control" type="text" name="firstname" id="firstname" v-model="firstName">
                            </div>

                            <div>
                                <label for="lastname">Last Name</label>
                                <input class="form-control" type="text" name="lastname" id="lastname" v-model="lastName">
                            </div>

                            <div> 
                                <label for="email">E-mail</label>
                                <input class="form-control" type="text" name="email" id="email" v-model="email">
                            </div>

                            <!-- This part shows error messages if there are any -->
                            <div class="text-danger">
                                <div v-for="error in errors" :key="error">
                                    {{error}}
                                </div>
                            </div>

                            <button type="submit" class="btn">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                firstName: '',
                lastName: '',
                email: '',
                currentProfilePicture: '',
                profilePicture: null,
                errors: []
            }
        },
        created() {
            // Retrieve the current user's details so we can show the current values on the form
            axios.get('/profile')
                .then(response => {                    
                    this.firstName = response.data.firstName;
                    this.lastName = response.data.lastName;
                    this.email = response.data.email;

                    this.currentProfilePicture = response.data.profilePicture;
                });
        },
        methods: {
            // Ensures the profile form contains valid input
            validate() {
                this.errors = [];

                if (this.firstName.length === 0) {
                    this.errors.push('Please enter a first name.');
                }

                if (this.lastName.length === 0) {
                    this.errors.push('Please enter a last name.');
                }

                if (this.email.length === 0) {
                    this.errors.push('Please enter an email address.');
                }
            },

            // Sets the data.profilePicture whenever the selected file changes
            // Necessary because there's no `v-model` for file inputs
            onFileChange(event) {
                const files = event.target.files;
                if (files.length > 0) {
                    // Just get the first file uploaded
                    this.profilePicture = files[0];
                }
                else {
                    this.profilePicture = null;
                }
            },

            onSubmit() {
                // Validate input first
                this.validate();

                if (this.errors.length > 0) {
                    console.log(this.errors);
                    return;
                }

                // Build a FormData object which we will use to send the form data to the server
                let formData = new FormData();
                formData.append('firstName', this.firstName);
                formData.append('lastName', this.lastName);
                formData.append('email', this.email);

                // Append to form data only if the user uploaded a profile picture file
                if (this.profilePicture) {
                    formData.append('profilePicture', this.profilePicture);
                }

                axios.post('/profile', formData)
                    .then(response => {
                        // Code in this part is run when the profile was updated successfully.
                        
                        alert('Profile successfully updated!');

                        // You can get data from the server via the `response.data` object.

                        // e.g., response.data.profilePicture contains a URL to the newly updated profile picture
                    });
            }
        }
    }
</script>

 </script>

<style scoped>

    .EditProfileButton {
        color:  whitesmoke;
        background: none;
        border: none;
        display: flex;
    }

    .formContainer {
        padding: 5%;
        background-color: #fff;
    }

    .formDiv {
        display: grid;
        grid-template-columns: 35% 65%;
        margin-bottom: 20px;
    }

    .formDiv .changeImageWrap {
        position: relative;
        margin-top: auto;
    }

    .formContainer input[type=text] {
        width: 100%;
        padding: 10px;
        margin: 5px 0 20px 0;
        border: none;
        background: #e9edee;
    }

    .formContainer input[type=text]:focus {
        background: #d8dcdd;
        outline: none;
        display: inline-block;
    }

    
    .formContainer input[type=file] {
        position: absolute;
        z-index: -1;
    }

    .formContainer .btn {
        display: block;
        padding: 12px 20px;
        border: none;
        background-color: #1a9988;
        color: white;
        cursor: pointer;
        width: 50%;
        margin: 5px;
        margin-bottom: 50px;
        opacity: 0.8;
        float: right;
    }

    .formContainer .changeImage {
        background-color: #eb5600;
        text-align: center;
        width: 77%;
        color: white;
        display: inline-block;
        padding: 12px 20px;
        cursor: pointer;
        border-radius: 5px;
        opacity: 0.8;        
        float: right;
    }

    .formContainer .btn:hover, .changeImage:hover {
        opacity: 1.0;
    }

</style>
